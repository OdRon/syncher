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


    public function scopeExisting($query, $year, $month, $lab_id)
    {
        return $query->where(['year' => $year, 'month' => $month, 'lab_id' => $lab_id]);
    }

    public function details() {
        return $this->hasMany('App\AllocationDetail');
    }

    public function lab() {
        return $this->belongsTo('App\Lab');
    }

    public function reviewed($testtype=null){
        $details = $this->details->when($testtype, function($query) use ($testtype){
                            if ($testtype == 'EID')
                                return $query->where('testtype', '=', 1);
                            else if ($testtype == 'VL')
                                return $query->where('testtype', '=', 2);
                            else if ($testtype == 'CONSUMABLES')
                                return $query->where('testtype', '=', NULL);         
                        })->where('approve', '<>', 0)->count();
        if ($details > 0)
            return true;
        return false;
    }
}
