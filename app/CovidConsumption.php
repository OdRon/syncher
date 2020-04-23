<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidConsumption extends BaseModel
{
    public function details()
    {
    	return $this->hasMany(CovidConsumptionDetail::class, 'consumption_id', 'id');
    }

    public function scopeExisting($query, $start_of_week)
    {
        return $query->where(['start_of_week' => $start_of_week]);
    }
}
