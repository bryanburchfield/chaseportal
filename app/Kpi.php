<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Kpi extends Model
{
    public $timestamps = false;

    public function kpiGroups()
    {
        return $this->hasMany('App\KpiGroup');
    }

    public function kpiRecipients()
    {
        return $this->hasMany('App\KpiRecipient');
    }

    public static function getKpis()
    {
        $groupId = Auth::user()->group_id;

        $kpis = self::select('kpis.*', 'KG.active', 'KG.interval')
            ->leftJoin('kpi_groups as KG', 'kpis.id', '=', 'KG.kpi_id')
            ->where('KG.group_id', $groupId)
            ->get();

        foreach ($kpis as &$k) {
            $k->{'recipients'} =
                \App\KpiRecipient::select('kpi_recipients.id', 'kpi_recipients.recipient_id', 'R.name', 'R.email', 'R.phone')
                ->where('kpi_recipients.kpi_id', $k->id)
                ->join('recipients as R', 'kpi_recipients.recipient_id', '=', 'R.id')
                ->orderby('R.name')
                ->get();
        }

        return $kpis;
    }

    public function getRecipients($group_id)
    {
        return
            \App\KpiRecipient::select('kpi_recipients.id', 'kpi_recipients.recipient_id', 'R.name', 'R.email', 'R.phone')
            ->where('kpi_recipients.kpi_id', $this->id)
            ->join('recipients as R', function ($join) use ($group_id) {
                $join->on('R.id', '=', 'kpi_recipients.recipient_id')
                    ->on('R.group_id', '=', DB::raw($group_id));
            })
            ->orderby('R.name')
            ->get();
    }
}
