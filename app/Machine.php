<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $table = "machines";

    public function kits() {
    	return $this->hasMany('App\Kits');
    }

    public function testsforLast3Months($lab = null) {
    	$id = $this->id;
    	$eid = Sample::selectRaw("count(*) as tests")->whereHas('worksheet', function($query) use ($id) {
		    		return $query->where('machine_type', '=', $id);
		    	})->whereRaw("datetested >= last_day(now()) + interval 1 day - interval 3 month")
                ->when($lab, function($query) use ($lab){
                    return $query->join('batches', 'batches.id', '=', 'samples.batch_id')->where('lab_id', '=', $lab);
                })->first()->tests;

    	$vl = Viralsample::selectRaw("count(*) as tests")->whereHas('worksheet', function($query) use ($id) {
		    		return $query->where('machine_type', '=', $id);
		    	})->whereRaw("datetested >= last_day(now()) + interval 1 day - interval 3 month")
                ->when($lab, function($query) use ($lab){
                    return $query->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')->where('lab_id', '=', $lab);
                })->first()->tests;

    	return (object)['EID' => $eid, 'VL' => $vl];
    }
}