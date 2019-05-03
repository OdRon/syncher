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

    public function apisave() {
        $this->synched = 1;
        $this->datesynched = date('Y-m-d');
        $this->save();
    }
}
