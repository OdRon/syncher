<?php

namespace App;

use App\BaseModel;

class Mother extends BaseModel
{

    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }
}
