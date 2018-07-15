<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewModel extends Model
{

    public function my_date_format($value)
    {
        if($this->$value) return date('d-M-Y', strtotime($this->$value));

        return '';
    }

    public function capitalised($value)
    {
        if($this->$value) return strtoupper($this->$value);

        return '';
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getGenderAttribute()
    {
        if($this->sex == 1){ return "Male"; }
        else if($this->sex == 2){ return "Female"; }
        else{ return "No Gender"; }
    }


    /**
     * Get the patient's gender shortened
     *
     * @return string
     */
    public function getGenderShortAttribute()
    {
        if($this->sex == 1){ return "M"; }
        else if($this->sex == 2){ return "F"; }
        else{ return "No Gender"; }
    }


    /**
     * Get the sample's received status name
     *
     * @return string
     */
    public function getReceivedAttribute()
    {
        if($this->receivedstatus == 1){ return "Accepted"; }
        else if($this->receivedstatus == 2){ return "Rejected"; }
        else{ return ""; }
    }


    public function eid_prev_test()
    {
        $sample = \App\Sample::where('patient_id', $this->patient_id)
                ->whereRaw("datetested=
                    (SELECT max(datetested) FROM samples WHERE patient_id={$this->patient_id} AND repeatt=0 AND result in (1, 2) AND datetested < '{$this->datetested}')")
                ->get()->first();
        $this->previous = $sample;
    }

    public function vl_prev_test()
    {
        $sample = \App\Viralsample::where('patient_id', $this->patient_id)
                ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$this->patient_id} AND repeatt=0 AND rcategory in (1, 2, 3, 4) AND datetested < '{$this->datetested}')")
                ->get()->first();
        $this->previous = $sample;
    }
}
