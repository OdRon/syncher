<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewModel extends Model
{

    /**
     * Get the sample's received status name
     *
     * @return string
     */
    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }

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

    public function my_string_format($value, $default='0')
    {
        if($this->$value) return (string) $this->$value;
        return $default;
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }



    public function scopeLocate($query, $original, $lab_id)
    {
        return $query->where(['original_sample_id' => $original->id, 'lab_id' => $lab_id]);
    }

    public function scopeSample($query, $facility, $patient, $datecollected)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient, 'datecollected' => $datecollected]);
    }

    public function scopeExisting($query, $data_array)
    {
        return $query->where(['facility_id' => $data_array['facility_id'], 'patient' => $data_array['patient'], 'datecollected' => $data_array['datecollected']]);
    }

    public function scopePatient($query, $facility, $patient)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient]);
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
