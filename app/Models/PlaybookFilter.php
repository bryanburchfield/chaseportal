<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookFilter extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'field',
        'operator',
        'value',
    ];
}
