<?php

namespace App;

use App\BaseModel;

class Viralsample extends BaseModel
{


    public function patient()
    {
    	return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Viralbatch', 'batch_id');
    }

    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Viralsample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Viralsample', 'parentid');
    }
}
