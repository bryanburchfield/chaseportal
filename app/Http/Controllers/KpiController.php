<?php

namespace App\Http\Controllers;

use App\Mail\KpiMail;
use Illuminate\Http\Request;
use App\Kpi;
use App\Recipient;
use App\KpiRecipient;
use App\KpiGroup;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Log;

class KpiController extends Controller
{
    public function index()
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $jsfile[] = "kpidash.js";
        $cssfile[] = "kpidash.css";

        // $page['menuitem'] = $this->currentDash;

        // $page['type'] = 'dash';
        // if ($this->currentDash == 'kpidash') {
        //     $page['type'] = 'kpi_page';
        // }

        $data = [
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
            'curdash' => 'kpidash',
        ];
        return view('kpidash')->with($data);
    }

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

    public function removeRecipient(Request $request)
    {

        $this->removeRecipientFromAll($request->id);

        $recipient = Recipient::find($request->id);
        if (!empty($recipient)) {
            $recipient->delete();
        }

        return ['remove_recip' => 1];
    }

    public function removeRecipientFromKpi(Request $request)
    {

        $recipient = KpiRecipient::find($request->id)->delete();

        return ['remove_recipient' => 1];
    }

    public function removeRecipientFromAll($id)
    {

        $recipients = KpiRecipient::where('recipient_id', $id)->get();
        foreach ($recipients as $recip) {
            $recip->delete();
        }
    }

    public function addRecipient(Request $request)
    {

        // See if recip exists by email or phone
        $recipient = Recipient::where('phone', $this->formatPhone($request->phone))
            ->orWhere('email', $request->email)->first();

        if (empty($recipient)) {
            $recipient = new Recipient();
            $recipient->name = $request->name;
            $recipient->email = $request->email;
            $recipient->phone = $this->formatPhone($request->phone);
            $recipient->group_id = Auth::user()->group_id;
            $recipient->save();
        }

        if ($request->addtoall == 'true' || $request->redirect_url == 'recipients') { // this also needs to run based on if it came from the add_recip form on kpi/recipients
            $kpis = Kpi::all();
        } else {
            $kpis = Kpi::where('id', $request->kpi_id)->get();
        }

        foreach ($kpis as $kpi) {
            $kr = new KpiRecipient();
            $kr->kpi_id = $kpi->id;
            $kr->recipient_id = $recipient->id;
            $kr->save();
        }

        return [
            'add_recipient' => [
                $recipient->name,
                $recipient->email,
                $recipient->phone,
                $recipient->id,
            ],
        ];
    }

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
        }

        $kpiGroup->active = $active;
        $kpiGroup->save();

        //ajax return
        return ['kpi_update' => '1'];
    }

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

    public function searchRecipients(Request $request)
    {
        $group_id = Auth::user()->group_id;
        $kpi_id = $request->kpi_id;
        $name = $request->name . '%';

        $recipients = Recipient::where('group_id', $group_id)
            ->where('name', 'like', $name)
            ->whereNotExists(function ($query) use ($kpi_id) {
                $query->select(DB::raw(1))
                    ->from('kpi_recipients')
                    ->whereRaw('kpi_id = ' . $kpi_id .
                        ' AND recipient_id = recipients.id');
            })
            ->orderBy('name')
            ->get()
            ->toArray();

        return ['search_recip' => $recipients];
    }

    private function formatPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    public function recipients()
    {
        $groupId = Auth::user()->group_id;
        $page['menuitem'] = 'kpidash';
        $page['type'] = 'other';
        $jsfile[] = "kpidash.js";

        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
            'curdash' => 'kpidash',
            'recipients' => Recipient::where('group_id', $groupId)
                ->orderBy('name')
                ->get(),
        ];
        return view('recipients')->with($data);
    }

    private function dateRange()
    {
        $tz = Auth::user()->tz;

        $fromDate = localToUtc(date('Y-m-d'), $tz);
        $toDate = new \DateTime();

        return [$fromDate, $toDate];
    }

    /**
     * Set SQL Server db in config and also run a USE statement
     *
     * @return void
     */
    private function setDb()
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $query = "USE [$db];";
        DB::connection('sqlsrv')->statement($query);
    }

    public function runKpi(Request $request)
    {
        $this->setDb();

        $kpiId = $request->kpi_id;

        list($fromDate, $toDate) = $this->dateRange();

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $group_id = Auth::user()->group_id;

        // Get kpi info
        $kpi = Kpi::where('id', $kpiId)->first();

        $kpi_name = $kpi->name;
        $query = $kpi->query;

        $recipients = $kpi->getRecipients($group_id);

        if (empty($recipients)) {
            return "No recipients have been added";
        }

        // Run the query
        $bind = [
            'groupid' => $group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $results = DB::connection('sqlsrv')->select($query, $bind);

        $sms = $this->getSms($kpi_name, $results);

        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $twilio = new Client($sid, $token);

        $tz = Auth::user()->tz;

        foreach ($recipients as $recipient) {
            $this->sendSms($twilio, $recipient, $sms);
            if (!empty($recipient->email)) {
                $message = [
                    'to' => $recipient->email,
                    'subject' => "Chase Data KPI",
                    'current' => utcToLocal(date('Y-m-d H:i:s'), $tz)->format('m/d/Y H:i'),
                    'url' => url('/') . '/',
                    'optouturl' => Url::signedRoute('kpi.optout', ['recipient_id' => $recipient->recipient_id]),
                    'kpi_name' => $kpi_name,
                    'table_headers' => $this->getHeaders($results),
                    'table_rows' => $this->getValues($results),
                ];
                $this->sendEmail($message);
            }
        }

        return 'true';
    }

    private function getSms($kpi_name, $results)
    {
        $sms = ' -== ' . $kpi_name . ' ==-' . PHP_EOL . PHP_EOL;

        if (empty($results)) {
            $sms .= 'No Data to Report' . PHP_EOL . PHP_EOL;
            return $sms;
        }

        foreach ($results as $rec) {
            $i = 0;
            foreach ($rec as $k => $v) {
                if ($i == 0 && ($k == 'Campaign' || $k == 'Agent')) {
                    $sms .= "$v - ";
                } else {
                    $sms .= "$k: $v ";
                }
                $i++;
            }
            $sms .= PHP_EOL . PHP_EOL;
        }

        return $sms;
    }

    private function sendSms($twilio, $recipient, $sms)
    {
        if (empty($recipient->phone)) {
            return;
        }

        $twilio->messages->create(
            $recipient->phone,
            [
                'from' => env('TWILIO_FROM'),
                'body' => $sms
            ]
        );

        return;
    }

    private function sendEmail($message)
    {
        Mail::to($message['to'])
            ->send(new KpiMail($message));
    }

    private function getHeaders($results)
    {
        return empty($results) ? [] : array_keys((array) $results[0]);
    }

    private function getValues($results)
    {
        $values = [];
        foreach ($results as $rec) {
            $values[] = array_values((array) $rec);
        }
        return $values;
    }

    /**
     * Return all kpi_group records that are due to run at this time
     *
     * @return KpiGroup collection
     */
    public static function cronDue()
    {
        // We could use the user's tz, but that would require
        // looking up a user for each kpi_group record, slowing
        // us down.  This function is being fired once a minute, 24/7

        $timezone = windowsToUnixTz('Eastern Standard Time');

        $return = collect();

        foreach (KpiGroup::where('active', 1)->orderBy('group_id')->get() as $rec) {
            switch ($rec->interval) {
                case 15:
                    $expression = '0,15,30,45 * * * 1-5';
                    break;
                case 30:
                    $expression = '0,30 * * * 1-5';
                    break;
                case 60:
                    $expression = '0 * * * 1-5';
                    $expression = '* * * * 1-5';
                    break;
                case 720:
                    $expression = '0 12,20 * * 1-5';
                    break;
                case 1440:
                    $expression = '0, 20 * * 1-5';
                    break;
                default:
                    continue;
            }

            // This is where we would look up a user to get their
            // timezone - if we were going to do that

            if ($rec->isDue($expression, $timezone)) {
                $return->add($rec);
            }
        }

        return $return;
    }

    public static function cronRun(KpiGroup $kpiGroup)
    {
        // authenticate as user of the group
        $user = User::where('group_id', '=', $kpiGroup->group_id)->first();
        Auth::logout();
        Auth::login($user);
        $kpi = new KpiController();

        $request = new Request();
        $request->setMethod('POST');
        $request->request->add(['kpi_id' => $kpiGroup->kpi_id]);
        $kpi->runKpi($request);
    }
}
