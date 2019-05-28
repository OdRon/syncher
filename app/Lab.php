<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    //
    public $timestamps = false;



    
    public function getTokenNameAttribute()
    {
    	return 'lab_' . $this->id . '_token';
    }
}
