<?php

namespace App;

use DB;
use App\ViewModel;

class SampleCompleteView extends ViewModel
{
	protected $table = 'sample_complete_view';

    public function facility()
    {
        return $this->belongsTo('App\ViewFacility','facility_id');
    }

    public function lab()
    {
    	return $this->belongsTo('App\Lab','lab_id');
    }
    public function hei_validation ($hei_validation = null) {
    	if (!isset($hei_validation))
    		return null;
    	return DB::table('hei_validation')->where('id','=',$hei_validation)->first();
    }
}
