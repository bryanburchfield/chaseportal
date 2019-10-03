<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    public function kpiRecipients()
    {
        return $this->hasMany('App\KpiRecipient');
    }

    public function kpiList()
    {
        $list = [];
        foreach (Kpi::orderBy('name')->get() as $kpi) {
            $list[] = [
                'id' => $kpi->id,
                'name' => $kpi->name,
                'description' => $kpi->description,
                'selected' => $this->kpiRecipients()->where('kpi_id', $kpi->id)->count(),
            ];
        }

        return $list;
    }
}
