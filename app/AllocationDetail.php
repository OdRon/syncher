<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDetail extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];
    
    public function scopeExisting($query, $original_id) {
        return $query->where(['original_allocation_detail_id' => $original_id]);
    }

    public function machine(){
        return $this->belongsTo('App\Machine');
    }

    public function breakdown(){
        return $this->hasMany('App\AllocationDetailsBreakdown');
    }
    // public function kit(){
    //     return $this->belongsTo('App\Kits');
    // }
}
