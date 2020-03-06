<?php

namespace App\Services;

use App\Exports\ReportExport;
use App\Mail\CallerIdMail;
use App\Models\Dialer;
use App\Models\TwilioAreaCode;
use App\Models\TwilioNumber;
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
        echo "truncating\n";

        TwilioAreaCode::truncate();
        TwilioNumber::truncate();

        $sid = config('twilio.did_sid');
        $token = config('twilio.did_token');

        echo "Clinet $sid $token\n";

        $this->twilio = new Twilio($sid, $token);
    }

    private function setGroup($group_id = null)
    {
        echo "set group: $group_id\n";

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

                $all_results[] = $rec;

                // Send email on change of group
                if ($group_id != '' && $group_id != $rec['GroupId']) {

                    echo "done with $group_id\n";

                    if ($this->setGroup($group_id)) {

                        echo "emailing $group_id\n";

                        $csvfile = $this->makeCsv($results);
                        $this->emailReport($csvfile);
                    }

                    $results = [];
                }

                $results[] = $rec;
                $group_id = $rec['GroupId'];
            }
        }

        echo "last group $group_id\n";

        if (!empty($results)) {
            if ($this->setGroup($group_id)) {

                echo "emailing $group_id\n";

                $csvfile = $this->makeCsv($results);
                $this->emailReport($csvfile);
            }
        }

        if (!empty($all_results)) {

            echo "emailing all results\n";

            // clear group specific vars
            $this->setGroup();
            $csvfile = $this->makeCsv($all_results);
            $this->emailReport($csvfile);
        }
    }

    private function runQuery()
    {
        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);

        $bind = [];

        $sql = "SELECT GroupId, GroupName, CallerId, SUM(cnt) Dials FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('id', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();

            $sql .= " $union SELECT DR.GroupId, G.GroupName, DR.CallerId, COUNT(*) cnt FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                WHERE DR.Date >= :startdate$i and DR.Date < :enddate$i
                AND DR.CallerId != ''
                GROUP BY DR.GroupId, GroupName, CallerId
                HAVING COUNT(*) >= 5500
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, CallerId
            HAVING SUM(cnt) >= 5500
            ORDER BY GroupName, Dials desc";

        return $this->runSql($sql, $bind);
    }

    private function makeCsv($results)
    {
        $headers = [
            'GroupID',
            'GroupName',
            'CallerID',
            'Dials in Last 30 Days',
        ];

        array_unshift($results, $headers);

        // Create a uniquish filename
        $tempfile = '/' . uniqid() . '.csv';

        // this write to the directiory: storage_path('app')
        Excel::store(new ReportExport($results), $tempfile);

        return $tempfile;
    }

    // private function arrayData($array)
    // {
    //     $data = [];

    //     foreach ($array as $rec) {
    //         $data[] = array_values($rec);
    //     }

    //     return $data;
    // }

    private function emailReport($csvfile)
    {
        // path out file
        $csvfile = storage_path('app' . $csvfile);

        // read file into variable, then delete file
        $csv = file_get_contents($csvfile);
        unlink($csvfile);

        if ($this->email_to === null) {
            $to = 'jonathan.gryczka@chasedatacorp.com';
            $cc = 'ahmed@chasedatacorp.com';
        } else {
            $to = $this->email_to;
            $cc = 'bryan.burchfield@chasedatacorp.com';

            // $cc = [
            //     'jonathan.gryczka@chasedatacorp.com',
            //     'ahmed@chasedatacorp.com',
            // ];
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
            ->send(new CallerIdMail($message));
    }

    private function activeNumber($phone)
    {
        echo "incoming $phone\n";

        // strip non-digits from phone
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // if it's a bogus number, return it as active
        if (strlen($phone) !== 10) {
            return true;
        }

        // There are 8 chances any number could already be loaded
        for ($i = 0; $i < 8; $i++) {

            $twilio_areacode = TwilioAreaCode::find(substr($phone, $i, 3));

            if ($twilio_areacode) {
                break;
            }
        }

        // if we don't alrady have it, load up the tables
        if (!$twilio_areacode) {
            $areacode = substr($phone, 0, 3);

            echo "adding areacode $areacode\n";

            TwilioAreaCode::create(['areacode' => $areacode]);

            // this will get any number with those 3 digits
            $phones = $this->twilio->incomingPhoneNumbers
                ->read(
                    array("phoneNumber" => $areacode)
                );

            $phones = collect($phones);

            echo "Found " . $phones->count() . "\n";

            $phones->each(function ($item, $key) {
                // strip the "+1" off the number
                // try/catch is probably faster than firstOrCreate()
                try {
                    TwilioNumber::create(['phone' => substr($item->phoneNumber, 2)]);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });

            echo "Inserted\n";
        }

        return TwilioNumber::find($phone);
    }
}
