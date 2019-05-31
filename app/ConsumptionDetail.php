<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumptionDetail extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */

    protected $guarded = [];

    public function scopeExisting($query, $consumption_id, $testtype, $machine)
    {
        return $query->where(['consumption_id' => $consumption_id, 'testtype' => $testtype, 'machine_id' => $machine]);
    }

    public function breakdown(){
        return $this->hasMany('App\ConsumptionDetailBreakdown', 'consumption_details_id');
    }

    public function machine() {
        return $this->belongsTo('App\Machine');
    }

    public function apisave() {
        $this->synched = 1;
        $this->datesynched = date('Y-m-d');
        $this->save();
    }
}
