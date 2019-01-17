<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\MlabRequest;

use \App\SampleView;
use \App\ViralsampleView;

class MlabController extends Controller
{

    public function api(MlabRequest $request)
    {     
        $test = $request->input('test');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $date_dispatched_start = $request->input('date_dispatched_start');
        $date_dispatched_end = $request->input('date_dispatched_end');
        $patients = $request->input('patient_id');
        $facilities = $request->input('facility_code');
        $dispatched = $request->input('dispatched');   
        $ids = $request->input('ids');   

        if($test == 2) $class = SampleView::class;
        else if($test == 1) $class = ViralsampleView::class;

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
 
        $result = $class::when($facilities, function($query) use($facilities){
                return $query->whereIn('facilitycode', $facilities);
            })
            ->when($dispatched, function($query){
                // return $query->whereNotNull('datedispatched');
                return $query->whereRaw("(datedispatched is not null OR (dateapproved is not null and dateapproved2 is not null))");
            })
            ->when($patients, function($query) use($patients, $test){
                if($test == 3) return $query->whereIn('medicalrecordno', $patients);
                return $query->whereIn('patient', $patients);
            })
            ->when($ids, function($query) use($ids){
                return $query->whereIn('id', $ids);
            })
            ->when(($start_date && $end_date), function($query) use($start_date, $end_date){
                return $query->whereBetween('datecollected', [$start_date, $end_date]);
            })
            ->when(($date_dispatched_start && $date_dispatched_end), function($query) use($date_dispatched_start, $date_dispatched_end){
                return $query->whereBetween('datedispatched', [$date_dispatched_start, $date_dispatched_end]);
            })
            ->where(['repeatt' => 0])          
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $result->transform(function ($sample, $key) use ($test){
            return [                
                'source' => '1',
                'result_id' => "{$sample->id}",
                'result_type' => "{$test}",
                'request_id' => '',
                'client_id' => $sample->patient,
                'age' => "{$sample->age}",
                'gender' => $sample->gender,
                'result_content' => "{$sample->result}",
                'units' => $sample->units ?? '',
                'mfl_code' => "{$sample->facilitycode}",
                'lab_id' => "{$sample->lab_id}",
                'date_collected' => $sample->datecollected ?? '0000-00-00',
                'cst' => $sample->my_string_format('sampletype'),
                'cj' => $sample->my_string_format('justification'),
                'csr' =>  "{$sample->rejectedreason}",
                'lab_order_date' => $sample->datetested ?? '0000-00-00',
            ];
        });

        return $result;

    }

}

