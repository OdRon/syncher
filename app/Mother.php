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

    
}
