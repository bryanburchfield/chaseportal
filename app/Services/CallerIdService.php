<?php

namespace App\Services;

use App\Mail\CallerIdMail;
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

        $this->guzzleClient = new Client();

        $token = config('calleridrep.token');

        $this->calleridHeaders = [
            'Authorization' => 'Bearer ' . $token,
        ];

        // // Clear out our calleridrep.com db
        $this->clearCallerIdRepPhones();
    }

    public static function execute()
    {
        $caller_id_service = new CallerIdService();
        $caller_id_service->runReport();
    }

    public function runReport()
    {
        Log::info('Starting CallerID report');

        $this->initialize();

        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);
        $this->maxcount = 1000;

        echo "Pulling report\n";
        Log::info('Pulling report');
        $this->saveToDb();

        echo "Checking flags\n";
        Log::info('Checking flags');
        $this->checkFlags();

        // echo "Swap Numbers\n";
        // Log::info('Swapping Numbers');
        // $this->swapNumbers();

        echo "Creating report\n";
        Log::info('Creating report');
        $this->mainReport();
    }

    private function mainReport()
    {
        $all_results = [];

        // read results from db
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->get() as $rec) {
            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

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
                    'in_system' => $rec['Active'],
                    'callerid_check' => $rec['CallerIdCheck'],
                    'calls' => $rec['Dials'],
                    'connect_ratio' => $rec['Connects'] / $rec['Dials'] * 100,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating PhoneFlag: ' . $phone);
            }
        }
    }

    private function formatPhone($phone, $strip1 = false)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        if ($strip1) {
            // Strip leading '1' if 11 digits
            if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
                $phone = substr($phone, 1);
            }
        } else {
            // Add leading '1' if 10 digits
            if (strlen($phone) == 10) {
                $phone = '1' . $phone;
            }
        }

        return $phone;
    }

    private function runQuery()
    {
        $bind = [];
        $bind['maxcount'] = $this->maxcount;

        $sql = "SELECT GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Active, SUM(cnt) as Dials, SUM(Connects) as Connects FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('dialer_numb', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();
            $bind['inner_maxcount' . $i] = $this->maxcount;

            $sql .= " $union SELECT
                DR.GroupId,
                G.GroupName, " .
                $dialer->dialer_numb . " as DialerNumb,
                DR.CallerId,
                I.Description as RingGroup,
                O.CallerIdCheck,
                'Active' = CASE WHEN I.InboundSource IS NOT NULL and O.Active IS NOT NULL THEN 1 ELSE 0 END,
                'cnt' = COUNT(*),
                'Connects' = SUM(CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END)
             FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
                LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = DR.GroupId AND I.InboundSource = DR.CallerId
                OUTER APPLY (
                    SELECT TOP 1 O.Active, O.CallerIdCheck
                    FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                    WHERE O.GroupId = DR.GroupId AND O.Phone = DR.CallerId) as O
                WHERE DR.CallDate >= :startdate$i AND DR.CallDate < :enddate$i
                AND DR.CallerId != ''
                AND DR.CallType IN (0,2)
                AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
                GROUP BY DR.GroupId, GroupName, CallerId, I.Description, O.CallerIdCheck, I.InboundSource, O.Active
                HAVING COUNT(*) >= :inner_maxcount$i
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Active
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
            // 'CallerIdCheck',
            'RingGroup',
            'Dials in Last 30 Days',
            'Connect Ratio',
            'Active',
            'Flagged',
            'Flags',
            'Replaced By',
            'Error',
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
                // $rec->callerid_check,
                $rec->ring_group,
                $rec->calls,
                $rec->connect_ratio,
                $rec->in_system,
                $rec->flagged,
                $rec->flags,
                $rec->replaced_by,
                $rec->swap_error,
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

    private function checkFlags()
    {
        $batch = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('checked', 0)
            ->where('in_system', 1)
            // ->where('callerid_check', 1)     uncomment when this is working
            ->select('phone')->distinct()
            ->orderBy('phone')
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

    private function checkBatch($batch)
    {
        echo "check batch of " . count($batch) . "\n";

        // upload batch to calleridrep
        foreach ($batch as $phone) {
            $this->addNumber($phone);
        }

        // get details for each number
        foreach ($batch as $phone) {
            echo "checking $phone\n";

            $flags = $this->checkNumber($phone);

            // Update db
            PhoneFlag::where('run_date', $this->run_date)
                ->where('phone', $phone)
                ->update([
                    'checked' => 1,
                    'flagged' => empty($flags) ? 0 : 1,
                    'flags' => $flags
                ]);
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
            $flags .= empty($content['ftc_flagged']) ? '' : ',FTC';
            $flags .= empty($content['ihs_flagged']) ? '' : ',ICEHOOK';
            $flags .= empty($content['nomorobo_flagged']) ? '' : ',NOMOROBO';
            $flags .= empty($content['robokiller_flagged']) ? '' : ',ROBOKILLER';
            $flags .= empty($content['telo_flagged']) ? '' : ',TELO';
            $flags .= empty($content['tts_flagged']) ? '' : ',TrueSpam';

            if (!empty($flags)) {
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

    private function swapNumbers()
    {
        $client = new Client();

        // read results from db
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('flagged', 1)
            // ->where('callerid_check', 1)      future enhancement
            ->whereIn('dialer_numb', [7, 24, 26])   // Supported servers by API
            ->whereIn('group_id', [236163])         // Supported groups
            ->orderBy('dialer_numb')
            ->orderBy('phone')
            ->get() as $rec) {

            if ($rec->group_id == 236163 && ($rec->ring_group == 'Branson Nationwide' || $rec->ring_group == 'Springfield Nationwide')) {
                $this->swapNumber($client, $rec);
            }
        }
    }

    private function swapNumber(Client $client, PhoneFlag $phoneFlag)
    {
        echo "Swapping " . $phoneFlag->phone . "\n";

        $error = null;
        $replaced_by = null;

        try {
            $response = $client->get(
                'https://billing.chasedatacorp.com/DID.aspx',
                [
                    'query' => [
                        'Token' => '3DCE9183-CA9C-4D1A-8B23-171DFA8C4D4B',
                        'Server' => 'dialer-' . sprintf('%02d', $phoneFlag->dialer_numb, 2),
                        'Number' => $this->formatPhone($phoneFlag->phone, 1),
                        'GroupId' => $phoneFlag->group_id,
                        'Action' => 'swap',
                    ]
                ]
            );
        } catch (Exception $e) {
            $error = 'Swap API failed: ' . $e->getMessage();
        }

        if (empty($error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                dump($body);  // debug

                if (!empty($body->NewDID)) {
                    $replaced_by = $body->NewDID;
                }
                if (!empty($body->Error)) {
                    $error = $body->Error;
                }
            } catch (\Throwable $th) {
                $error = 'Could not swap number: ' . $e->getMessage();
            }
        }

        // update db
        $phoneFlag->swap_error = $error;
        $phoneFlag->replaced_by = $replaced_by;
        $phoneFlag->save();
    }
}
