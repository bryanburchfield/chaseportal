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

    /**
     * Return list of operators for filters
     * 
     * @param bool $detail 
     * @return array 
     */
    public static function getOperators($detail = false)
    {
        $mathops_detail = [
            '=' => [
                'description' => trans('tools.equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '!=' => [
                'description' => trans('tools.not_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<' => [
                'description' => trans('tools.less_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>' => [
                'description' => trans('tools.greater_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<=' => [
                'description' => trans('tools.less_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>=' => [
                'description' => trans('tools.greater_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            'blank' => [
                'description' => trans('tools.is_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
            'not_blank' => [
                'description' => trans('tools.is_not_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
        ];

        $dateops_detail = [
            'days_ago' => [
                'description' => trans('tools.days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            'days_from_now' => [
                'description' => trans('tools.days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_ago' => [
                'description' => trans('tools.less_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_ago' => [
                'description' => trans('tools.greater_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_from_now' => [
                'description' => trans('tools.less_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_from_now' => [
                'description' => trans('tools.greater_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
        ];

        // there's a better way to do this
        if ($detail) {
            $mathops = $mathops_detail;
            $dateops = $dateops_detail;
        } else {
            $mathops = [];
            $dateops = [];
            foreach ($mathops_detail as $key => $array) {
                $mathops[$key] = $array['description'];
            }
            foreach ($dateops_detail as $key => $array) {
                $dateops[$key] = $array['description'];
            }
        }

        return [
            'integer' => $mathops,
            'string' => $mathops,
            'date' => array_merge($mathops, $dateops),
            'text' => $mathops,
            'phone' => $mathops,
        ];
    }
}
