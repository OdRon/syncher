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

    public function kit(){
        dd(Kits::find($this->kit_id));
    	return $this->belongsTo('App\Kits', 'kit_id');
    }
}
