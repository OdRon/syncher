<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
  
	   public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }
}
