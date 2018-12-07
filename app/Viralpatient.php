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

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_patient_id' => $original->id, 'facility_id' => $original->facility_id]);
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getGenderAttribute()
    {
        if($this->sex == 1){ return "Male"; }
        else if($this->sex == 2){ return "Female"; }
        else{ return "No Gender"; }
    }
}
