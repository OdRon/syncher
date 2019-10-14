<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerFacility extends Model
{	
    public $timestamps = false;


    public function partner()
    {
    	return $this->belongsTo('App\Partner', 'partner_id');
    }

    public function facility()
    {
    	return $this->belongsTo('App\Facility', 'facility_id');
    }
}
