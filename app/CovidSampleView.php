<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidSampleView extends BaseModel
{
	protected $connection = 'covid';

	protected $table = "covid_sample_view";

    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getGenderAttribute()
    {
        if($this->sex == 1){ return "Male"; }
        else if($this->sex == 2){ return "Female"; }
        else{ return "No Gender"; }
    }
}
