<?php

namespace App;

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
}
