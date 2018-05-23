<?php

namespace App;

use App\BaseModel;

class Batch extends BaseModel
{
  
	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function scopeExisting($query, $original_id, $lab)
    {
        return $query->where(['original_batch_id' => $original_id, 'lab_id' => $lab]);
    }

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_batch_id' => $original->id, 'lab_id' => $original->lab_id]);
    }
}
