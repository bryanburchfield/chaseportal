<?php

namespace App\Services;

use App\Exports\ReportExport;
use App\Mail\CallerIdMail;
use App\Models\Dialer;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use GuzzleHttp\Client;
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
    private $maxcount;
    private $guzzleClient;
    private $calleridHeaders;

    // for storing numbers' flags
    private $numbers;

    // For tracking rate limiting
    private $apiRequests = [];
    private $apiLimitRequests = 60;
    private $apiLimitSeconds = 60;

    private function initialize()
    {
        $sid = config('twilio.did_sid');
        $did_token = config('twilio.did_token');

        $this->twilio = new Twilio($sid, $did_token);

        $this->guzzleClient = new Client();

        $token = config('calleridrep.token');

        $this->calleridHeaders = [
            'Authorization' => 'Bearer ' . $token,
        ];
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

        $tmpfname = tempnam("/tmp", "CID");

        // run report for >5.5k calls over 30 days

        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);
        $this->maxcount = 5500;

        // Save results to file
        $this->saveToFile($tmpfname);

        $group_id = '';
        $results = [];
        $all_results = [];

        // read results from file
        if (($handle = fopen($tmpfname, "r")) !== false) {
            while (($csv = fgetcsv($handle)) !== false) {
                $rec = $this->csvToRec($csv);

                // check if this number is still active
                // if ($this->activeNumber($rec['CallerId'])) {

                $rec['ContactRate'] = round($rec['Contacts'] / $rec['Dials'] * 100, 2) . '%';
                unset($rec['Contacts']);

                $rec['flagged_by'] = $this->checkFlagged($rec['CallerId']);

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
                // }  // active check
            }
            fclose($handle);
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

        // Now run report for >15.5k calls over 30 days

        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);
        $this->maxcount = 15500;

        // Save results to file
        $this->saveToFile($tmpfname);

        $all_results = [];

        // read results from file
        if (($handle = fopen($tmpfname, "r")) !== false) {
            while (($csv = fgetcsv($handle)) !== false) {
                $rec = $this->csvToRec($csv);

                // check if this number is still active
                // if ($this->activeNumber($rec['CallerId'])) {
                $rec['ContactRate'] = round($rec['Contacts'] / $rec['Dials'] * 100, 2) . '%';
                unset($rec['Contacts']);

                $rec['flagged_by'] = $this->checkFlagged($rec['CallerId']);

                $all_results[] = $rec;
                // } // active check
            }
            fclose($handle);
        }

        if (!empty($all_results)) {
            // clear group specific vars
            $this->setGroup();
            $csvfile = $this->makeCsv($all_results);
            $this->emailReport($csvfile);
        }

        // Delete temp file
        unlink($tmpfname);
    }

    private function csvToRec($csv)
    {
        $rec = [];

        $rec['GroupId'] = $csv[0];
        $rec['GroupName'] = $csv[1];
        $rec['CallerId'] = $csv[2];
        $rec['Dials'] = $csv[3];
        $rec['Contacts'] = $csv[4];

        return $rec;
    }

    private function saveToFile($tmpfname)
    {
        // Save results to file
        $handle = fopen($tmpfname, "w");

        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }
            fputcsv($handle, $rec);
        }

        fclose($handle);
    }

    private function runQuery()
    {
        $bind = [];
        $bind['maxcount'] = $this->maxcount;

        $sql = "SELECT GroupId, GroupName, CallerId, SUM(cnt) as Dials, SUM(Contacts) as Contacts FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('dialer_numb', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();
            $bind['inner_maxcount' . $i] = $this->maxcount;

            $sql .= " $union SELECT DR.GroupId, G.GroupName, DR.CallerId,
              'cnt' = COUNT(*),
              'Contacts' = SUM(CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END)
             FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
                WHERE DR.CallDate >= :startdate$i AND DR.CallDate < :enddate$i
                AND DR.CallerId != ''
                AND DR.CallType IN (0,2)
                AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD', 'Inbound')
                GROUP BY DR.GroupId, GroupName, CallerId
                HAVING COUNT(*) >= :inner_maxcount$i
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, CallerId
            HAVING SUM(cnt) >= :maxcount
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
            'Contact Rate',
            'Flagged By',
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
                'dylan.farley@chasedatacorp.com'
            ];
        } else {
            $to = $this->email_to;
            $cc = [];
        }

        // email report
        $message = [
            'subject' => 'Caller ID Report',
            'csv' => base64_encode($csv),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'enddate' => $this->enddate->toFormattedDateString(),
            'maxcount' => $this->maxcount,
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

    private function checkFlagged($phone)
    {
        if (!isset($this->numbers[$phone])) {
            $this->numbers[$phone] = $this->getFlags($phone);
        }

        return $this->numbers[$phone];
    }

    private function getFlags($phone)
    {
        $flags = [];

        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // Add leading '1' if 10 digits
        if (strlen($phone) == 10) {
            $phone = '1' . $phone;
        }

        // Add number

        if (!$this->waitToSend()) {
            return '';
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones/add';

        try {
            $response = $this->guzzleClient->request('POST', $endpoint, [
                'headers' => $this->calleridHeaders,
                'form_params' => [
                    'number' => $phone,
                    'description' => 'Test phone number',
                ],
            ]);
        } catch (Exception $e) {
            // don't care
        }

        // Check number

        // Wait a while after adding - seems to improve results
        sleep(10);

        if (!$this->waitToSend()) {
            return '';
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones/' . $phone;

        try {
            $response = $this->guzzleClient->request('GET', $endpoint, [
                'headers' => $this->calleridHeaders,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            $content = '';
        }

        if (is_array($content)) {
            foreach ($content as $key => $value) {
                if (substr($key, -8) == '_flagged' && $value == true) {
                    $flags[] = substr($key, 0, -8);
                }
            }
        }

        $flags = implode(',', $flags);

        // Delete number

        if (!$this->waitToSend()) {
            return $flags;
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones/' . $phone;

        try {
            $response = $this->guzzleClient->request('DELETE', $endpoint, [
                'headers' => $this->calleridHeaders,
            ]);
        } catch (Exception $e) {
            // don't really care
        }

        return $flags;
    }

    private function waitToSend()
    {
        // Check that we're not up against the API rate limit
        $i = 0;
        while (!$this->readyToSend()) {
            // check for infinite loop
            if ($i++ > ($this->apiLimitSeconds + 2)) {
                return false;
            }
            sleep(1);
        }

        return true;
    }

    private function readyToSend()
    {
        // count recent requests
        $count = 0;
        foreach ($this->apiRequests as $time) {
            if ($time >= (time() - $this->apiLimitSeconds)) {
                $count++;
            }
        }

        if ($count >= $this->apiLimitRequests) {
            return false;
        }

        // Ok to send!
        $this->apiRequests[] = time();
        return true;
    }

    // private function loadThinq()
    // {
    //     $this->thinqNumbers = [];

    //     $client = new Client(['base_uri' => 'https://api.thinq.com/']);

    //     $page = 1;
    //     while (true) {

    //         echo "get page $page\n";

    //         $response = $client->request(
    //             'GET',
    //             '/origination/did/search2/did/13446',
    //             [
    //                 'headers' => [
    //                     'Authorization' => 'Basic ' . 'Z3NhbmRvdmFsOjVhYWM4ODM1MWJiNDIxMWRhNjZmMjVlMzg4MDI5NTVhNjhiMjgwNWM',
    //                 ],
    //                 'query' => [
    //                     'page' => $page
    //                 ]
    //             ]
    //         );

    //         // Bail if we don't get a response
    //         if (!$response->getBody()) {
    //             break;
    //         }

    //         $results = json_decode($response->getBody()->getContents());

    //         echo 'got' . count($results->rows) . "\n";

    //         foreach ($results->rows as $rec) {
    //             $this->thinqNumbers[] = $rec->id;
    //         }

    //         // bail if this was the last page
    //         if (!$results->has_next_page) {
    //             break;
    //         }

    //         $page++;
    //     }

    //     Log::debug($this->thinqNumbers);
    //     die();
    // }
}
