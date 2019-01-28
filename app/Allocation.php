<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];


    public function scopeExisting($query, $year, $month, $testtype, $kit)
    {
        return $query->where(['year' => $year, 'month' => $month, 'testtype' => $testtype, 'kit_id' => $kit]);
    }
}
