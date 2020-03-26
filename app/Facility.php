<?php

namespace App;

use App\BaseModel;

class Facility extends BaseModel
{
	
    protected $table = "facilitys";

    public $timestamps = false;

    public function scopeLocate($query, $param)
    {
    	if(is_numeric($param)) return $query->where('DHIScode', $param);
    	 return $query->where('facilitycode', $param);
    }
}
