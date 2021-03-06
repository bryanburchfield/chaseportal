<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Kpi extends Model
{
    protected $fillable = [
        'name',
        'query',
    ];

    public function kpiGroups()
    {
        return $this->hasMany('App\Models\KpiGroup');
    }

    public function kpiRecipients()
    {
        return $this->hasMany('App\Models\KpiRecipient');
    }

    public static function getKpis()
    {
        $kpis = self::select('kpis.id', 'kpis.name', 'KG.active', 'KG.interval')
            ->leftJoin('kpi_groups as KG', function ($join) {
                $join->on('kpis.id', '=', 'KG.kpi_id')
                    ->where('KG.group_id', '=', Auth::user()->group_id);
            })
            ->get();

        $kpis->transform(function ($item, $key) {
            $item['trans_name'] = trans('kpi.' . $item['name']);
            return $item;
        });
        $kpis = $kpis->sortBy('trans_name');

        foreach ($kpis as &$k) {
            $k->{'recipients'} =
                KpiRecipient::select('kpi_recipients.id', 'kpi_recipients.recipient_id', 'R.name', 'R.email', 'R.phone', 'R.user_id')
                ->join('recipients as R', function ($join) {
                    $join->on('R.id', '=', 'kpi_recipients.recipient_id')
                        ->on('R.group_id', '=', DB::raw(Auth::user()->group_id));
                })
                ->where('kpi_recipients.kpi_id', $k->id)
                ->orderby('R.name')
                ->get();
        }

        return $kpis;
    }

    public function getRecipients($group_id)
    {
        return
            KpiRecipient::select('kpi_recipients.id', 'kpi_recipients.recipient_id', 'R.name', 'R.email', 'R.phone')
            ->where('kpi_recipients.kpi_id', $this->id)
            ->join('recipients as R', function ($join) use ($group_id) {
                $join->on('R.id', '=', 'kpi_recipients.recipient_id')
                    ->on('R.group_id', '=', DB::raw($group_id));
            })
            ->orderby('R.name')
            ->get();
    }

    public function sql($db_list, $group_id, $fromdate, $todate)
    {
        list($inner_sql, $bind) = $this->parseInnerSql($db_list, $group_id, $fromdate, $todate);

        $sql = str_replace('{{inner_sql}}', $inner_sql, $this->outer_sql);

        return [$sql, $bind];
    }

    private function parseInnerSql($db_list, $group_id, $fromdate, $todate)
    {
        $sql = $this->inner_sql;

        $bind = [];
        $final = '';

        $union = '';
        foreach ($db_list as $i => $db) {
            $snippet = str_replace('{{db}}', $db, $sql);

            if (strpos($sql, '{{:fromdate}}') !== false) {
                $bind['fromdate' . $i] = $fromdate;
                $snippet = str_replace('{{:fromdate}}', ':fromdate' . $i, $snippet);
            }
            if (strpos($sql, '{:todate}') !== false) {
                $bind['todate' . $i] = $todate;
                $snippet = str_replace('{{:todate}}', ':todate' . $i, $snippet);
            }
            if (strpos($sql, '{:group_id}') !== false) {
                $bind['group_id' . $i] = $group_id;
                $snippet = str_replace('{{:group_id}}', ':group_id' . $i, $snippet);
            }
            if (strpos($sql, '{:group_id1}') !== false) {
                $bind['group_id1' . $i] = $group_id;
                $snippet = str_replace('{{:group_id1}}', ':group_id1' . $i, $snippet);
            }
            if (strpos($sql, '{:group_id2}') !== false) {
                $bind['group_id2' . $i] = $group_id;
                $snippet = str_replace('{{:group_id2}}', ':group_id2' . $i, $snippet);
            }

            $final .= " $union $snippet";
            $union = $this->union_all ? 'UNION ALL' : 'UNION';
        }

        return [$final, $bind];
    }
}
