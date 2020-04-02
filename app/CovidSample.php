<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidSample extends BaseModel
{

	protected $connection = 'covid';

    protected $hidden = ['county_id'];

	protected $dates = ['datecollected', 'datereceived', 'datetested', 'datedispatched', 'dateapproved', 'dateapproved2'];

	protected $casts = [
		'symptoms' => 'array',
		'observed_signs' => 'array',
		'underlying_conditions' => 'array',		
	];

    public function patient()
    {
        return $this->belongsTo('App\CovidPatient', 'patient_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CovidWorksheet', 'worksheet_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\CovidSample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\CovidSample', 'parentid');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function calc_age()
    {
        if($this->datecollected) $this->age = $this->datecollected->diffInYears($this->patient->dob);
        $this->age = now()->diffInYears($this->patient->dob);
    }


    public function setResultAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['result'] = $value;
        else{
            $value = strtolower($value);
            if(str_contains($value, ['neg'])) $this->attributes['result'] = 1;
            else if(str_contains($value, ['pos'])) $this->attributes['result'] = 2;
        }
    }

    public function setSampleTypeAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['sample_type'] = $value;
        else{
            $a = explode(' ', $value);
            if(count($a) == 1) $a = explode('-', $value);
            $word = $a[0];
            $this->attributes['sample_type'] = DB::table('covid_sample_types')->where('name', 'like', "{$value}%")->first()->id ?? null;
        }
    }

}
