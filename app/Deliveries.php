<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deliveries extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function scopeExisting($query, $year, $quarter, $testtype, $kit)
    {
        return $query->where(['year' => $year, 'quarter' => $quarter, 'testtype' => $testtype, 'kit_id' => $kit]);
    }
}
