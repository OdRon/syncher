<?php

namespace App;

use App\BaseModel;

class Viralsample extends BaseModel
{


    public function tat($datedispatched)
    {
        return \App\Misc::working_days($this->datecollected, $datedispatched);
    }

    public function patient()
    {
    	return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Viralbatch', 'batch_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\Viralworksheet', 'worksheet_id');
    }

    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Viralsample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Viralsample', 'parentid');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

    public function scopeLocate($query, $original)
    {
        return $query->where(['original_sample_id' => $original->id]);
    }

    public function last_test()
    {
        $sample = \App\Viralsample::where('patient_id', $this->patient_id)
                ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$this->patient_id} AND repeatt=0 AND rcategory between 1 and 4 AND datetested < '{$this->datetested}')")
                ->get()->first();
        $this->recent = $sample;
    }

    public function prev_tests()
    {
        $s = $this;
        $samples = \App\Viralsample::where('patient_id', $this->patient_id)
                ->when(true, function($query) use ($s){
                    if($s->datetested) return $query->where('datetested', '<', $s->datetested);
                    return $query->where('datecollected', '<', $s->datecollected);
                })
                ->where('repeatt', 0)
                ->whereIn('rcategory', [1, 2, 3, 4])
                ->get();
        $this->previous_tests = $samples;
    }

    /**
     * Get the sample's result comment
     *
     * @return string
     */
    public function getResultCommentAttribute()
    {
        $str = '';
        $result = $this->result;
        $interpretation = $this->interpretation;
        $lower_interpretation = strtolower($interpretation);
        // < ldl
        if(str_contains($interpretation, ['<'])){
            $str = "LDL:Lower Detectable Limit ";
            // $str .= "i.e. Below Detectable levels by machine ";
            // if(str_contains($interpretation, ['839'])){
            //     $str .= "( Abbott DBS  &lt;839 copies/ml )";
            // }
            // else if(str_contains($interpretation, ['40'])){
            //     $str .= "( Abbott Plasma  &lt;40 copies/ml )";
            // }
            // else if(str_contains($interpretation, ['150'])){
            //     $str .= "( Abbott Plasma  &lt;150 copies/ml )";
            // }
            // else if(str_contains($interpretation, ['20'])){
            //     $str .= "( Roche Plasma  &lt;20 copies/ml )";
            // }
            // else if(str_contains($interpretation, ['30'])){
            //     $str .= "( Pantha Plasma  &lt;30 copies/ml )";
            // }
            // else{
            //     $n = preg_replace("/[^<0-9]/", "", $interpretation);
            //     $str .= "( &lt;{$n} copies/ml )";
            // }
        }
        else if(str_contains($result, ['<']) && str_contains($lower_interpretation, ['not detected'])){
            $str = "No circulating virus ie. level of HIV in blood is below the threshold needed for detection by this test. Doesn’t mean client Is Negative";
        }
        else if($result == "Target Not Detected"){
            $str = "No circulating virus ie. level of HIV in blood is below the threshold needed for detection by this test. Doesn’t mean client Is Negative";
        }
        else if($result == "Collect New Sample" || $result == "Failed"){
            $str = "Sample failed during processing due to sample deterioration or equipment malfunction.  Redraw another sample and send to lab as soon as possible";
        }
        else{}
        return "<small>{$str}</small>";
    }

}
