<?php

namespace App\Services;

use App\Http\Controllers\EmailDripController;
use App\Models\EmailDripCampaign;
use App\Models\EmailDripCampaignFilter;
use App\Models\EmailDripSend;
use App\Models\EmailServiceProvider;
use App\Models\Script;
use App\Models\User;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailDripService
{
    use SqlServerTraits;

    // Directory where Email Service Providers live
    // This is in the controller class too!
    const ESP_DIR = 'Interfaces\\EmailServiceProvider';

    private $email_service_provider;

    public function __construct(EmailServiceProvider $email_service_provider)
    {
        // full path the class so we don't have to import it
        $class = 'App\\' . self::ESP_DIR . '\\' .
            Str::studly($email_service_provider->provider_type);

        $this->email_service_provider = new $class($email_service_provider);
    }

    public function testConnection()
    {
        return $this->email_service_provider->testConnection();
    }

    public static function runDrips()
    {
        $email_drip_campaigns = EmailDripCampaign::where('active', 1)
            ->has('emailDripCampaignFilters')
            ->orderBy('group_id')
            ->orderBy('email_service_provider_id')
            ->get();

        foreach ($email_drip_campaigns as $email_drip_campaign) {
            $email_service_provider = EmailServiceProvider::find($email_drip_campaign->email_service_provider_id);

            if ($email_service_provider) {
                $email_drip_service = new EmailDripService($email_service_provider);

                $email_drip_service->runDrip($email_drip_campaign);
            }
        }
    }

    private function runDrip(EmailDripCampaign $email_drip_campaign)
    {
        if (!Auth::check() || Auth::user()->group_id !== $email_drip_campaign->group_id) {
            // authenticate as user of the group
            Auth::logout();
            $user = User::where('group_id', '=', $email_drip_campaign->group_id)->first();
            if ($user) {
                Auth::login($user);
            }
        }

        // make sure we actually logged someone in
        if (!Auth::check()) {
            return;
        }

        // update run times 
        $now = (new Carbon)->toDateTimeString();
        $email_drip_campaign->last_run_from = empty($email_drip_campaign->last_run_to) ? $now : $email_drip_campaign->last_run_to;
        $email_drip_campaign->last_run_to = $now;
        $email_drip_campaign->save();

        // Set SqlSrv database
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        // We need to pass a request object later
        $request = new Request(['campaign' => $email_drip_campaign->campaign]);

        $email_drip_controller = new EmailDripController();

        // find custom table
        $table_id = $email_drip_controller->getCustomTableId($email_drip_campaign->campaign);
        $table_name = $email_drip_controller->getCustomTableName($table_id);

        // Build WHERE clause from filters
        $where = '';
        $dr_where = '';
        $bind = [
            'group_id' => Auth::user()->group_id,
            'campaign' => $email_drip_campaign->campaign,
        ];

        foreach ($email_drip_campaign->emailDripCampaignFilters as $filter) {
            if ($filter->field == 'CallStatus') {
                $dr_where .= ' AND ' . $this->buildQuery($email_drip_controller, $request, $filter, $bind);
            } else {
                $where .= ' AND ' . $this->buildQuery($email_drip_controller, $request, $filter, $bind);
            }
        }

        // query leads, outer join custom table
        $sql = "SELECT L.id AS lead_id, * FROM [$db].[dbo].[Leads] L";

        if (!empty($table_name)) {
            $sql .= " INNER JOIN [$db].[dbo].[ADVANCED_$table_name] A ON A.LeadId = L.IdGuid";
        }

        $sql .= " WHERE GroupId = :group_id
        AND Campaign = :campaign";

        if (!empty($email_drip_campaign->subcampaign)) {
            $sql .= " AND Subcampaign = :subcampaign";
            $bind['subcampaign'] = $email_drip_campaign->subcampaign;
        }

        $sql .= ' ' . $where . "
            AND L.id IN (
            SELECT LeadId FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Date > '$email_drip_campaign->last_run_from'
            AND DR.Date <= '$email_drip_campaign->last_run_to'
            $dr_where
            )";

        $results = $this->runSql($sql, $bind);

        foreach ($results as $rec) {
            // If email field is blank, bail now
            if (
                $rec[$email_drip_campaign->email_field] == 'NULL' ||
                empty($rec[$email_drip_campaign->email_field])
            ) {
                continue;
            }

            // Find the count and last time we emailed this lead for this campaign
            $sends = EmailDripSend::where('email_drip_campaign_id', $email_drip_campaign->id)
                ->where('lead_id', $rec['lead_id'])
                ->orderBy('emailed_at')
                ->get();

            $count = $sends->count();

            if ($count) {
                $last_sent = Carbon::parse($sends->last()->emailed_at);
                $days_ago = $last_sent->diffInDays();
            } else {
                $days_ago = 30000;  // stupid big number
            }

            // if we're under the limit and outside the re-send window, send an email
            if ($count < $email_drip_campaign->emails_per_lead && $days_ago >= $email_drip_campaign->days_between_emails) {
                $this->emailLead($email_drip_controller, $email_drip_campaign, $rec);
            }
        }
    }

    private function buildQuery($email_drip_controller, Request $request, EmailDripCampaignFilter $filter, &$bind)
    {
        $today = Carbon::parse('midnight today', Auth::user()->iana_tz)->tz('UTC');
        $i = count($bind);

        switch ($filter->operator) {
            case 'blank':
                $compare = '= \'\'';
                break;
            case 'not_blank':
                $compare = '!= \'\'';
                break;
            case 'days_ago':
                $from = (clone $today)->subDays($filter->value)->toDateTimeString();
                $to = (clone $today)->subDays($filter->value - 1)->toDateTimeString();
                $compare = 'BETWEEN \'' . $from . '\' AND \'' . $to . '\'';
                break;
            case 'days_from_now':
                $from = (clone $today)->addDays($filter->value)->toDateTimeString();
                $to = (clone $today)->addDays($filter->value + 1)->toDateTimeString();
                $compare = 'BETWEEN \'' . $from . '\' AND \'' . $to . '\'';
                break;
            case '<_days_ago':
                $from = (clone $today)->subDays($filter->value)->toDateTimeString();
                $compare = '< \'' . $from . '\'';
                break;
            case '>_days_ago':
                $from = (clone $today)->subDays($filter->value - 1)->toDateTimeString();
                $compare = '>= \'' . $from . '\'';
                break;
            case '<_days_from_now':
                $from = (clone $today)->addDays($filter->value)->toDateTimeString();
                $compare = '< \'' . $from . '\'';
                break;
            case '>_days_from_now':
                $from = (clone $today)->addDays($filter->value + 1)->toDateTimeString();
                $compare = '>= \'' . $from . '\'';
                break;
            default:
                $compare = $filter->operator . ' :bind' . $i;
                $bind['bind' . $i] = $filter->value;
        }

        $table_fields = array_keys($email_drip_controller->getTableFields($request));

        // Special case for CallStatus
        if ($filter->field == 'CallStatus') {
            $where = 'DR.CallStatus ' . $compare;
        } elseif (in_array($filter->field, $table_fields)) {
            $where = 'A.[' . $filter->field . '] ' . $compare;
        } else {
            $where = 'L.[' . $filter->field . '] ' . $compare;
        }

        return $where;
    }

    private function emailLead(EmailDripController $email_drip_controller, EmailDripCampaign $email_drip_campaign, $rec)
    {
        // load body from template
        $body = Script::find($email_drip_campaign->template_id);

        if (!$body) {
            return;
        }

        $body = $body->HtmlContent;
        $subject = $email_drip_campaign->subject;

        // get list of mergable fields
        $fields = array_keys($email_drip_controller->getFilterFields($email_drip_campaign));

        // do merge
        foreach ($fields as $field) {
            $body = str_ireplace('(#' . $field . '#)', htmlspecialchars($rec[$field]), $body);
            $subject = str_ireplace('(#' . $field . '#)', $rec[$field], $subject);
        }

        $payload = [
            'from' => $email_drip_campaign->from,
            'to' => $rec[$email_drip_campaign->email_field],
            'subject' => $subject,
            'body' => $body,
        ];

        $result = $this->email_service_provider->send($payload);

        // Insert a sent record
        EmailDripSend::create([
            'email_drip_campaign_id' => $email_drip_campaign->id,
            'lead_id' => $rec['lead_id'],
        ]);
    }
}
