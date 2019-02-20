<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\HitRequest;

use \App\SampleView;
use \App\ViralsampleView;

class HitController extends Controller
{

    public function eid(HitRequest $request)
    {     
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $date_dispatched_start = $request->input('date_dispatched_start');
        $date_dispatched_end = $request->input('date_dispatched_end');
        $patients = $request->input('patient_id');
        $facilities = $request->input('facility_code');
        $dispatched = $request->input('dispatched');   
        $ids = $request->input('ids');   

        // if($test == 2){
            $class = SampleView::class;
            $table = 'samples_view';
        // }
        // else if($test == 1){
        //     $class = ViralsampleView::class;
        //     $table = 'viralsamples_view';
        // }

        if($patients){
            $patients = str_replace(' ', '', $patients);
            $patients = explode(',', $patients);
        }
        if($ids){
            $ids = str_replace(' ', '', $ids);
            $ids = explode(',', $ids);
        }
        if($facilities){
            $facilities = str_replace(' ', '', $facilities);
            $facilities = explode(',', $facilities);
        }
 
        $result = $class::select("{$table}.*", 'view_facilitys.facilitycode')
            ->join('view_facilitys', 'view_facilitys.id', '=', "{$table}.facility_id")
            ->whereIn('county_id', [2, 33, 43])
            ->when($facilities, function($query) use($facilities){
                return $query->whereIn('facilitycode', $facilities);
            })
            ->when($patients, function($query) use($patients){
                return $query->whereIn('patient', $patients);
            })
            ->when($ids, function($query) use($ids){
                return $query->whereIn('original_sample_id', $ids);
            })
            ->when(($start_date && $end_date), function($query) use($start_date, $end_date){
                return $query->whereBetween('datecollected', [$start_date, $end_date]);
            })
            ->when(($date_dispatched_start && $date_dispatched_end), function($query) use($date_dispatched_start, $date_dispatched_end){
                return $query->whereBetween('datedispatched', [$date_dispatched_start, $date_dispatched_end]);
            })
            ->where(['repeatt' => 0])          
            ->orderBy('datecollected', 'desc')
            ->paginate(50);

        $result->transform(function ($sample, $key){

            return [        
                'lab_id' => $sample->id,
                'patient_id' => $sample->patient,
                'MFLCode' => $sample->facility_code,
                'date_collected' => $sample->datecollected,
                'date_received' => $sample->datereceived,
                'date_tested' => $sample->datetested,
                'date_dispatched' => $sample->datedispatched,
                'result' => $sample->result_name,
            ];
        });

        return $result;
    }

}

