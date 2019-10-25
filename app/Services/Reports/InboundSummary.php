<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class InboundSummary
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Inbound Summary Report';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['campaigns'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Campaign' => 'Campaign',
            'Source' => 'Inbound Source',
            'Total' => 'Total',
            'Duration' => 'Minutes',
            'HandledByRep' => 'Handled By Rep',
            'HandledByIVR' => 'Handled By IVR',
            'VoiceMail' => 'Voice Mail',
            'Abandoned' => 'Abandoned Calls',
            'AvgTalkTime' => 'Avg Talk Time',
            'AvgHoldTime' => 'Avg Hold Time',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        $bind['campaigns'] = $campaigns;

        $sql = "SET NOCOUNT ON;

            CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');

            CREATE TABLE #CampaignSummary(
                Source varchar(50),
                Campaign varchar(50),
                Total int DEFAULT 0,
                HandledByRep int DEFAULT 0,
                HandledByIVR int DEFAULT 0,
                AvgHoldTime numeric(18,2) DEFAULT 0,
                AvgTalkTime numeric(18,2) DEFAULT 0,
                Abandoned int DEFAULT 0,
                VoiceMail int DEFAULT 0,
                Duration  int DEFAULT 0
            );

            CREATE TABLE #Avgs(
                GAvgHoldTime numeric(18,2) DEFAULT 0,
                GAvgTalkTime numeric(18,2) DEFAULT 0
            );
            insert into #Avgs values (0,0);

            CREATE UNIQUE INDEX IX_CampaignDate ON #CampaignSummary (Source, Campaign);

            SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT
                    IsNull(dr.CallerId, '') as Source,
                    dr.Campaign as Campaign,
                    dr.CallStatus as CallStatus,
                    dr.Rep as Rep,
                    dr.HoldTime as HoldTime,
                    dr.Duration as Duration
                FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
                INNER JOIN #SelectedCampaign c on c.CampaignName = dr.Campaign
                WHERE dr.GroupId = :group_id$i
                AND dr.CallType = 1
                AND dr.Date >= :startdate$i
                AND dr.Date < :enddate$i";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

            INSERT #CampaignSummary(Source, Campaign)
            SELECT DISTINCT dr.Source, dr.Campaign
            FROM #DialingResultsStats dr

            UPDATE #CampaignSummary
            SET Total = b.Total
            FROM (SELECT a.Source, a.Campaign, SUM(a.Total) as Total
                  FROM (SELECT dr.Source, dr.Campaign, COUNT(*) as Total
                        FROM #DialingResultsStats dr
                        WHERE dr.CallStatus = 'Inbound'
                        GROUP BY dr.Source, dr.Campaign) a
                  GROUP BY a.Source, a.Campaign) b
            WHERE #CampaignSummary.Source = b.Source
            AND #CampaignSummary.Campaign = b.Campaign

            UPDATE #CampaignSummary
            SET Duration = b.Duration
            FROM (SELECT a.Source, a.Campaign, SUM(a.Duration) as Duration
                  FROM (SELECT dr.Source, dr.Campaign, SUM(dr.Duration) as Duration
                        FROM #DialingResultsStats dr
                        WHERE dr.CallStatus = 'Inbound'
                        GROUP BY  dr.Source, dr.Campaign) a
                  GROUP BY a.Source, a.Campaign) b
            WHERE #CampaignSummary.Source = b.Source
            AND #CampaignSummary.Campaign = b.Campaign

            UPDATE #CampaignSummary
            SET HandledByRep = a.Total
            FROM (SELECT dr.Source, dr.Campaign, COUNT(*) as Total
                  FROM #DialingResultsStats dr
                  WHERE dr.Rep <> '' AND dr.CallStatus NOT IN ('Inbound', 'Inbound Voicemail')
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #CampaignSummary
            SET HandledByIVR = a.Total
            FROM (SELECT dr.Source, dr.Campaign, COUNT(*) as Total
                  FROM #DialingResultsStats dr
                  WHERE dr.Rep = ''
                  AND dr.CallStatus NOT IN ('Inbound', 'Inbound Voicemail', 'CR_HANGUP', 'Busy Termination')
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #CampaignSummary
            SET AvgHoldTime = a.HoldTime
            FROM (SELECT dr.Source, dr.Campaign, AVG(HoldTime) as HoldTIme
                  FROM #DialingResultsStats dr
                  WHERE dr.HoldTime > 0
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #CampaignSummary
            SET AvgTalkTime = a.TalkTime
            FROM (SELECT dr.Source, dr.Campaign, AVG(Duration) as TalkTime
                  FROM #DialingResultsStats dr
                  WHERE dr.Rep <> ''
                  AND dr.Duration > 0
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #CampaignSummary
            SET VoiceMail = a.VoiceMail
            FROM (SELECT dr.Source, dr.Campaign, COUNT(*) as VoiceMail
                  FROM #DialingResultsStats dr
                  WHERE dr.CallStatus in ('Inbound Voicemail')
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #CampaignSummary
            SET Abandoned = a.Abandoned
            FROM (SELECT dr.Source, dr.Campaign, COUNT(*) as Abandoned
                  FROM #DialingResultsStats dr
                  WHERE dr.CallStatus in ('CR_HANGUP')
                  GROUP BY dr.Source, dr.Campaign) a
            WHERE #CampaignSummary.Source = a.Source
            AND #CampaignSummary.Campaign = a.Campaign

            UPDATE #Avgs
            SET GAvgHoldTime =
            (SELECT AVG(HoldTime)
             FROM #DialingResultsStats dr
             WHERE dr.HoldTime > 0)

            UPDATE #Avgs
            SET GAvgTalkTime =
             (SELECT AVG(Duration)
              FROM #DialingResultsStats dr
              WHERE dr.Rep <> ''
              AND dr.Duration > 0)

            SELECT
             cs.Campaign,
             IsNull(s.Description + ' [' + cs.Source + ']', cs.Source) as Source,
             cs.Total,
             cs.Duration,
             cs.HandledByRep,
             cs.HandledByIVR,
             cs.VoiceMail,
             cs.Abandoned,
             cs.AvgTalkTime,
             cs.AvgHoldTime,
             a.GAvgTalkTime,
             a.GAvgHoldTime
            FROM #Avgs a, #CampaignSummary cs
            LEFT JOIN InboundSources s on s.InboundSource = cs.Source";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Campaign, Source';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
            $results = $this->processResults($results);
        }

        return $this->getPage($results);
    }

    private function processResults($results)
    {
        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$k] = '';
        }

        $total['Campaign'] = 'Total:';
        $total['Total'] = 0;
        $total['Duration'] = 0;
        $total['HandledByRep'] = 0;
        $total['HandledByIVR'] = 0;
        $total['VoiceMail'] = 0;
        $total['Abandoned'] = 0;

        foreach ($results as &$rec) {
            $total['Total'] += $rec['Total'];
            $total['Duration'] += $rec['Duration'];
            $total['HandledByRep'] += $rec['HandledByRep'];
            $total['HandledByIVR'] += $rec['HandledByIVR'];
            $total['VoiceMail'] += $rec['VoiceMail'];
            $total['Abandoned'] += $rec['Abandoned'];

            // total avgs are one each row
            $total['AvgTalkTime'] = $rec['GAvgTalkTime'];
            $total['AvgHoldTime'] = $rec['GAvgHoldTime'];

            // remove gtot cols
            unset($rec['GAvgTalkTime']);
            unset($rec['GAvgHoldTime']);

            $rec['Duration'] = $this->secondsToHms($rec['Duration']);
            $rec['AvgTalkTime'] = $this->secondsToHms($rec['AvgTalkTime']);
            $rec['AvgHoldTime'] = $this->secondsToHms($rec['AvgHoldTime']);
        }

        // format totals
        $total['Duration'] = $this->secondsToHms($total['Duration']);
        $total['AvgTalkTime'] = $this->secondsToHms($total['AvgTalkTime']);
        $total['AvgHoldTime'] = $this->secondsToHms($total['AvgHoldTime']);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (empty($request->campaigns)) {
            $this->errors->add('campaign.required', "Campaign required");
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->calltype)) {
            $this->params['calltype'] = $request->calltype;
        }

        if (!empty($request->phone)) {
            $this->params['phone'] = $request->phone;
        }

        if (!empty($request->callerids)) {
            $this->params['callerids'] = $request->callerids;
        }

        if (!empty($request->callstatuses)) {
            $this->params['callstatuses'] = $request->callstatuses;
        }

        if (empty($request->durationfrom)) {
            $this->params['durationfrom'] = '';
            $from = 0;
        } else {
            $this->params['durationfrom'] = $request->durationfrom;
            $from = $request->durationfrom;
        }

        if (empty($request->durationto)) {
            $this->params['durationto'] = '';
            $to = 0;
        } else {
            $this->params['durationto'] = $request->durationto;
            $to = $request->durationto;
        }

        if ($from > $to) {
            $this->errors->add('duration', "Invalid Duration values");
        }

        if (!empty($request->showonlyterm)) {
            $this->params['showonlyterm'] = $request->showonlyterm;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
