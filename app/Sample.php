<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{

    public function patient()
    {
    	return $this->belongsTo('App\Patient');
    }

    public function batch()
    {
        return $this->belongsTo('App\Batch');
    }

    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Sample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Sample', 'parentid');
    }
}
