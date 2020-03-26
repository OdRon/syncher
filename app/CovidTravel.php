<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidTravel extends BaseModel
{

	protected $connection = 'covid';
	
	protected $dates = ['travel_date'];
}
