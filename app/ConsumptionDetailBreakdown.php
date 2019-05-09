<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumptionDetailBreakdown extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */

    protected $guarded = [];

    public function scopeExisting($query, $details_id, $breakdown_id, $breakdown_type)
    {
        return $query->where(['consumption_details_id' => $details_id, 'breakdown_id' => $breakdown_id, 'breakdown_type' => $breakdown_type]);
    }


    public function apisave() {
        $this->synched = 1;
        $this->datesynched = date('Y-m-d');
        $this->save();
    }
}
