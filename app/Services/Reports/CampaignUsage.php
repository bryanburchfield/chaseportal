<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \App\Traits\ReportTraits;

class CampaignUsage
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Campaign Usage Report';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['campaign'] = '';
        $this->params['subcampaign'] = '';
        $this->params['columns'] = [
            'Stat' => 'Status',
            'Attempt' => 'Attempt',
            'Tries' => 'Count',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaign' => $this->getAllCampaigns(),
            'subcampaign' => $this->getAllSubcampaigns(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        $bind = [];

        $sql = "SET NOCOUNT ON;

        CREATE TABLE #CampaignUsage(
            Attempt int,
            Callable bit,
            Tries int default 0
        );

        INSERT INTO #CampaignUsage(Attempt,Callable) VALUES
        (0,0),(0,1),(1,0),(1,1),(2,0),(2,1),(3,0),(3,1),(4,0),(4,1),
        (5,0),(5,1),(6,0),(6,1),(7,0),(7,1),(8,0),(8,1),(9,0),(9,1),
        (10,0),(10,1),(11,0),(11,1),(12,0),(12,1),(13,0),(13,1),(14,0),(14,1),
        (15,0),(15,1),(16,0),(16,1),(17,0),(17,1),(18,0),(18,1),(19,0),(19,1),
        (20,0),(20,1);";

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['campaign' . $i] = $this->params['campaign'];

            $sql .= " UPDATE #CampaignUsage
            SET #CampaignUsage.Tries += a.Tries
            FROM (SELECT l2.Attempt, sum(l2.Tries) as Tries
                    FROM (SELECT
                        case when l.Attempt > 20 then 20 else l.Attempt end as Attempt,
                        COUNT(Attempt) as Tries
                        FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                        WHERE l.GroupId = :group_id$i
                        AND l.Campaign = :campaign$i";

            if (!empty($this->params['subcampaign'])) {
                $bind['subcampaign' . $i] =  $this->params['subcampaign'];
                $sql .= " AND l.Subcampaign = :subcampaign$i";
            }

            $sql .= "
                    AND l.WasDialed = 1
                    GROUP BY Attempt) l2
                    GROUP BY l2.Attempt) a
            WHERE #CampaignUsage.Attempt = a.Attempt
            AND #CampaignUsage.Callable = 0;";
        }

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['campaign1' . $i] = $this->params['campaign'];

            $sql .= " UPDATE #CampaignUsage
            SET #CampaignUsage.Tries += a.Tries
            FROM (SELECT l2.Attempt, sum(l2.Tries) as Tries
                    FROM (SELECT
                    case when l.Attempt > 20 then 20 else l.Attempt end as Attempt,
                    COUNT(Attempt) as Tries
                        FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                        WHERE l.GroupId = :group_id1$i
                        AND l.Campaign = :campaign1$i";

            if (!empty($this->params['subcampaign'])) {
                $bind['subcampaign1' . $i] =  $this->params['subcampaign'];
                $sql .= " AND l.Subcampaign = :subcampaign1$i";
            }

            $sql .= "
                    AND l.WasDialed = 0
                    GROUP BY Attempt) l2
                    GROUP BY l2.Attempt) a
            WHERE #CampaignUsage.Attempt = a.Attempt
            AND #CampaignUsage.Callable = 1;";
        }

        $sql .= "SELECT CallStatus, COUNT(*) as Cnt FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id2' . $i] = Auth::user()->group_id;
            $bind['campaign2' . $i] = $this->params['campaign'];

            $sql .= " $union SELECT CallStatus
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            WHERE l.GroupId = :group_id2$i
            AND l.Campaign = :campaign2$i";

            if (!empty($this->params['subcampaign'])) {
                $bind['subcampaign2' . $i] =  $this->params['subcampaign'];
                $sql .= " AND l.Subcampaign = :subcampaign2$i";
            }

            $sql .= "
            AND l.WasDialed = 1";

            $union = "UNION ALL";
        }

        $sql .= ") tmp
        GROUP BY CallStatus
        ORDER BY Cnt desc;

        SELECT Subcampaign, COUNT(*) as Cnt FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id3' . $i] = Auth::user()->group_id;
            $bind['campaign3' . $i] = $this->params['campaign'];

            $sql .= " $union SELECT IsNull(Subcampaign, '') as Subcampaign
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            WHERE l.GroupId = :group_id3$i
            AND l.Campaign = :campaign3$i";

            if (!empty($this->params['subcampaign'])) {
                $bind['subcampaign3' . $i] =  $this->params['subcampaign'];
                $sql .= " AND l.Subcampaign = :subcampaign3$i";
            }

            $sql .= "
            AND l.WasDialed = 0";

            $union = "UNION ALL";
        }

        $sql .= ") tmp
        GROUP BY Subcampaign
        ORDER BY Cnt desc;

        SELECT
        case
          when Callable = 0 then 'NonCallable'
          when Callable = 1 then 'Callable'
        end [Stat],
        Attempt, Tries
        FROM #CampaignUsage
        ORDER BY Callable DESC, Attempt";

        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $pdo = DB::connection('sqlsrv')->getPdo();
        // $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $stmt = $pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        try {
            $this->extras['callstats'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $this->extras['subcampaigns'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->extras['callstats'] = [];
            $this->extras['subcampaigns'] = [];
            $results = [];
        }

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
            $results = [];
        } else {
            $this->params['totrows'] = 40;
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        $this->createExtras($results);

        return $results;
    }

    private function createExtras($results)
    {
        $callable = [];
        $noncallable = [];

        foreach ($results as $rec) {
            if ($rec['Stat'] == 'Callable') {
                $callable[$rec['Attempt']] = $rec['Tries'];
            } else {
                $noncallable[$rec['Attempt']] = $rec['Tries'];
            }
        }

        ksort($callable);
        ksort($noncallable);

        $this->extras['callable'] = $callable;
        $this->extras['noncallable'] = $noncallable;
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        if (!empty($request->campaign)) {
            $this->params['campaign'] = $request->campaign;
        } else {
            $this->errors->add('campaign.required', "Campaign required");
        }

        if (!empty($request->subcampaign)) {
            $this->params['subcampaign'] = $request->subcampaign;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
