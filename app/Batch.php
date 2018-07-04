<?php

namespace App;

use App\BaseModel;

class Batch extends BaseModel
{
    public function outdated()
    {
        $now = \Carbon\Carbon::now();

        if($now->diffInMonths($this->created_at) > 6) return true;
        return false;
    }

	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
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
