<?php

namespace App\Services;

use App\Mail\InternalSpamCheckMail;
use App\Models\AreaCode;
use App\Models\Dialer;
use App\Models\InternalPhoneFlag;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InternalSpamCheckService
{
    use SqlServerTraits;
    use TimeTraits;

    private $period;
    private $run_date;
    private $startdate;

    // Groups to exclude
    private $ignoreGroups =
    [
        224195,
        236163,
        2256969,
    ];

    public static function execute($period)
    {
        $caller_id_service = new InternalSpamCheckService();
        $caller_id_service->runReport($period);
    }

    private function initialize()
    {
        $this->run_date = now();
        $this->startdate = today();
    }

    public function runReport($period)
    {
        $this->period = $period;
        Log::info('Starting Internal Spam Check - ' . $period);

        $this->initialize();

        echo "Pulling report\n";
        Log::info('Pulling report');
        $this->saveReport();

        // echo "Swap Numbers\n";
        // Log::info('Swapping numbers');
        // $this->swapNumbers();

        echo "Creating report\n";
        Log::info('Creating report');
        $this->createReport();

        echo "Finished\n";
        Log::info('Finished');
    }

    private function saveReport()
    {
        // Save results to database
        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhone($rec['Phone']);

            try {
                InternalPhoneFlag::create([
                    'run_date' => $this->run_date,
                    'group_id' => $rec['GroupId'],
                    'group_name' => $rec['GroupName'],
                    'dialer_numb' => $rec['Dialer'],
                    'phone' => $phone,
                    'ring_group' => $rec['Description'],
                ]);
            } catch (Exception $e) {
                Log::error('Error creating InternalPhoneFlag: ' . $phone);
                Log::critical($e->getMessage());
            }
        }
    }

    private function createReport()
    {
        $all_results = [];

        foreach (InternalPhoneFlag::where('run_date', $this->run_date)
            ->orderBy('group_name')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['phone'] = $this->formatPhone($rec['phone'], true);
            $rec['replaced_by'] = $this->formatPhone($rec['replaced_by'], true);

            $all_results[] = $rec;
        }

        $mainCsv = $this->makeCsv($all_results);

        $this->emailReport($mainCsv);
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
        // Make list of groups to ignore
        $ignoreGroups = implode(',', $this->ignoreGroups);

        $bind = [];
        $bind['startdate1'] = $this->startdate->toDateTimeString();
        $bind['startdate2'] = $bind['startdate1'];

        $sql = "SET NOCOUNT ON;

        SELECT * INTO #busy FROM (
            SELECT DISTINCT CallerId
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
            AND CallDate >= :startdate1
            AND Campaign = 'TOP_1500_USED_DIDS'
            AND CallStatus = 'CR_BUSY'
        ) tmp

        SELECT * INTO #remotehangups FROM (
            SELECT DISTINCT CallerId
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
            AND CallDate >= :startdate2
            AND Campaign = 'TOP_1500_USED_DIDS'
            AND sip_bye = 1
        ) tmp

        SELECT DISTINCT CallerId INTO #bad FROM (
            SELECT CallerId FROM #busy
            UNION
            SELECT CallerId FROM #remotehangups
        ) tmp

        SELECT * INTO #activebad FROM (
            SELECT Dialer, GroupId, GroupName, Phone, Description FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {

            $sql .= " $union SELECT " . $dialer->dialer_numb . " as Dialer, O.GroupId, GroupName, O.Phone, I.Description
                FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = O.GroupId AND I.InboundSource = O.phone
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[Groups] G ON G.GroupId = O.GroupId
                INNER JOIN #bad ON #bad.CallerId = O.Phone
                WHERE O.Active = 1
                AND O.GroupId NOT IN ($ignoreGroups)
                AND (I.Description like '%caller%id%call%back%' or I.Description like '%nationwide%')";

            $union = 'UNION';
        }

        $sql .= ") tmp ) activebad

        SELECT *
        FROM #activebad";

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
            'Replaced By',
            'Error',
        ];

        // write to file
        $tempfile = tempnam("/tmp", "ISC");
        $handle = fopen($tempfile, "w");

        fputcsv($handle, $headers);

        foreach ($results as $rec) {
            $row = [
                $rec->dialer_numb,
                $rec->group_name,
                $rec->group_id,
                $rec->phone,
                $rec->ring_group,
                $rec->replaced_by,
                $rec->swap_error,
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return $tempfile;
    }

    private function emailReport($mainCsv)
    {
        // read files into variables, then delete files
        $mainData = file_get_contents($mainCsv);

        unlink($mainCsv);

        $to = 'ahmed@chasedatacorp.com';
        $cc = [
            'g.sandoval@chasedatacorp.com',
            'brandon.b@chasedatacorp.com'
        ];

        // email report
        $message = [
            'subject' => 'Internal Spam Check Report - ' . $this->period,
            'mainCsv' => base64_encode($mainData),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'period' => $this->period
        ];

        Mail::to($to)
            ->cc($cc)
            ->bcc('bryan.burchfield@chasedatacorp.com')
            ->send(new InternalSpamCheckMail($message));
    }

    private function swapNumbers()
    {
        $client = new Client();

        // read results from db
        foreach (InternalPhoneFlag::where('run_date', $this->run_date)
            ->whereIn('dialer_numb', [7, 24, 26])   // Supported servers by API
            ->whereNull('replaced_by')
            ->orderBy('dialer_numb')
            ->orderBy('phone')
            ->get() as $rec) {

            list($rec->replaced_by, $rec->swap_error) = $this->swapNumber($client, $rec->phone, $rec->dialer_numb, $rec->group_id);
            $rec->save();
        }
    }

    private function swapNumber(Client $client, $phone, $dialer_numb, $group_id)
    {
        // try to replace with same NPA
        list($replaced_by, $swap_error) = $this->swapNumberNpa($client, $phone, $dialer_numb, $group_id);

        if (empty($replaced_by)) {

            // Find area code record
            $npa = substr($this->formatPhone($phone, true), 0, 3);
            $areaCode = AreaCode::find($npa);

            if ($areaCode) {
                // get list of nearby same state npas
                $alternates = $areaCode->alternateNpas();

                // loop through till swap succeeds or errors
                foreach ($alternates as $alternate) {
                    list($replaced_by, $swap_error) = $this->swapNumberNpa($client, $phone, $dialer_numb, $group_id, $alternate->npa);
                    if (!empty($replaced_by)) {
                        break;
                    }
                }
            }
        }

        return [$replaced_by, $swap_error];
    }

    private function swapNumberNpa(Client $client, $phone, $dialer_numb, $group_id, $npa = null)
    {
        echo "Swapping $phone $npa\n";

        $error = null;
        $replaced_by = null;

        try {
            $response = $client->get(
                'https://billing.chasedatacorp.com/DID.aspx',
                [
                    'query' => [
                        'Token' => '3DCE9183-CA9C-4D1A-8B23-171DFA8C4D4B',
                        'Server' => 'dialer-' . sprintf('%02d', $dialer_numb, 2),
                        'Number' => $this->formatPhone($phone, 1),
                        'GroupId' => $group_id,
                        'Action' => 'swap',
                        'NPA' => $npa
                    ]
                ]
            );
        } catch (Exception $e) {
            $error = 'Swap API failed: ' . $e->getMessage();
        }

        if (empty($error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (isset($body->NewDID)) {
                    if (!empty($body->NewDID)) {
                        $replaced_by = $this->formatPhone($body->NewDID);
                    } else {
                        $error = 'No repalcement available';
                    }
                }
                if (!empty($body->Error)) {
                    $error = $body->Error;
                }
            } catch (Exception $e) {
                $error = 'Could not swap number: ' . $e->getMessage();
            }
        }

        // truncate error just in case
        if (!empty($error)) {
            $error = substr($error, 0, 190);
        }

        return [$replaced_by, $error];
    }
}
