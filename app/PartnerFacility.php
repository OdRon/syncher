<?php

namespace App;

class PartnerFacility extends BaseModel
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
