<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidContact extends BaseModel
{

	protected $connection = 'covid';

    public function patient()
    {
        return $this->belongsTo('App\CovidPatient', 'patient_id');
    }
}
