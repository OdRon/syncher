<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportPermission extends BaseModel
{
    public function report() {
    	return $this->belongsTo('App\PartnerReport', 'partner_report_id');
    }

    public function user_type() {
    	return $this->belongsTo('App\UserType');
    }
}
