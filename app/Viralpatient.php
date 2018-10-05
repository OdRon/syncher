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

    public function getGenderAttribute(){
        $sex = $this->sex;
        // $gender = \DB::table('gender')->where('id', '=', $sex)->first();

        // return $gender->gender_description;
        if($sex == 1) {
            $gender = 'Male';
        } else if ($sex == 2) {
            $gender = 'Female';
        } else {
            $gender = 'No data';
        }
        return $gender;
    }
}
