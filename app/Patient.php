<?php

namespace App;

use App\BaseModel;

class Patient extends BaseModel
{

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function mother()
    {
    	return $this->belongsTo('App\Mother');
    }

    public function scopeExisting($query, $facility_id, $hei_number)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $hei_number]);
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
