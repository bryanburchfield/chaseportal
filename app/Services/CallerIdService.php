<?php

namespace App\Services;

use App\Mail\CallerIdMail;
use App\Models\AreaCode;
use App\Models\Dialer;
use App\Models\PhoneFlag;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
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

    // For Twilio 
    private $twilio;

    // For Truespam 
    private $truespam;
    private $truespamScore = 40;          // min score to be considered spam
    private $truespamRequests = [];       // rate limiting -- 600 per minute
    private $truespamLimitRequests = 600;
    private $truespamLimitSeconds = 60;

    // For stamping db
    private $run_date;

    private function initialize()
    {
        $this->run_date = now();

        $this->twilio = new Twilio(
            config('twilio.spam_sid'),
            config('twilio.spam_token')
        );

        $this->truespam = new Client([
            'base_uri' => 'https://api.truecnam.net',
            'defaults' => [
                'query' => [
                    'username' => config('truespam.key'),
                    'password' => config('truespam.password'),
                    'resp_type' => 'extended',
                    'resp_format' => 'json',
                ]
            ]
        ]);
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
        $this->maxcount = 0;

        echo "Pulling report\n";
        Log::info('Pulling report');
        $this->saveToDb();

        echo "Checking flags\n";
        Log::info('Checking flags');
        $this->checkFlags();

        echo "Swap Numbers\n";
        Log::info('Swapping Numbers');
        $this->swapNumbers();

        echo "Creating report\n";
        Log::info('Creating report');
        $this->createReport();
    }

    private function createReport()
    {
        // big report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {
            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $mainCsv = $this->makeCsv($all_results);

        // autoswap report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereIn('dialer_numb', [7, 24, 26])   // Supported servers by API
            ->where(function ($query) {
                $query->where('ring_group', 'like', '%Caller%Id%Call%back%')
                    ->orWhere('ring_group', 'like', '%Nationwide%');
            })
            ->where(function ($query) {
                $query->where('flagged', 1)
                    // ->orWhere(function ($query2) {
                    //     $query2->where('calls', '>=', 600)
                    //         ->where('calls', '<', 1000)
                    //         ->where('connect_ratio', '<', 10);
                    // })
                    ->orWhere(function ($query2) {
                        $query2->where('calls', '>=', 1000)
                            ->where('connect_ratio', '<', 13);
                    });
            })
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $autoswapCsv = $this->makeCsv($all_results);

        // manual swap report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereNotIn('dialer_numb', [7, 24, 26])   // Supported servers by API
            ->where(function ($query) {
                $query->where('ring_group', 'like', '%Caller%Id%Call%back%')
                    ->orWhere('ring_group', 'like', '%Nationwide%');
            })
            ->where(function ($query) {
                $query->where('flagged', 1)
                    // ->orWhere(function ($query2) {
                    //     $query2->where('calls', '>=', 600)
                    //         ->where('calls', '<', 1000)
                    //         ->where('connect_ratio', '<', 10);
                    // })
                    ->orWhere(function ($query2) {
                        $query2->where('calls', '>=', 1000)
                            ->where('connect_ratio', '<', 13);
                    });
            })
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $manualswapCsv = $this->makeCsv($all_results);

        $this->emailReport($mainCsv, $autoswapCsv, $manualswapCsv);
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
                    'owned' => $rec['Owned'],
                    'callerid_check' => $rec['CallerIdCheck'],
                    'calls' => $rec['Dials'],
                    'connects' => $rec['Connects'],
                    'connect_ratio' => $rec['Connects'] / $rec['Dials'] * 100,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating PhoneFlag: ' . $phone);
                Log::critical($e->getMessage());
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
        // Have to hard-code what's considered 'system' for connect calculations
        $system_codes = "'CR_BAD_NUMBER',
'CR_BUSY',
'CR_CEPT',
'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PAMD',
'CR_CNCT/CON_PVD',
'CR_DISCONNECTED',
'CR_DROPPED',
'CR_ERROR',
'CR_FAILED',
'CR_FAXTONE',
'CR_HANGUP',
'CR_NOANS',
'CR_NORB',
'CR_UNFINISHED',
'SYS_CALLBACK',
'Inbound Voicemail',
'Inbound Transfer',
'UNKNOWN',
'UNFINISHED',
'Skip',
'TRANSFERRED',
'PARKED',
'Answering Machine'
        ";

        $bind = [];
        $bind['maxcount'] = $this->maxcount;

        $sql = "SET NOCOUNT ON;
        SELECT DISTINCT Phone, CallerIdCheck INTO #phones FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {

            $sql .= " $union SELECT O.phone, MAX(CAST(CallerIdCheck as INT)) as CallerIdCheck
                FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] S on S.InboundSource = O.phone
                WHERE O.Active = 1
                GROUP BY Phone";

            $union = 'UNION';
        }

        $sql .= ") tmp
        
        SELECT GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Owned, SUM(cnt) as Dials, SUM(Connects) as Connects FROM (";

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
                TP.CallerIdCheck,
                'Owned' = CASE WHEN TP.Phone IS NOT NULL THEN 1 ELSE 0 END,
                'cnt' = COUNT(*),
                SUM(CASE WHEN DR.CallStatus NOT IN ($system_codes) THEN 1 ELSE 0 END) as Connects
            FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR
            INNER JOIN [" . $dialer->reporting_db . "].[dbo].[Groups] G on G.GroupId = DR.GroupId AND G.IsActive = 1
            LEFT JOIN #phones TP ON TP.Phone = DR.CallerId
            LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = DR.GroupId AND I.InboundSource = DR.CallerId
            CROSS APPLY (
                SELECT TOP 1 O.Active
                FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                WHERE O.GroupId = DR.GroupId AND O.Phone = DR.CallerId
                ORDER BY O.Active DESC) as O
            WHERE DR.CallDate >= :startdate$i AND DR.CallDate < :enddate$i
            AND DR.CallerId != ''
            AND DR.CallType IN (0,2)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
            AND O.Active = 1
            GROUP BY DR.GroupId, GroupName, CallerId, I.Description, TP.CallerIdCheck, I.InboundSource, O.Active, TP.Phone
            HAVING COUNT(*) >= :inner_maxcount$i
                ";

            $union = 'UNION';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Owned
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
            'Owned',
            'Flagged',
            // 'Flags',
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
                $rec->owned,
                $rec->flagged,
                // $rec->flags,
                $rec->replaced_by,
                $rec->swap_error,
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return $tempfile;
    }

    private function emailReport($mainCsv, $autoswapCsv, $manualswapCsv)
    {
        // read files into variables, then delete files
        $mainData = file_get_contents($mainCsv);
        $autoswapData = file_get_contents($autoswapCsv);
        $manualswapData = file_get_contents($manualswapCsv);

        unlink($mainCsv);
        unlink($autoswapCsv);
        unlink($manualswapCsv);

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
            'mainCsv' => base64_encode($mainData),
            'autoswapCsv' => base64_encode($autoswapData),
            'manualswapCsv' => base64_encode($manualswapData),
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
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('checked', 0)
            // ->where('callerid_check', 1)     uncomment when this is working
            ->select('phone')->distinct()
            ->orderBy('phone')
            ->get() as $rec) {

            $phone = $this->formatPhone($rec['phone']);

            $flags = $this->checkNumber($phone);
            $flagged = !empty($flags);

            // update all matching numbers
            PhoneFlag::where('run_date', $this->run_date)
                ->where('phone', $phone)
                ->update([
                    'checked' => 1,
                    'flags' => $flags,
                    'flagged' => $flagged
                ]);
        }
    }

    private function checkNumber($phone)
    {
        echo "Check $phone\n";

        $flags = null;

        // check from cheapest to most expensive

        if ($this->checkTruespam($phone)) {
            $flags .= ',Truespam';
            return 'Truespam';  // bail early
        }

        if ($this->checkNomorobo($phone)) {
            $flags .= ',Nomorobo';
            return 'Nomorobo';  // bail early
        }

        if (!empty($flags)) {
            $flags = substr($flags, 1);
        }

        return $flags;
    }

    private function checkTruespam($phone)
    {
        if (!$this->waitToSend($this->truespamRequests, $this->truespamLimitRequests, $this->truespamLimitSeconds)) {
            return false;
        }

        $query = ['query' => ['calling_number' => $phone]];

        try {
            $response = $this->truespam->get(
                '/api/v1',
                array_merge_recursive($query, $this->truespam->getConfig('defaults'))
            );
        } catch (Exception $e) {
            Log::error('TrueSpam error: ' . $e->getMessage());
            return false;
        }

        // check response
        $body = $this->objectToArray(json_decode($response->getBody()->getContents()));

        if ((int)Arr::get($body, 'err') !== 0) {
            Log::error('Truspam error: ' . Arr::get($body, 'error_msg'));
            return false;
        }

        if ((int)Arr::get($body, 'spam_score') >= $this->truespamScore) {
            return true;
        }

        return false;
    }

    private function checkNomorobo($phone)
    {
        try {
            $result = $this->twilio->lookups->v1->phoneNumbers('+' . $phone)->fetch(['addOns' => ['nomorobo_spamscore']]);
        } catch (Exception $e) {
            Log::error('Twilio lookup failed: ' . $e->getMessage());
            return false;
        }

        $result = $this->objectToArray($result->addOns);

        $nomoroboData = Arr::get($result, 'results.nomorobo_spamscore');

        if (Arr::get($nomoroboData, 'status') == 'failed') {
            Log::error('Nomorobo error: ' . Arr::get($nomoroboData, 'message'));
            return false;
        }

        $spamScore = (int)Arr::get($nomoroboData, 'result.score');

        return $spamScore >= 1;
    }

    private function swapNumbers()
    {
        $client = new Client();

        // read results from db
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereIn('dialer_numb', [7, 24, 26])   // Supported servers by API
            ->where(function ($query) {
                $query->where('ring_group', 'like', '%Caller%Id%Call%back%')
                    ->orWhere('ring_group', 'like', '%Nationwide%');
            })
            ->where(function ($query) {
                $query->where('flagged', 1)
                    // ->orWhere(function ($query2) {
                    //     $query2->where('calls', '>=', 600)
                    //         ->where('connect_ratio', '<', 10);
                    // })
                    ->orWhere(function ($query2) {
                        $query2->where('calls', '>=', 1000)
                            ->where('connect_ratio', '<', 13);
                    });
            })
            ->orderBy('dialer_numb')
            ->orderBy('phone')
            ->get() as $rec) {

            $this->swapNumber($client, $rec);
        }
    }

    private function swapNumber(Client $client, PhoneFlag $phoneFlag)
    {
        // try to replace with same NPA
        if (!$this->swapNumberNpa($client, $phoneFlag)) {

            // Find area code record
            $npa = substr($this->formatPhone($phoneFlag->phone, true), 0, 3);
            $areaCode = AreaCode::find($npa);

            if ($areaCode) {
                // get list of nearby same state npas
                $alternates = $areaCode->alternateNpas();

                // loop through till swap succeeds or errors
                foreach ($alternates as $alternate) {
                    if ($this->swapNumberNpa($client, $phoneFlag, $alternate->npa)) {
                        break;
                    }
                }
            }
        }
    }

    private function swapNumberNpa(Client $client, PhoneFlag $phoneFlag, $npa = null)
    {
        echo "Swapping " . $phoneFlag->phone . " $npa\n";

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
                        'NPA' => $npa
                    ]
                ]
            );
        } catch (Exception $e) {
            $return_code = -1;
            $error = 'Swap API failed: ' . $e->getMessage();
        }

        if (empty($error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (isset($body->NewDID)) {
                    if (!empty($body->NewDID)) {
                        $return_code = 1;
                        $replaced_by = $body->NewDID;
                    } else {
                        $return_code = 0;
                        $error = 'No repalcement available';
                    }
                }
                if (!empty($body->Error)) {
                    $return_code = -1;
                    $error = $body->Error;
                }
            } catch (\Throwable $th) {
                $return_code = -1;
                $error = 'Could not swap number: ' . $e->getMessage();
            }
        }

        // update db
        $phoneFlag->replaced_by = $replaced_by;
        $phoneFlag->swap_error = $error;
        $phoneFlag->save();

        return $return_code;
    }

    private function waitToSend($apiLog, $limitRequests, $limitSeconds)
    {
        // Check that we're not up against the API rate limit
        $i = 0;
        while (!$this->readyToSend($apiLog, $limitRequests, $limitSeconds)) {
            // check for infinite loop
            if ($i++ > ($limitSeconds + 1)) {
                return false;
            }
            sleep(1);
        }

        return true;
    }

    private function readyToSend($apiLog, $limitRequests, $limitSeconds)
    {
        // count recent requests
        $count = 0;
        foreach ($apiLog as $time) {
            if ($time >= (time() - $limitSeconds)) {
                $count++;
            }
        }

        if ($count >= $limitRequests) {
            return false;
        }

        // Ok to send!
        $apiLog[] = time();
        return true;
    }

    private function objectToArray($obj)
    {
        return @json_decode(json_encode($obj), true);
    }
}
