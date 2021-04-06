<?php

namespace App\Services;

use App\Includes\ChaseDataDidApi;
use App\Mail\InternalSpamCheckMail;
use App\Models\Dialer;
use App\Models\InternalPhoneCount;
use App\Models\InternalPhoneFlag;
use App\Traits\PhoneTraits;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InternalSpamCheckService
{
    use SqlServerTraits;
    use TimeTraits;
    use PhoneTraits;

    private $didSwapService;
    private $chaseDataDidApi;

    private $period;
    private $run_date;
    private $startdate;
    private $did_count;

    // Groups to exclude
    private $ignoreGroups =
    [
        224195,
        224802,
        236163,
        2256969,
    ];

    public static function execute($period)
    {
        $spam_check_service = get_called_class();
        $spam_check_service = new $spam_check_service;
        $spam_check_service->runPeriod($period);
    }

    public static function interim()
    {
        $spam_check_service = get_called_class();
        $spam_check_service = new $spam_check_service;
        $spam_check_service->runInterim();
    }

    private function initialize()
    {
        $this->run_date = now();
        $this->startdate = today();

        $this->didSwapService = new DidSwapService();
        $this->chaseDataDidApi = new ChaseDataDidApi();
    }

    public function runPeriod($period)
    {
        $this->period = $period;
        Log::info('Starting Internal Spam Check - ' . $period);

        $this->initialize();

        echo "Counting CallerIds\n";
        Log::info('Counting CallerIds');
        $this->countCallerIds();

        echo "Pulling report\n";
        Log::info('Pulling report');
        $this->saveReport();

        echo "Swap Numbers\n";
        Log::info('Swapping numbers');
        $this->swapNumbers();

        echo "Creating report\n";
        Log::info('Creating report');
        $this->createReport();

        echo "Clear test campaigns\n";
        Log::info('Clear test campaigns');
        $this->clearTestCampaigns();

        echo "Load test campaigns\n";
        Log::info('Load test campaigns');
        $this->loadTestCampaigns();

        echo "Finished\n";
        Log::info('Finished');
    }

    public function runInterim()
    {
        $this->period = 'interim';
        Log::info('Starting Internal Spam Check - interim');

        $this->initialize();

        echo "Pulling report\n";
        Log::info('Pulling report');
        $this->saveInterimReport();

        echo "Swap Numbers\n";
        Log::info('Swapping numbers');
        $this->swapNumbers();

        echo "Finished\n";
        Log::info('Finished');
    }

    private function countCallerIds()
    {
        $results = $this->didQuery();

        $this->did_count = count($results) ? $results[0]['Cnt'] : 0;

        try {
            InternalPhoneCount::create([
                'run_date' => $this->run_date,
                'did_count' => $this->did_count,
            ]);
        } catch (Exception $e) {
            Log::error('Error creating InternalPhoneCount: ' . $this->did_count);
            Log::critical($e->getMessage());
        }
    }

    private function didQuery()
    {
        $bind = [
            'startdate' => $this->getStartDate(),
            'enddate' => $this->run_date->toDateTimeString(),
        ];

        $sql = "
        DECLARE @startdate AS DATETIME
        DECLARE @enddate   AS DATETIME

        SET @startdate = :startdate
        SET @enddate = :enddate

        SELECT COUNT(DISTINCT CallerId) As Cnt
        FROM [PowerV2_Reporting_Dialer-07].[dbo].[DialingResults]
        WHERE GroupId = 2256969
        AND CallDate >= @startdate
        AND CallDate < @enddate
        AND Campaign = 'TOP_1500_USED_DIDS'";

        return $this->runSql($sql, $bind);
    }

    private function getStartDate()
    {
        if (strtolower($this->period) == 'morning') {
            $startdate = Carbon::parse('yesterday 5pm', 'America/New_York')->tz('UTC')->toDateTimeString();
        } else {
            $startdate = Carbon::parse('today 8am', 'America/New_York')->tz('UTC')->toDateTimeString();
        }

        return $startdate;
    }

    private function saveReport()
    {
        // Save results to database
        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhoneElevenDigits($rec['Phone']);

            try {
                InternalPhoneFlag::create([
                    'run_date' => $this->run_date,
                    'period' => $this->period,
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

    private function saveInterimReport()
    {
        // Save results to database
        foreach ($this->runInterimQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhoneElevenDigits($rec['Phone']);

            try {
                InternalPhoneFlag::create([
                    'run_date' => $this->run_date,
                    'period' => $this->period,
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

        $last_full_run_date = $this->getLastFullRunDate();

        foreach (InternalPhoneFlag::where('run_date', '>', $last_full_run_date)
            ->orderBy('group_id')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['phone'] = $this->formatPhoneTenDigits($rec['phone']);
            $rec['replaced_by'] = $this->formatPhoneTenDigits($rec['replaced_by']);

            $all_results[] = $rec;
        }

        $mainCsv = $this->makeCsv($all_results);

        $this->emailReport($mainCsv);
    }

    private function getLastFullRunDate()
    {
        $most_recent_date = InternalPhoneFlag::where('run_date', '<', $this->run_date)
            ->whereIn('period', ['morning', 'afternoon', 'evening'])
            ->max('run_date');

        // if not found, return really old date
        return $most_recent_date ?? '2000-01-01 00:00:00';
    }

    private function runQuery()
    {
        $bind = [
            'startdate' => $this->getStartDate(),
            'enddate' => $this->run_date->toDateTimeString(),
            'ratio' => 2.15,
            'lookback' => 30,
        ];

        $sql = "SET NOCOUNT ON;

        DECLARE @startdate AS DATETIME
        DECLARE @enddate   AS DATETIME
        DECLARE @lookBack  AS INT
        DECLARE @ratio     AS FLOAT
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
        ) tmp
        ";

        $sql .= $this->buildPeriodSql();
        $sql .= $this->buildActiveDidsSql();

        return $this->runSql($sql, $bind);
    }

    private function runInterimQuery()
    {
        // Make list of groups to ignore
        $ignoreGroups = implode(',', $this->ignoreGroups);

        $bind = [
            'startdate' => $this->getLastFullRunDate(),
            'enddate' => $this->run_date->toDateTimeString(),
            'lookback' => 30,
            'minhangups' => 4,
        ];

        $sql = "SET NOCOUNT ON;

        DECLARE @startdate AS DATETIME
        DECLARE @enddate   AS DATETIME
        DECLARE @lookBack  AS INT
        DECLARE @minHangups  AS INT
        DECLARE @systemCodes table (CallStatus varchar(50))

        SET @startdate = :startdate
        SET @enddate = :enddate
        SET @lookBack = :lookback
        SET @minHangups = :minhangups";


        $sql .= "        
        SELECT * INTO #sipdids FROM (";

        $union = '';

        // dialer 26 doesn't have sip_bye column
        foreach (Dialer::where('dialer_numb', '!=', 26)->get() as $dialer) {

            $sql .= " $union
                SELECT DR.id, CallerId, CallDate
                FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O on O.Phone = DR.CallerId AND O.GroupId = DR.GroupId
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I on I.GroupId = O.GroupId AND I.InboundSource = O.Phone
                WHERE O.Active = 1
                AND (I.Description LIKE '%caller%id%call%back%' OR I.Description LIKE '%nationwide%')
                AND CallDate >= @startdate
                AND CallDate < @enddate
                AND DR.GroupId NOT IN ($ignoreGroups)
                AND CallType IN (0,2)
                AND sip_bye = 1";

            $union = 'UNION';
        }

        $sql .= "
        ) tmp
        SELECT * INTO #bad FROM (
            SELECT DISTINCT S1.CallerId, 'LiveHangups' as Subcampaigns
            FROM #sipdids S1
            WHERE (SELECT count(*)
                FROM #sipdids S2
                WHERE S2.CallerId = S1.CallerId
                AND S2.id != S1.id
                AND S2.CallDate BETWEEN S1.CallDate AND DATEADD(ss,60,S1.CallDate)
                ) >= @minHangups
        ) tmp";

        $sql .= $this->buildActiveDidsSql();

        return $this->runSql($sql, $bind);
    }

    private function buildPeriodSql()
    {
        $sql = '';

        if (strtolower($this->period) == 'morning') {

            $sql = "
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
        }

        if (strtolower($this->period) == 'afternoon') {
            $sql = "
        SELECT CallerId, string_agg(Subcampaign, ',') AS Subcampaigns INTO #bad FROM (
            SELECT CallerId, Subcampaign FROM #busy
            UNION
            SELECT CallerId, Subcampaign FROM #remotehangups
        ) tmp
        GROUP BY CallerId
                ";
        }

        if (strtolower($this->period) == 'evening') {

            $sql = "
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

        return $sql;
    }

    private function buildActiveDidsSql()
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

        $sql = "
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

        return $sql;
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
            'brandon.b@chasedatacorp.com',
            'dylan.farley@chasedatacorp.com',
            'jonathan.gryczka@chasedatacorp.com'
        ];

        // email report
        $message = [
            'subject' => 'Internal Spam Check Report - ' . $this->period,
            'mainCsv' => base64_encode($mainData),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'period' => $this->period,
            'did_count' => $this->did_count
        ];

        Mail::to($to)
            ->cc($cc)
            ->bcc('bryan.burchfield@chasedatacorp.com')
            ->send(new InternalSpamCheckMail($message));
    }

    private function swapNumbers()
    {
        // read results from db
        foreach (InternalPhoneFlag::where('run_date', $this->run_date)
            ->whereIn('dialer_numb', [7, 9, 24, 26])   // Supported servers by API
            ->whereNull('replaced_by')
            ->orderBy('dialer_numb')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec->swap_error = null;;
            $rec->replaced_by = $this->didSwapService->swapNumber($rec->phone, $rec->dialer_numb, $rec->group_id);

            if ($rec->replaced_by === false) {
                $rec->replaced_by = null;
                $rec->swap_error = $this->didSwapService->error;
            }

            $rec->save();
        }
    }

    private function clearTestCampaigns()
    {
        $this->clearCampaign('TOP_1500_USED_DIDS');
        $this->clearCampaign('VERIZON');
    }

    private function clearCampaign($campaign)
    {
        $ids = resultsToList($this->getCallerIds($campaign));

        foreach ($ids as $id) {
            if (!$this->chaseDataDidApi->deleteCallerId(7, $id)) {
                Log::error($this->chaseDataDidApi->error);
                echo $this->chaseDataDidApi->error . "\n";
            }
        }
    }

    private function getCallerIds($campaign)
    {
        $bind['campaign'] = $campaign;

        $sql = "
        SELECT id
        FROM [PowerV2_Reporting_Dialer-07].[dbo].[OwnedNumbers]
        WHERE GroupId = 2256969
        AND Campaign = :campaign";

        return $this->runSql($sql, $bind);
    }

    private function loadTestCampaigns()
    {
        $dids = array_keys(resultsToList($this->getTopDids()));

        foreach ($dids as $did) {
            if (!$this->chaseDataDidApi->addCallerId(7, 2256969, $did, 'TOP_1500_USED_DIDS')) {
                Log::error($this->chaseDataDidApi->error);
                echo $this->chaseDataDidApi->error . "\n";
            }
            if (!$this->chaseDataDidApi->addCallerId(7, 2256969, $did, 'VERIZON')) {
                Log::error($this->chaseDataDidApi->error);
                echo $this->chaseDataDidApi->error . "\n";
            }
        }
    }

    private function getTopDids()
    {
        $ignoreGroups = implode(',', $this->ignoreGroups);

        $bind = [
            'enddate' => $this->run_date->toDateTimeString(),
            'mindials' => 250,
        ];

        switch (today()->dayOfWeek) {
            case 0:  // sunday
                $bind['startdate'] = today()->subDays(2)->toDateTimeString();
                break;
            case 1:  // monday
                $bind['startdate'] = today()->subDays(3)->toDateTimeString();
                break;
            default:
                $bind['startdate'] = today()->subDays(1)->toDateTimeString();
        }

        $sql = "SET NOCOUNT ON
        DECLARE @startdate AS DATETIME
        DECLARE @enddate   AS DATETIME
        DECLARE @minDials  INT

        SET @startdate = :startdate
        SET @enddate   = :enddate
        SET @minDials  = :mindials

        SELECT CallerId, SUM(Cnt) AS Cnt
        FROM (";

        $union = '';
        foreach (Dialer::all() as $dialer) {

            $sql .= " $union
            SELECT CallerId, COUNT(*) AS Cnt
            FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR
            INNER JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = DR.GroupId AND I.InboundSource = DR.CallerId
            INNER JOIN [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O ON O.GroupId = DR.GroupId AND O.Phone = DR.CallerId
            WHERE DR.CallDate >= @startdate
            AND DR.CallDate < @enddate
            AND DR.CallType = 0
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            AND (I.Description like '%caller%id%call%back%' or I.Description like '%nationwide%')
            AND O.Active = 1
            AND DR.GroupId NOT IN ($ignoreGroups)
            GROUP BY CallerId
            ";

            $union = 'UNION ALL';
        }

        // Load all of Teldar's regardless
        $sql .= "
        UNION
        SELECT O.Phone as CallerId, 99999 AS Cnt
        FROM [PowerV2_Reporting_Dialer-24].[dbo].[InboundSources] I
        INNER JOIN [PowerV2_Reporting_Dialer-24].[dbo].[OwnedNumbers] O ON O.GroupId = I.GroupId AND O.Phone = I.InboundSource
        WHERE I.GroupId = 1111
        AND O.Active = 1
        AND (I.Description like '%caller%id%call%back%' or I.Description like '%nationwide%')
        ";

        $sql .= ") tmp
        GROUP BY CallerId
        HAVING SUM(Cnt) >= @minDials
        ORDER BY CallerId";

        return $this->runSql($sql, $bind);
    }
}
