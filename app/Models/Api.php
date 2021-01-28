<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Api extends Model
{
    public $timestamps = false;

    public function api_transactions()
    {
        return $this->hasMany('App\Models\ApiTransaction');
    }

    public function transactions_since($time)
    {
        return ApiTransaction::where('api_id', $this->id)
            ->where('created_at', '>=', $time)
            ->count();
    }

    public function add_transaction()
    {
        try {
            $api_transaction = new ApiTransaction(['api_id' => $this->id]);
            $api_transaction->save();
        } catch (Exception $e) {
            Log::error('Failed to insert transaction for ' . $this->name);
            Log::error($e->getMessage());
        }
    }

    public function trimTransactions($time)
    {
        ApiTransaction::where('api_id', $this->id)
            ->where('created_at', '<', $time)
            ->delete();
    }
}
