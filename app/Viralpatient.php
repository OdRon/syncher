<?php

namespace App;

use App\BaseModel;

class Viralpatient extends BaseModel
{


    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'patient_id');
    }

    public function scopeExisting($query, $facility_id, $ccc_number)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $ccc_number]);
    }
}
