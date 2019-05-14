<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationContact extends Model
{
    protected $guarded = [];

    public function lab()
    {
    	return $this->belongsTo('App\Lab');
    }
}
