<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidSample extends BaseModel
{

	protected $connection = 'covid';

	protected $dates = ['datecollected', 'datereceived', 'datetested', 'datedispatched', 'dateapproved', 'dateapproved2'];

	protected $casts = [
		'symptoms' => 'array',
		'observed_signs' => 'array',
		'underlying_conditions' => 'array',		
	];
}
