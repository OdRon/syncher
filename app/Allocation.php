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


    public function scopeExisting($query, $year, $month, $testtype, $machine)
    {
        return $query->where(['year' => $year, 'month' => $month, 'testtype' => $testtype, 'machine_id' => $machine]);
    }

    public function machine(){
        return $this->belongsTo('App\Machine');
    }

    public function details() {
        return $this->hasMany('App\AllocationDetail');
    }
}
