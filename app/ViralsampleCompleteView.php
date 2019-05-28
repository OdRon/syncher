<?php

namespace App;

use App\ViewModel;

class ViralsampleCompleteView extends ViewModel
{
	protected $table = 'viralsample_complete_view';

    public function facility()
    {
        return $this->belongsTo('App\ViewFacility','facility_id');
    }

    public function lab()
    {
    	return $this->belongsTo('App\Lab','lab_id');
    }
}
