<?php

namespace App;

use App\BaseModel;

class Sample extends BaseModel
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

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_sample_id' => $original->id]);
    }
}
