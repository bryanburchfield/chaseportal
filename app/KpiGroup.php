<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Schedulable;

class KpiGroup extends Model
{
    use Schedulable;

    public function kpi()
    {
        return $this->belongsTo('App\Kpi');
    }

    public function sql()
    {
        $sql = $this->kpi->query;
        return $sql;
    }
}

// New db fields:
//   outer_sql
//   inner_sql
//   union_all

// outer_sql =
// SELECT
// 'Campaign' = ISNULL(Campaign, 'Total'),
// 'Peak Agents' = COUNT(DISTINCT Rep)
// FROM (
// {inner_sql}
// ) tmp
// GROUP BY ROLLUP(Campaign);

// inner_sql =
// SELECT Campaign, Rep
// FROM [{db}].[dbo].[AgentActivity]
// WHERE GroupId = {:group_id}
// AND Date >= {:fromdate}
// AND Date < {:todate}
// AND Campaign != ''

// union_all = 1|0
