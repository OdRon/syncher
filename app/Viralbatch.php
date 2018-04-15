<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viralbatch extends Model
{

	public function sample()
    {
        return $this->hasMany('App\Viralsample', 'batch_id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }
}
