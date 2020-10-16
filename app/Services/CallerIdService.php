<?php

namespace App\Services;

use App\Mail\CallerIdMail;
use App\Models\ActiveNumber;
use App\Models\Dialer;
use App\Models\PhoneFlag;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as Twilio;

class CallerIdService
{
    use SqlServerTraits;
    use TimeTraits;

    private $startdate;
    private $enddate;
    private $maxcount;
    private $guzzleClient;
    private $calleridHeaders;

    // For stamping db
    private $run_date;

    // For tracking rate limiting
    private $apiRequests = [];
    private $apiLimitRequests = 60;
    private $apiLimitSeconds = 60;

    private function initialize()
    {
        $this->run_date = now();

        $sid = config('twilio.did_sid');
        $did_token = config('twilio.did_token');

        $this->twilio = new Twilio($sid, $did_token);

        $this->guzzleClient = new Client();

        $token = config('calleridrep.token');

        $this->calleridHeaders = [
            'Authorization' => 'Bearer ' . $token,
        ];

        // Clear out active_numbers table
        ActiveNumber::truncate();

        // // Clear out our calleridrep.com db
        $this->clearCallerIdRepPhones();

        // // Load numbers from vendors
        $this->loadThinq();
        $this->loadVoipMs();
    }

    public static function execute()
    {
        $caller_id_service = new CallerIdService();
        $caller_id_service->runReport();
    }

    public function runReport()
    {
        $this->initialize();

        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);
        $this->maxcount = 5500;

        echo "Pulling report\n";
        $this->saveToDb();

        echo "Checking actives\n";
        $this->checkActive();

        echo "Checking flags\n";
        $this->checkFlags();

        echo "Creating 5500 report\n";
        $this->report5500();
    }

    private function report5500()
    {
        $all_results = [];

        // read results from db
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('calls', '>=', 5500)
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('phone')
            ->get() as $rec) {
            $rec['contact_ratio'] = round($rec['contact_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        if (!empty($all_results)) {
            $csvfile = $this->makeCsv($all_results);
            $this->emailReport($csvfile);
        }
    }

    private function saveToDb()
    {
        // Save results to database
        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhone($rec['CallerId']);

            try {
                PhoneFlag::create([
                    'run_date' => $this->run_date,
                    'group_id' => $rec['GroupId'],
                    'group_name' => $rec['GroupName'],
                    'dialer_numb' => $rec['DialerNumb'],
                    'phone' => $phone,
                    'ring_group' => $rec['RingGroup'],
                    'calls' => $rec['Dials'],
                    'contact_ratio' => $rec['Contacts'] / $rec['Dials'] * 100,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating PhoneFlag: ' . $phone);
            }
        }
    }

    private function formatPhone($phone)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // Add leading '1' if 10 digits
        if (strlen($phone) == 10) {
            $phone = '1' . $phone;
        }

        return $phone;
    }

    private function runQuery()
    {
        $bind = [];
        $bind['maxcount'] = $this->maxcount;

        $sql = "SELECT GroupId, GroupName, DialerNumb, CallerId, RingGroup, SUM(cnt) as Dials, SUM(Contacts) as Contacts FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('dialer_numb', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();
            $bind['inner_maxcount' . $i] = $this->maxcount;

            $sql .= " $union SELECT DR.GroupId, G.GroupName, " . $dialer->dialer_numb . " as DialerNumb,
               DR.CallerId, I.Description as RingGroup,
              'cnt' = COUNT(*),
              'Contacts' = SUM(CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END)
             FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
                LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = DR.GroupId AND I.InboundSource = DR.CallerId
                WHERE DR.CallDate >= :startdate$i AND DR.CallDate < :enddate$i
                AND DR.CallerId != ''
                AND DR.CallType IN (0,2)
                AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
                GROUP BY DR.GroupId, GroupName, CallerId, I.Description
                HAVING COUNT(*) >= :inner_maxcount$i
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, DialerNumb, CallerId, RingGroup
            HAVING SUM(cnt) >= :maxcount";

        return $this->runSql($sql, $bind);
    }

    private function makeCsv($results)
    {
        $headers = [
            'Dialer',
            'GroupName',
            'GroupID',
            'CallerID',
            'RingGroup',
            'Dials in Last 30 Days',
            'Contact Ratio',
            'In System',
            'Flagged',
            'Flags',
            // 'Replaced By',
        ];

        // write to file
        $tempfile = tempnam("/tmp", "CID");
        $handle = fopen($tempfile, "w");

        fputcsv($handle, $headers);

        foreach ($results as $rec) {
            $row = [
                $rec->dialer_numb,
                $rec->group_name,
                $rec->group_id,
                $rec->phone,
                $rec->ring_group,
                $rec->calls,
                $rec->contact_ratio,
                $rec->in_system,
                $rec->flagged,
                $rec->flags,
                // $rec->replaced_by,
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return $tempfile;
    }

    private function emailReport($csvfile)
    {
        // read file into variable, then delete file
        $csv = file_get_contents($csvfile);
        unlink($csvfile);

        $to = 'jonathan.gryczka@chasedatacorp.com';
        $cc = [
            'g.sandoval@chasedatacorp.com',
            'brandon.b@chasedatacorp.com',
            'ahmed@chasedatacorp.com',
            'dylan.farley@chasedatacorp.com'
        ];

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

    private function checkActive()
    {
        foreach (PhoneFlag::where('run_date', $this->run_date)->select('phone')->distinct()->get() as $rec) {
            if ($this->activeNumber($rec->phone)) {
                PhoneFlag::where('run_date', $this->run_date)
                    ->where('phone', $rec->phone)
                    ->update(['in_system' => 1]);
            }
        }
    }

    private function checkFlags()
    {
        $batch = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('checked', 0)
            ->select('phone')->distinct()
            ->get() as $rec) {

            $batch[] = $rec->phone;

            // Check numbers in batches of 490
            if (count($batch) >= 490) {
                $this->checkBatch($batch);
                $batch = [];
            }
        }

        // Check what's left
        if (!empty($batch)) {
            $this->checkBatch($batch);
        }
    }

    private function activeNumber($phone)
    {
        if ($this->activeOtherVendor($phone)) {
            return true;
        }

        if ($this->activeTwilio($phone)) {
            return true;
        }

        return false;
    }

    private function activeOtherVendor($phone)
    {
        $active_number = ActiveNumber::find($phone);

        if ($active_number) {
            return true;
        }

        return false;
    }

    private function activeTwilio($phone)
    {
        // strip leading '1' if it's 11 digits
        if (strlen($phone) == 11) {
            if (substr($phone, 0, 1) == '1') {
                $phone = substr($phone, 1);
            }
        }

        // if it's not 10 digits, return it as inactive
        if (strlen($phone) !== 10) {
            return false;
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

    private function checkBatch($batch)
    {
        echo "check batch of " . count($batch) . "\n";

        // upload batch to calleridrep
        foreach ($batch as $phone) {
            $this->addNumber($phone);
        }

        // wait for them to process the numbers
        echo "Waiting 10 secs for them to process....\n";
        sleep(10);

        // Get list of all phones w/ flagged column
        $phones = $this->getAllCallerIdRepPhones();

        // Update db
        foreach ($phones as $rec) {
            $phone = $this->formatPhone($rec['number']);

            PhoneFlag::where('run_date', $this->run_date)
                ->where('phone', $phone)
                ->update(['checked' => 1, 'flagged' => $rec['flagged']]);
        }

        // Now get details for each number
        foreach ($batch as $phone) {
            echo "checking $phone\n";

            $flags = $this->checkNumber($phone);

            if (!empty($flags)) {
                // Update db
                PhoneFlag::where('run_date', $this->run_date)
                    ->where('phone', $phone)
                    ->update(['flags' => $flags]);
            }
        }

        // clear em out
        $this->clearCallerIdRepPhones();
    }

    private function addNumber($phone)
    {
        echo "add number $phone\n";

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
            Log::error('Error uploading number ' . $phone);
        }
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

    private function getAllCallerIdRepPhones()
    {
        if (!$this->waitToSend()) {
            return [];
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones';

        $content = '';
        try {
            $response = $this->guzzleClient->request('GET', $endpoint, [
                'headers' => $this->calleridHeaders,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        }

        return $content;
    }

    private function checkNumber($phone)
    {
        if (!$this->waitToSend()) {
            return null;
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones/' . $phone;

        $content = null;
        $flags = null;

        try {
            $response = $this->guzzleClient->request('GET', $endpoint, [
                'headers' => $this->calleridHeaders,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        }

        if (is_array($content)) {
            $flags = '';
            $flags .= empty($content['ftc_flagged']) ? '' : ',FTC';
            $flags .= empty($content['nomorobo_flagged']) ? '' : ',NOMOROBO';
            $flags .= empty($content['ihs_flagged']) ? '' : ',ICEHOOK';
            $flags .= empty($content['tts_flagged']) ? '' : ',TrueSpam';
            $flags .= empty($content['telo_flagged']) ? '' : ',TELO';

            if (empty($flags)) {
                $flags = null;
            } else {
                $flags = substr($flags, 1);
            }
        }

        return $flags;
    }

    private function clearCallerIdRepPhones()
    {
        echo "delete all caller id rep phones\n";

        $phones = $this->getAllCallerIdRepPhones();

        if (is_array($phones)) {
            foreach ($phones as $rec) {
                $this->deletePhone($rec['number']);
            }
        }
    }

    private function deletePhone($phone)
    {
        echo "delete phone $phone\n";

        if (!$this->waitToSend()) {
            return;
        }

        $endpoint = 'https://app.calleridrep.com/api/v1/phones/' . $phone;

        try {
            $response = $this->guzzleClient->request('DELETE', $endpoint, [
                'headers' => $this->calleridHeaders,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error($responseBodyAsString);
        }
    }

    private function loadThinq()
    {
        echo "loading thinq\n";

        $client = new Client(['base_uri' => 'https://api.thinq.com/']);

        $page = 1;
        while (true) {
            echo "...page $page\n";

            $response = $client->request(
                'GET',
                '/origination/did/search2/did/13446',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . 'Z3NhbmRvdmFsOjVhYWM4ODM1MWJiNDIxMWRhNjZmMjVlMzg4MDI5NTVhNjhiMjgwNWM',
                    ],
                    'query' => [
                        'page' => $page
                    ]
                ]
            );

            // Bail if we don't get a response
            if (!$response->getBody()) {
                Log::error('Could not get Thinq numbers');
                break;
            }

            $results = json_decode($response->getBody()->getContents());

            if (!isset($results->rows)) {
                Log::error('Thinq rows not found');
                return;
            }

            foreach ($results->rows as $rec) {
                $phone = $this->formatPhone($rec->id);
                try {
                    ActiveNumber::create(['phone' => $phone, 'vendor' => 'thinq']);
                } catch (Exception $e) {
                    Log::error('Cant insert thinq number: ' . $phone);
                }
            }

            // bail if this was the last page
            if (!$results->has_next_page) {
                break;
            }

            $page++;
        }
    }

    private function loadVoipMs()
    {
        echo "loading voip.ms\n";

        $client = new Client(['base_uri' => 'https://voip.ms/']);

        $response = $client->request(
            'GET',
            '/api/v1/rest.php',
            [
                'query' => [
                    'api_username' => 'g.sandoval@chasedatacorp.com',
                    'api_password' => 'bdyvAJbxgJf4Z',
                    'method' => 'getDIDsInfo'
                ]
            ]
        );

        // Bail if we don't get a response
        if (!$response->getBody()) {
            Log::error('Could not get voip.ms numbers');
        }

        $results = json_decode($response->getBody()->getContents());

        if (!isset($results->dids)) {
            Log::error('voip.ms dids not found');
            return;
        }

        foreach ($results->dids as $rec) {
            $phone = $this->formatPhone($rec->did);
            try {
                ActiveNumber::create(['phone' => $phone, 'vendor' => 'voip.ms']);
            } catch (Exception $e) {
                Log::error('Cant insert voip.ms number: ' . $phone);
            }
        }
    }
}
