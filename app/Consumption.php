<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function kit() {
    	return $this->belongsTo('App\Kits');
    }


    public function scopeExisting($query, $year, $month, $testtype, $kit)
    {
        return $query->where(['year' => $year, 'month' => $month, 'testtype' => $testtype, 'kit_id' => $kit]);
    }
}
