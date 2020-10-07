<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Dispo extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Dispos';

    /**
     * Return list of available Dispos
     * 
     * @param mixed|null $campaign 
     * @param mixed|null $group_id 
     * @return mixed 
     */
    public static function availableDispos($campaign = null, $group_id = null)
    {
        // select distinct Disposition
        // from Dispos
        // where (GroupId = 777 OR IsSystem=1)
        // AND (Campaign = '' OR Campaign = '')
        // order by Disposition

        if (Auth::check() && empty($group_id)) {
            $group_id = Auth::user()->group_id;
        }

        return Dispo::select('Disposition')
            ->where(function ($query) use ($group_id) {
                $query->where('GroupId', $group_id)
                    ->orWhere('IsSystem', 1);
            })
            ->where(function ($query) use ($campaign) {
                $query->where('Campaign', $campaign)
                    ->orWhere('Campaign', '');
            })
            ->distinct()
            ->orderBy('Disposition')
            ->get();
    }
}
