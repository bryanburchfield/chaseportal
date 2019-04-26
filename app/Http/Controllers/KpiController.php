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
use Illuminate\Support\Facades\Log;

class KpiController extends Controller
{
    public function index()
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $jsfile[] = "kpidash.js";
        $cssfile[] = "kpidash.css";

        $data = [
            'jsfile' => $jsfile,
            'cssfile' => $cssfile
        ];
        return view('kpidash')->with($data);
    }

    public function optOut(Request $request)
    {
        $this->removeRecipient($request->recipient_id);

        return view('unsubscribed');
    }

    public function removeRecipient($id)
    {
        $this->removeRecipientFromAll($id);

        $recipient = Recipient::find($id);
        if (!empty($recipient)) {
            $recipient->delete();
        }
    }

    public function removeRecipientFromAll($id)
    {
        foreach (KpiRecipient::where('recipient_id', $id) as $kr) {
            $kr->delete();
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

        if ($request->addtoall == 'true') {
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

        // ajax return
        $return['add_recipient'] = [$recipient->name, $recipient->email, $recipient->phone, $recipient->id];
        echo json_encode($return);
    }

    public function removeRecipientFromKpi(Request $request)
    {
        $kr = KpiRecipient::find($request->id);

        if (!empty($request->fromall)) {
            foreach (KpiRecipient::where('recipient_id', $kr->recipient_id) as $rec) {
                $rec->delete();
            }
        } else {
            $kr->delete();
        }

        // ajax return
        $return['remove_recipient'] = 1;
        echo json_encode($return);
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
        $return['kpi_update'] = '1';
        echo json_encode($return);
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
        $return['adjust_interval'] = '1';
        echo json_encode($return);
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

        $return['search_recip'] = $recipients;
        echo json_encode($return);
    }

    private function formatPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    public function recipients()
    {
        $groupId = Auth::user()->group_id;

        $data = [
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

    private function setDb()
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);
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
        $kpi = \App\Kpi::where('id', $kpiId)->first();

        $kpi_name = $kpi->name;
        $query = $kpi->query;

        $recipients = $kpi->getRecipients();

        if (empty($recipients)) {
            return "No recipients have been added";
        }

        // Run the query
        $bind = [
            'groupid' => $group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $results = DB::connection('sqlsrv')->select(DB::raw($query), $bind);

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
        return empty($results) ? [] : array_keys((array)$results[0]);
    }

    private function getValues($results)
    {
        $values = [];
        foreach ($results as $rec) {
            $values[] = array_values((array)$rec);
        }
        return $values;
    }
}
