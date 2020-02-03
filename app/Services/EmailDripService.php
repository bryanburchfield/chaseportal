<?php

namespace App\Services;

use App\Http\Controllers\EmailDripController;
use App\Models\EmailDripCampaign;
use App\Models\EmailDripCampaignFilter;
use App\Models\EmailServiceProvider;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailDripService
{
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
        $now = (new Carbon)->toDateTimeString();

        $email_drip_campaigns = EmailDripCampaign::where('active', 1)
            ->orderBy('group_id')
            ->orderBy('email_service_provider_id')
            ->get();

        foreach ($email_drip_campaigns as $email_drip_campaign) {
            // update run times 
            $email_drip_campaign->last_run_from = empty($email_drip_campaign->last_run_to) ? $now : $email_drip_campaign->last_run_to;
            $email_drip_campaign->last_run_to = $now;
            $email_drip_campaign->save();

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

        // Set SqlSrv database
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $email_drip_controller = new EmailDripController();

        // find custom table
        $table_id = $email_drip_controller->getCustomTableId($email_drip_campaign->campaign);
        $table_name = $email_drip_controller->getCustomTableName($table_id);

        // Build WHERE clause from filters
        $where = '';
        foreach ($email_drip_campaign->emailDripCampaignFilters as $filter) {
            $where .= ' AND ' . $this->buildQuery($filter);
        }

        // query leads, outer join custom table
        $bind = [
            'group_id' => Auth::user()->group_id,
            'campaign' => $email_drip_campaign->campaign,
        ];

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
            SELECT LeadId FROM [$db].[dbo].[DialingResults]
            WHERE Date > '$email_drip_campaign->last_run_from'
            AND Date <= '$email_drip_campaign->last_run_to'
            )";

        echo "$sql\n";
    }

    private function buildQuery(EmailDripCampaignFilter $filter)
    {
        $today = Carbon::parse('midnight today', Auth::user()->iana_tz)->tz('UTC');

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
                $compare = $filter->operator . ' \'' . $filter->value . '\'';
        }

        $where = '[' . $filter->field . '] ' . $compare;

        return $where;
    }
}
