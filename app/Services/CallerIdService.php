<?php

namespace App\Services;

use App\Exports\ReportExport;
use App\Mail\CallerIdMail;
use App\Models\Dialer;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client as Twilio;

class CallerIdService
{
    use SqlServerTraits;
    use TimeTraits;

    private $group_id;
    private $email_to;
    private $startdate;
    private $enddate;

    private function initialize()
    {
        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);

        $sid = config('twilio.did_sid');
        $token = config('twilio.did_token');

        $this->twilio = new Twilio($sid, $token);
    }

    private function setGroup($group_id = null)
    {
        // hard-coded recips
        $canned = [
            235773  => [
                'email_to' => 'g.sandoval@chasedatacorp.com',
            ],
        ];

        if (!isset($canned[$group_id])) {
            $this->group_id = null;
            $this->email_to = null;
            return false;
        }

        $this->group_id = $group_id;
        $this->email_to = $canned[$group_id]['email_to'];

        return true;
    }

    public static function execute()
    {
        $caller_id_service = new CallerIdService();
        $caller_id_service->runReport();
    }

    public function runReport()
    {
        $this->initialize();

        $group_id = '';
        $results = [];
        $all_results = [];

        foreach ($this->runQuery() as $rec) {
            // check if this number is still active
            if ($this->activeNumber($rec['CallerId'])) {

                $rec['ContactRate'] = round($rec['Contacts'] / $rec['Dials'] * 100, 2) . '%';
                unset($rec['Contacts']);

                $all_results[] = $rec;

                // Send email on change of group
                if ($group_id != '' && $group_id != $rec['GroupId']) {
                    if ($this->setGroup($group_id)) {
                        $csvfile = $this->makeCsv($results);
                        $this->emailReport($csvfile);
                    }

                    $results = [];
                }

                $results[] = $rec;
                $group_id = $rec['GroupId'];
            }
        }

        if (!empty($results)) {
            if ($this->setGroup($group_id)) {
                $csvfile = $this->makeCsv($results);
                $this->emailReport($csvfile);
            }
        }

        if (!empty($all_results)) {
            // clear group specific vars
            $this->setGroup();
            $csvfile = $this->makeCsv($all_results);
            $this->emailReport($csvfile);
        }
    }

    private function runQuery()
    {
        $bind = [];

        $sql = "SELECT GroupId, GroupName, CallerId, SUM(cnt) as Dials, SUM(Contacts) as Contacts FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('id', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();

            $sql .= " $union SELECT DR.GroupId, G.GroupName, DR.CallerId,
              'cnt' = COUNT(*),
              'Contacts' = SUM(CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END)
             FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                OUTER APPLY (SELECT TOP 1 [Type]
                    FROM " . '[' . $dialer->reporting_db . ']' . ".[dbo].[Dispos]
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [id]) DI
                WHERE DR.Date >= :startdate$i AND DR.Date < :enddate$i
                AND DR.CallerId != ''
                AND DR.CallType IN (0,2)
                GROUP BY DR.GroupId, GroupName, CallerId
                HAVING COUNT(*) >= 5500
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, CallerId
            HAVING SUM(cnt) >= 5500
            ORDER BY GroupName, Dials desc";

        return $this->yieldSql($sql, $bind);
    }

    private function makeCsv($results)
    {
        $headers = [
            'GroupID',
            'GroupName',
            'CallerID',
            'Dials in Last 30 Days',
            'Contact Rate',
        ];

        array_unshift($results, $headers);

        // Create a uniquish filename
        $tempfile = '/' . uniqid() . '.csv';

        // this write to the directiory: storage_path('app')
        Excel::store(new ReportExport($results), $tempfile);

        return $tempfile;
    }

    private function emailReport($csvfile)
    {
        // path out file
        $csvfile = storage_path('app' . $csvfile);

        // read file into variable, then delete file
        $csv = file_get_contents($csvfile);
        unlink($csvfile);

        if ($this->email_to === null) {
            $to = 'jonathan.gryczka@chasedatacorp.com';
            $cc = [
                'g.sandoval@chasedatacorp.com',
                'ahmed@chasedatacorp.com',
            ];
        } else {
            $to = $this->email_to;
            $cc = [
                'jonathan.gryczka@chasedatacorp.com',
                'ahmed@chasedatacorp.com',
            ];
        }

        // email report
        $message = [
            'subject' => 'Caller ID Report',
            'csv' => base64_encode($csv),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'enddate' => $this->enddate->toFormattedDateString(),
        ];

        Mail::to($to)
            ->cc($cc)
            ->bcc('bryan.burchfield@chasedatacorp.com')
            ->send(new CallerIdMail($message));
    }

    private function activeNumber($phone)
    {
        // strip non-digits from phone
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // strip leading '1' if it's 11 digits
        if (strlen($phone) == 11) {
            if (substr($phone, 0, 1) == '1') {
                $phone = substr($phone, 1);
            }
        }

        // if it's not 10 digits, return it as active
        if (strlen($phone) !== 10) {
            return true;
        }

        // Look it up
        $phones = $this->twilio->incomingPhoneNumbers
            ->read(
                array("phoneNumber" => $phone)
            );

        // Did we find it?
        if (collect($phones)->isEmpty()) {
            return false;
        }

        return true;
    }
}
