<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidPatient extends BaseModel
{

	protected $connection = 'covid';

	protected $dates = ['dob', 'date_symptoms', 'date_admission', 'date_isolation', 'date_death'];
}
