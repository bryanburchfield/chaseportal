<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamCheckBatch extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'uploaded_at',
        'process_started_at',
        'processed_at',
    ];

    public function spamCheckBatchDetails()
    {
        return $this->hasMany('App\Models\SpamCheckBatchDetail');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function errorRecs()
    {
        return SpamCheckBatchDetail::where('spam_check_batch_id', $this->id)
            ->whereNotNull('succeeded')
            ->where('succeeded', false);
    }

    public function flaggedRecs()
    {
        return SpamCheckBatchDetail::where('spam_check_batch_id', $this->id)
            ->where('flagged', true);
    }

    public function percentProcessed()
    {
        $total = $this->spamCheckBatchDetails()->where('succeeded', 1)->count();
        $processed = $this->spamCheckBatchDetails()->where('checked', 1)->count();

        $percent = ($total > 0) ? $processed / $total * 100 : 0;

        return number_format($percent, 0);
    }
}
