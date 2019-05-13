<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    private $testtypes = [
            'EID' => SamplView::class,
            'VL' => ViralsamplView::class
    ];
    //
    public $timestamps = false;

    public function getTokenNameAttribute()
    {
    	return 'lab_' . $this->id . '_token';
    }

    public function allocations(){
    	return $this->hasMany('App\Allocation');
    }

    public function consumptions(){
        return $this->hasMany('App\Consumption');
    }

    public function allocation_contacts() {
        return $this->hasOne('App\AllocationContact');
    }

    public static function samples_breakdown_count($testtype, $year, $month = null) {
        $ordinary_samples = $this->get_ordinary_samples($testtype, $year, $month);
    }

    private function get_ordinary_samples($testtype, $year, $month = null) {
        $class = $this->testtypes[$testtype];
        $samples = $class->selectRaw(" count(*) as `samples`, year(datereceived) as `actualyear`, monthname(datereceived) as `actualmonth`")->where('site_entry', '=', '0')->where('lab_id', $this->id)->get();
        return $samples;
    }
}
