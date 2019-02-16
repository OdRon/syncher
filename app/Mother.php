<?php

namespace App;

use App\BaseModel;

class Mother extends BaseModel
{

    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }

    public function scopeExisting($query, $facility, $ccc)
    {
        return $query->where(['facility_id' => $facility, 'ccc_no' => $ccc]);
    }

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_mother_id' => $original->id]);
    }

    public function calc_age()
    {
        if($this->mother_dob){
            $today = date("Y-m-d");
            $this->age = \App\Lookup::calculate_viralage($today, $this->mother_dob);
        }
    }

    
}
