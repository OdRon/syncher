<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viralpatient extends Model
{


    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'patient_id');
    }
}
