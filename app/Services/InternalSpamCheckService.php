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
use Illuminate\Support\Carbon;
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

        echo "Swap Numbers\n";
        Log::info('Swapping numbers');
        $this->swapNumbers();

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
                    'subcampaigns' => $rec['Subcampaigns'],
                    'dials' => $rec['Dials'],
                    'connects' => $rec['Connects'],
                    'connect_pct' => $rec['ConnectPct'],
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
            ->orderBy('group_id')
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

        // Have to hard-code what's considered 'system' for connect calculations
        $system_codes = "
'CR_BAD_NUMBER',
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
'Inbound Transfer',
'Inbound Voicemail',
'PARKED',
'SYS_CALLBACK',
'Skip',
'TRANSFERRED',
'UNFINISHED',
'UNKNOWN'
        ";

        $bind = [
            'enddate' => now()->toDateTimeString(),
            'ratio' => 2.15,
            'lookback' => 30,
        ];

        if (strtolower($this->period) == 'morning') {
            $bind['startdate'] = today()->subDay(1)->toDateTimeString();
        } else {
            $bind['startdate'] = Carbon::parse('today 8am', 'America/New_York')->tz('UTC')->toDateTimeString();
        }

        $sql = "SET NOCOUNT ON;

        DECLARE @startdate AS DATETIME
        DECLARE @enddate   AS DATETIME
        DECLARE @lookBack  AS INT
        DECLARE @ratio     AS FLOAT
        DECLARE @excludeGroups table (GroupId int)
        DECLARE @systemCodes table (CallStatus varchar(50))

        SET @startdate = :startdate
        SET @enddate = :enddate
        SET @ratio = :ratio
        SET @lookBack = :lookback
        
        SELECT * INTO #busy FROM (
            SELECT DISTINCT CallerId, Subcampaign
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
          	AND CallDate >= @startdate
	        AND CallDate < @enddate
            AND Campaign = 'TOP_1500_USED_DIDS'
            AND Subcampaign != 'VERIZON'
            AND CallStatus = 'CR_BUSY'
        ) tmp

        SELECT * INTO #ratio FROM (
            SELECT CallerId,
            SUM(CASE WHEN callstatus = 'CR_CNCT/CON_PAMD' THEN 1 ELSE 0 END) AS Pamd,
            SUM(CASE WHEN sip_bye = 1 THEN 1 ELSE 0 END) AS RemoteHangup
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
            AND CallDate >= @startdate
            AND CallDate < @enddate
            AND Campaign = 'TOP_1500_USED_DIDS'
            AND Subcampaign != 'VERIZON'
            GROUP BY CallerId
        ) tmp

        SELECT * INTO #remotehangupdids FROM (
            SELECT CallerId
            FROM #ratio
            WHERE RemoteHangup > 0
            AND ROUND(CAST(Pamd AS float) / CAST(RemoteHangup AS float), 2) < @ratio
        ) tmp

        SELECT * INTO #remotehangups FROM (
            SELECT DISTINCT DR.CallerId, Subcampaign
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults] DR
            INNER JOIN #remotehangupdids D ON D.CallerId = DR.CallerId
            WHERE GroupId = 2256969
            AND CallDate >= @startdate
	        AND CallDate < @enddate
            AND Campaign = 'TOP_1500_USED_DIDS'
            AND Subcampaign != 'VERIZON'
            AND sip_bye = 1
        ) tmp";

        if (strtolower($this->period) == 'morning') {

            $sql .= "
        SELECT * INTO #verizon FROM (
            SELECT CallerId,
                SUM(CASE WHEN CallStatus = 'CR_CNCT/CON_PAMD' THEN 1 ELSE 0 END) AS Pamd,
                SUM(CASE WHEN CallStatus = 'CR_NOANS' THEN 1 ELSE 0 END) AS Noans,
                SUM(CASE WHEN CallStatus NOT IN ('CR_CNCT/CON_PAMD','CR_NOANS') THEN 1 ELSE 0 END) AS Other
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
            AND CallDate >= @startdate
            AND CallDate < @enddate
            AND Campaign = 'VERIZON'
            AND Subcampaign = 'VERIZON'
            GROUP BY CallerId
        ) tmp

        SELECT * INTO #pamd FROM (
            SELECT CallerId, 'VERIZON' AS Subcampaign
            FROM #verizon
            WHERE (Pamd + Noans + Other) >= 3
            AND Pamd > 1
            AND Pamd >= Noans
        ) tmp

        SELECT * INTO #pamd4 FROM (
            SELECT CallerId, 'VERIZON' AS Subcampaign
            FROM #verizon
            WHERE Pamd >= 4
            AND Pamd < Noans
            AND CallerId in (
                SELECT CallerId FROM #remotehangups
                UNION
                SELECT CallerId FROM #busy
            )
        ) tmp

        SELECT CallerId, string_agg(Subcampaign, ',') AS Subcampaigns INTO #bad FROM (
            SELECT CallerId, Subcampaign FROM #busy
            UNION
            SELECT CallerId, Subcampaign FROM #remotehangups
            UNION
            SELECT CallerId, Subcampaign FROM #pamd
            UNION
            SELECT CallerId, Subcampaign FROM #pamd4
            ) tmp
        GROUP BY CallerId
        ";
        } else {  // not morning

            $sql .= "
        SELECT * INTO #pamd FROM (
            SELECT id, CallerId, CallDate
            FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
            WHERE GroupId = 2256969
            AND CallDate >= @startdate
            AND CallDate < @enddate
            AND Campaign = 'VERIZON'
            AND Subcampaign = 'VERIZON'
            AND CallStatus = 'CR_CNCT/CON_PAMD'
        ) tmp

        SELECT * INTO #verizon FROM (
            SELECT DISTINCT P1.CallerId, 'VERIZON' AS Subcampaign
            FROM #pamd P1
            INNER JOIN #pamd P2 ON P2.CallerId = P1.CallerId AND P1.id != P2.id
            WHERE P2.CallDate BETWEEN P1.CallDate AND DATEADD(ss,60,P1.CallDate)
        ) tmp

        SELECT CallerId, string_agg(Subcampaign, ',') AS Subcampaigns INTO #bad FROM (
            SELECT CallerId, Subcampaign FROM #busy
            UNION
            SELECT CallerId, Subcampaign FROM #remotehangups
            UNION
            SELECT CallerId, Subcampaign FROM #verizon
        ) tmp
        GROUP BY CallerId
            ";
        }

        $sql .= "
        SELECT * INTO #activebad FROM (
            SELECT Dialer, GroupId, GroupName, Phone, Description, Subcampaigns FROM (";

        $union = '';
        foreach (Dialer::all() as $dialer) {

            $sql .= " $union SELECT " . $dialer->dialer_numb . " as Dialer, O.GroupId, GroupName, O.Phone, I.Description, Subcampaigns
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

        ALTER TABLE #activebad ADD
            Dials INT,
            Connects INT,
            ConnectPct DECIMAL(5,2)";

        foreach (Dialer::all() as $dialer) {

            $sql .= "
                UPDATE #activebad
                SET Dials = a.Dials
                FROM (
                    SELECT DR.CallerId, DR.GroupId, COUNT(*) as Dials
                    FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR 
                    INNER JOIN #activebad AB ON DR.GroupId = AB.GroupId AND DR.CallerId = AB.Phone AND AB.Dialer = " . $dialer->dialer_numb . "
                    WHERE DR.CallDate >= DATEADD(day, -@lookBack, @enddate)
                    AND DR.CallDate < @enddate
                    AND DR.CallerId != ''
                    AND DR.CallType IN (0,2)
                    AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
                    AND DR.GroupId NOT IN ($ignoreGroups)
                    GROUP BY DR.CallerId, DR.GroupId
                ) a
                WHERE dialer = " . $dialer->dialer_numb . "
                AND #activebad.GroupId = a.GroupId
                AND #activebad.phone = a.CallerId

                UPDATE #activebad
                SET Connects = a.Connects
                FROM (
                    SELECT DR.CallerId, DR.GroupId, COUNT(*) as Connects
                    FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR 
                    INNER JOIN #activebad AB ON DR.GroupId = AB.GroupId AND DR.CallerId = AB.Phone AND AB.Dialer = " . $dialer->dialer_numb . "
                    WHERE DR.CallDate >= DATEADD(day, -@lookBack, @enddate)
                    AND DR.CallDate < @enddate
                    AND DR.CallerId != ''
                    AND DR.CallType IN (0,2)
                    AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
                    AND DR.CallStatus NOT IN ($system_codes)
                    AND DR.GroupId NOT IN ($ignoreGroups)
                    GROUP BY DR.CallerId, DR.GroupId
                ) a
                WHERE dialer = " . $dialer->dialer_numb . "
                AND #activebad.GroupId = a.GroupId
                AND #activebad.phone = a.CallerId
                ";
        }

        $sql .= "
        UPDATE #activebad
        SET ConnectPct = (CAST(Connects AS NUMERIC(18,2)) / CAST(Dials AS NUMERIC(18,2))) * 100
        WHERE Dials > 0

        SELECT Dialer, GroupId, GroupName, Phone, Description, Subcampaigns, Dials, Connects, ConnectPct
        FROM #activebad
        ORDER BY Dialer, GroupId, Phone";

        return $this->runSql($sql, $bind);
    }

    private function makeCsv($results)
    {
        $headers = [
            'Dialer',
            'GroupID',
            'GroupName',
            'CallerID',
            'RingGroup',
            'FlaggedByCarrier',
            'Dials30Days',
            'ConnectPct',
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
                $rec->group_id,
                $rec->group_name,
                $rec->phone,
                $rec->ring_group,
                $rec->subcampaigns,
                $rec->dials,
                $rec->connect_pct,
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
            ->whereIn('dialer_numb', [7, 9, 24, 26])   // Supported servers by API
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
