<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportCategory extends BaseModel
{
    public function reports () {
    	return $this->hasMany('App\PartnerReport');
    }
}
