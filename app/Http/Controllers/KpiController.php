<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\KpiGroup;
use App\Models\KpiRecipient;
use App\Models\Recipient;
use App\Models\User;
use App\Mail\KpiMail;
use App\Traits\TimeTraits;
use App\Http\Requests\AddRecipient;
use App\Http\Requests\EditRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Twilio\Rest\Client as Twilio;
use OwenIt\Auditing\Models\Audit;

class KpiController extends Controller
{
    use TimeTraits;

    /**
     * Opt-out of KPI mailings/texts
     * This is triggered from an optout link in the emails
     *
     * @param Request $request
     * @return view
     */
    public function optOut(Request $request)
    {
        // $request->recipient_id is the recip to remove
        // We need to create a request->id to pass to removeRecipient()
        $newRequest = new Request();
        $newRequest->setMethod('POST');
        $newRequest->request->add(['id' => $request->recipient_id]);

        $this->removeRecipient($newRequest);

        return view('unsubscribed');
    }

    /**
     * Remove recipient entirely
     *
     * @param Request $request
     * @return void
     */
    public function removeRecipient(Request $request)
    {
        $this->removeRecipientFromAll($request->id);

        $recipient = Recipient::find($request->id);
        if (!empty($recipient)) {
            $recipient->delete();
        }

        return ['remove_recip' => 1];
    }

    /**
     * Get recipient - return recipient info
     *
     * @param Request $request
     * @return void
     */
    public function getRecipient(Request $request)
    {
        $recipient = Recipient::find($request->id);
        return [
            'recipient' => $recipient,
            'kpi_list' => $recipient->kpiList()
        ];
    }

    /**
     * Update recipient
     *
     * @param Request $request
     * @return void
     */
    public function updateRecipient(EditRecipient $request)
    {
        // flash the kpi id to session so we can re-open that one
        if ($request->has('kpi_id')) {
            $request->flashOnly('kpi_id');
        }

        // check the group here just in case they're trying to hack the form
        $recipient = Recipient::where('group_id', Auth::user()->group_id)
            ->where('id', $request->recipient_id)
            ->firstOrFail();

        $recipient->email = $request->email;
        $recipient->name = $request->name;
        $recipient->phone = $this->formatPhone($request->phone);
        $recipient->save();

        // add/remove recip from kpis
        if (empty($request->kpi_list)) {
            $this->removeRecipientFromAll($recipient->id);
        } else {
            $kpi_ids = [];
            foreach ($request->kpi_list as $kpi_id) {
                if (is_numeric($kpi_id)) {
                    $kpi_ids[] = $kpi_id;
                    $kpi_recipient = KpiRecipient::where('kpi_id', $kpi_id)->where('recipient_id', $recipient->id)->first();
                    if (!$kpi_recipient) {
                        $kpi_recipient = KpiRecipient::create(['kpi_id' => $kpi_id, 'recipient_id' => $recipient->id]);
                    }
                }
            }

            // delete any not on the list
            // Can't do a mass delete because it won't create audit records
            foreach (KpiRecipient::where('recipient_id', $recipient->id)->whereNotIn('kpi_id', $kpi_ids)->get() as $kpi_recipient) {
                $kpi_recipient->delete();
            }
        }

        return ['status' => 'success'];
    }

    /**
     * Remove recipient from KPI
     *
     * @param Request $request
     * @return void
     */
    public function removeRecipientFromKpi(Request $request)
    {
        $kpi_recipient = KpiRecipient::find($request->id);

        // check the group here just in case they're trying to hack the form
        if ($kpi_recipient->recipient->group_id != Auth::user()->group_id) {
            abort(404);
        }

        $kpi_recipient->delete();

        return ['kpi_recipient' => $kpi_recipient];
    }

    /**
     * Remove recipient from all KPIs
     *
     * @param [type] $id
     * @return void
     */
    public function removeRecipientFromAll($id)
    {
        $recipients = KpiRecipient::where('recipient_id', $id)->get();
        foreach ($recipients as $recip) {
            $recip->delete();
        }
    }

    /**
     * Add a new recipient
     *
     * @param Request $request
     * @return void
     */
    public function addRecipient(AddRecipient $request)
    {
        $recipient = new Recipient();

        $recipient->group_id = Auth::user()->group_id;
        $recipient->email = $request->email;
        $recipient->name = $request->name;
        $recipient->phone = $this->formatPhone($request->phone);
        $recipient->user_id = Auth::user()->id;
        $recipient->save();

        if (!empty($request->kpi_list)) {
            foreach ($request->kpi_list as $kpi_id) {
                if (is_numeric($kpi_id)) {
                    $kr = new KpiRecipient();
                    $kr->kpi_id = $kpi_id;
                    $kr->recipient_id = $recipient->id;
                    $kr->save();
                }
            }
        }

        return $this->recipients();
    }

    /**
     * Toggle KPI active/inactive
     *
     * @param Request $request
     * @return void
     */
    public function toggleKpi(Request $request)
    {
        $kpi_id = $request->kpi;
        $active = ($request->checked == 0) ? false : true;
        $group_id = Auth::user()->group_id;

        $kpiGroup = KpiGroup::where('kpi_id', $kpi_id)
            ->where('group_id', $group_id)
            ->first();

        if (empty($kpiGroup)) {
            $kpiGroup = new KpiGroup();
            $kpiGroup->kpi_id = $kpi_id;
            $kpiGroup->group_id = $group_id;
            $kpiGroup->interval = 1440;  // default interval
        }

        $kpiGroup->active = $active;
        $kpiGroup->save();

        //ajax return
        return ['kpi_group' => $kpiGroup];
    }

    /**
     * Update interval at which KPI runs
     *
     * @param Request $request
     * @return void
     */
    public function adjustInterval(Request $request)
    {
        $kpi_id = $request->kpi_id;
        $interval = $request->interval;
        $group_id = Auth::user()->group_id;

        $kpiGroup = KpiGroup::where('kpi_id', $kpi_id)
            ->where('group_id', $group_id)
            ->first();

        if (empty($kpiGroup)) {
            $kpiGroup = new KpiGroup();
            $kpiGroup->kpi_id = $kpi_id;
            $kpiGroup->group_id = $group_id;
        }

        $kpiGroup->interval = $interval;
        $kpiGroup->save();

        // ajax return
        return ['adjust_interval' => '1'];
    }

    /**
     * Find recipients by partial name match
     *
     * @param Request $request
     * @return void
     */
    public function searchRecipients(Request $request)
    {
        $group_id = Auth::user()->group_id;
        $name = $request->input('query') . '%';

        $recipients = Recipient::where('group_id', $group_id)
            ->where('name', 'like', $name)
            ->orderBy('name')
            ->get();

        return ['search_recip' => $recipients];
    }

    /**
     * Remove all non-digits from a phone number
     *
     * @param [type] $phone
     * @return void
     */
    private function formatPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Recipients view
     *
     * @return view
     */
    public function recipients()
    {
        $groupId = Auth::user()->group_id;
        $page['menuitem'] = 'kpidash';
        $page['type'] = 'recipients';
        $jsfile[] = "kpidash.js";

        $all_kpis = Kpi::orderBy('name', 'asc')->pluck('name', 'id')->all();

        foreach ($all_kpis as $id => &$name) {
            $name = trans('kpi.' . $name);
        }

        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
            'curdash' => 'kpidash',
            'all_kpis' =>  $all_kpis,
            'recipients' => Recipient::where('group_id', $groupId)
                ->orderBy('name')
                ->get(),
        ];
        return view('dashboards.recipients')->with($data);
    }

    /**
     * Calculate date range from midnight local to current time
     *
     * @return array
     */
    private function dateRange()
    {
        $tz = Auth::user()->iana_tz;

        $fromDate = Carbon::parse('today', $tz)->tz('UTC');
        $toDate = new Carbon();

        return [$fromDate, $toDate];
    }

    /**
     * Set SQL Server db in config and also run a USE statement
     *
     * @return void
     */
    private function setDb()
    {
        $db = Auth::user()->dialer->reporting_db;
        config(['database.connections.sqlsrv.database' => $db]);

        $query = "USE [$db];";
        DB::connection('sqlsrv')->statement($query);
    }

    /**
     * Run a KPI
     *
     * @param Request $request
     * @return void
     */
    public function runKpi(Request $request)
    {
        $this->setDb();

        $kpiId = $request->kpi_id;

        list($fromDate, $toDate) = $this->dateRange();

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $group_id = Auth::user()->group_id;
        $db_list = array_values(Auth::user()->getDatabaseArray());

        // Get kpi info
        $kpi = Kpi::where('id', $kpiId)->first();

        $kpi_name = $kpi->name;
        $recipients = $kpi->getRecipients($group_id);

        if (empty($recipients)) {
            return "No recipients have been added";
        }

        list($sql, $bind) = $kpi->sql($db_list, $group_id, $startDate, $endDate);

        // Run the query
        $results = DB::connection('sqlsrv')->select($sql, $bind);

        $sms = $this->getSms($kpi_name, $results);

        $sid   = config('twilio.sid');
        $token = config('twilio.token');

        $twilio = new Twilio($sid, $token);

        $tz = Auth::user()->iana_tz;

        foreach ($recipients as $recipient) {
            try {
                $this->sendSms($twilio, $recipient, $sms);
            } catch (\Exception $e) {
                // don't care
            }

            if (!empty($recipient->email)) {
                $message = [
                    'to' => $recipient->email,
                    'subject' => "Chase Data KPI",
                    'current' => Carbon::parse()->tz($tz)->isoFormat('LLLL'),
                    'url' => url('/') . '/',
                    'optouturl' => Url::signedRoute('kpi.optout', ['recipient_id' => $recipient->recipient_id]),
                    'kpi_name' => $kpi_name,
                    'table_headers' => $this->getHeaders($results),
                    'table_rows' => $this->getValues($results),
                ];

                try {
                    $this->sendEmail($message);
                } catch (\Exception $e) {
                    // don't care
                }
            }
        }

        return 'true';
    }

    /**
     * Construct text message from KPI results
     *
     * @param string $kpi_name
     * @param array $results
     * @return string
     */
    public function getSms($kpi_name, $results)
    {
        $sms = ' -== ' . trans('kpi.' . $kpi_name) . ' ==-' . PHP_EOL . PHP_EOL;

        if (empty($results)) {
            $sms .= trans('kpi.no_data');
            return $sms;
        }

        foreach ($results as $rec) {
            $i = 0;
            foreach ($rec as $k => $v) {
                if ($i == 0 && ($k == 'Campaign' || $k == 'Agent')) {
                    $sms .= "$v - ";
                } else {
                    $sms .= trans('kpi.' . Str::snake($k)) . ": $v ";
                }
                $i++;
            }
            $sms .= PHP_EOL . PHP_EOL;
        }

        return $sms;
    }

    /**
     * Send text message
     *
     * @param object $twilio
     * @param object $recipient
     * @param string $sms
     * @return void
     */
    private function sendSms($twilio, $recipient, $sms)
    {
        if (empty($recipient->phone)) {
            return;
        }

        $twilio->messages->create(
            $recipient->phone,
            [
                'from' => config('twilio.from'),
                'body' => $sms
            ]
        );

        return;
    }

    /**
     * Send KPI results in an email
     *
     * @param object $message
     * @return void
     */
    private function sendEmail($message)
    {
        Mail::to($message['to'])
            ->send(new KpiMail($message));
    }

    /**
     * Extract column headers from KPI results
     *
     * @param array $results
     * @return array
     */
    private function getHeaders($results)
    {
        $headers = empty($results) ? [] : array_keys((array) $results[0]);

        return array_map(function ($a) {
            return trans('kpi.' . Str::snake($a));
        }, $headers);
    }

    /**
     * Extract values from KPI results
     *
     * @param array $results
     * @return array
     */
    private function getValues($results)
    {
        $values = [];
        foreach ($results as $rec) {
            $values[] = array_values((array) $rec);
        }
        return $values;
    }

    public function auditRecipient(Request $request)
    {
        $recipient = Recipient::find($request->id);

        // In case the recipient was deleted, create a temp one with the old id
        if (!$recipient) {
            $recipient = new Recipient();
            $recipient->id = $request->id;
        }

        $audits = $recipient->audits;

        // find all KpiRecipient audit recs with this recipient_id
        $kpi_recipient_audits = [];
        foreach (Audit::where('auditable_type', 'App\Models\KpiRecipient')->get() as $audit) {
            if (
                (isset($audit->old_values['recipient_id']) && $audit->old_values['recipient_id'] == $request->id) ||
                (isset($audit->new_values['recipient_id']) && $audit->new_values['recipient_id'] == $request->id)
            ) {
                // Change 'event' to something more meaningful
                switch ($audit->event) {
                    case 'created':
                        $audit->kpi_event = "Added to";
                        break;
                    case 'deleted':
                        $audit->kpi_event = "Removed from";
                        break;
                    default:
                        $audit->kpi_event = $audit->event;
                }
                // One of these will be set
                $kpi_id = isset($audit->old_values['kpi_id']) ?  $audit->old_values['kpi_id'] : $audit->new_values['kpi_id'];

                $kpi = Kpi::find($kpi_id);

                // just in case
                if (!$kpi) {
                    $kpi = new Kpi;
                }

                $created_at = $audit->created_at->toDateTimeString();

                // if we're creating, and there was a matching deleted, ignore both
                if ($audit->event == 'created' && isset($kpi_recipient_audits[$created_at])) {
                    foreach ($kpi_recipient_audits[$created_at] as $key => $saved_audit) {
                        if ($saved_audit['event'] == 'deleted' && $saved_audit['kpi'] == $kpi) {
                            unset($kpi_recipient_audits[$created_at][$key]);
                            continue 2;
                        }
                    }
                }

                $kpi_recipient_audits[$created_at][] = [
                    'created_at' => $audit->created_at,
                    'ip_address' => $audit->ip_address,
                    'user_name' => $audit->user->name,
                    'user_email' => $audit->user->email,
                    'event' => $audit->event,
                    'kpi_event' => $audit->kpi_event,
                    'kpi' => $kpi,
                ];
            }
        }

        $page['menuitem'] = 'kpidash';
        $page['type'] = 'page';

        $data = [
            'page' => $page,
            'recipient' =>  $recipient,
            'audits' =>  $audits,
            'kpi_recipient_audits' =>  $kpi_recipient_audits,
        ];
        return view('dashboards.recipient_audit')->with($data);
    }

    /**
     * Return all kpi_group records that are due to run at this time
     *
     * @return KpiGroup collection
     */
    public static function cronDue()
    {
        $return = collect();

        foreach (KpiGroup::where('active', 1)->orderBy('group_id')->get() as $rec) {
            switch ($rec->interval) {
                case 15:
                    $expression = '0,15,30,45 8-20 * * 1-5';
                    break;
                case 30:
                    $expression = '0,30 8-20 * * 1-5';
                    break;
                case 60:
                    $expression = '0 8-20 * * 1-5';
                    break;
                case 720:
                    $expression = '0 12,20 * * 1-5';
                    break;
                case 1440:
                    $expression = '0 20 * * 1-5';
                    break;
                default:
                    continue 2;
            }

            // find timezone of first user of that group
            $user = User::where('group_id', '=', $rec->group_id)->first();

            if ($user) {
                $timezone = $user->iana_tz;

                if ($rec->isDue($expression, $timezone)) {
                    $return->add($rec);
                }
            }
        }

        return $return;
    }

    /**
     * Run KPI in background (from scheduler)
     *
     * @param KpiGroup $kpiGroup
     * @return void
     */
    public static function cronRun(KpiGroup $kpiGroup)
    {
        // They should never be logged in at this point, but just in case
        if (Auth::check()) {
            Auth::logout();
        }

        // authenticate as user of the group
        $user = User::where('group_id', '=', $kpiGroup->group_id)->first();

        if ($user) {
            // set a flag so the audit trail doesn't pick it up
            session(['isCron' => 1]);
            Auth::login($user);

            $kpi = new KpiController();

            $request = new Request();
            $request->setMethod('POST');
            $request->request->add(['kpi_id' => $kpiGroup->kpi_id]);
            $kpi->runKpi($request);

            Auth::logout();
        }
    }
}
