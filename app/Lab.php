<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    //
    public $timestamps = false;

    public function getTokenNameAttribute()
    {
    	return 'lab_' . $this->id . '_token';
    }

    public function allocations(){
    	return $this->hasMany('App\Allocation');
    }

    public function consumptions(){
        return $this->hasMany('App\Consumption');
    }

    public function allocation_contacts() {
        return $this->hasOne('App\AllocationContact');
    }
}
