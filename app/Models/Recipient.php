<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    public function kpiRecipients()
    {
        return $this->hasMany('App\Models\KpiRecipient');
    }

    public function kpiList()
    {
        $list = [];
        foreach (Kpi::orderBy('name')->get() as $kpi) {
            $list[] = [
                'id' => $kpi->id,
                'name' => trans('kpi.' . $kpi->name),
                'description' => trans('kpi.desc_' . $kpi->name),
                'selected' => $this->kpiRecipients()->where('kpi_id', $kpi->id)->count(),
            ];
        }

        return $list;
    }
}