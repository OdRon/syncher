<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportPermission extends BaseModel
{
    public function reports () {
    	return $this->belongsTo('App\PartnerReport');
    }

    public function user_type() {
    	return $this->belongsTo('App\UserType');
    }
}
