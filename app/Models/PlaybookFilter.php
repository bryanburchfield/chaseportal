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

    public function playbook_touch_filters()
    {
        return $this->hasMany('App\Models\PlaybookTouchFilter');
    }

    public function getOperatorNameAttribute()
    {
        $operators = self::getOperators();
        foreach ($operators as $group => $ops) {
            foreach ($ops as $operator => $description) {
                if ($operator == $this->operator) {
                    return $description;
                }
            }
        }

        return '';
    }

    /**
     * Return list of operators for filters
     * 
     * @param bool $detail 
     * @return array 
     */
    public static function getOperators($field_type = null)
    {
        $mathops_detail = self::mathopsDetail();
        $dateops_detail = self::dateopsDetail();

        $mathops = [];
        $dateops = [];
        foreach ($mathops_detail as $key => $array) {
            $mathops[$key] = $array['description'];
        }
        foreach ($dateops_detail as $key => $array) {
            $dateops[$key] = $array['description'];
        }

        $return = [
            'integer' => $mathops,
            'string' => $mathops,
            'date' => array_merge($mathops, $dateops),
            'text' => $mathops,
            'phone' => $mathops,
        ];

        if ($field_type !== null) {
            if (isset($return[$field_type])) {
                return $return[$field_type];
            }
            return [];
        }

        return $return;
    }

    public static function operatorDetail($operator)
    {
        $mathops_detail = self::mathopsDetail();
        $dateops_detail = self::dateopsDetail();

        $ops = array_merge($mathops_detail, $dateops_detail);

        return $ops[$operator];
    }

    private static function mathopsDetail()
    {
        return [
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
    }

    private static function dateopsDetail()
    {
        return [
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
    }
}
