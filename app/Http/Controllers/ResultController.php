<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lookup;
use App\Batch;
use App\Viralbatch;
use App\ViralsampleView as VLView;
use App\SampleView as EIDView;
use Excel;

class ResultController extends Controller
{
    public function index($testtype = 'EID')
    {
    	if ($testtype == 'EID'){
    		$testtypetitle = 'EID';
    		$data = Batch::select('*');
    		if (auth()->user()->user_type_id == 8)
    			$data = $data->where('facility_id', '=', auth()->user()->facility_id)->orderBy('datereceived', 'desc')->get();
    	}else if ($testtype == 'VL'){
    		$testtypetitle = 'VIRAL LOAD';
    		$data = Viralbatch::select('*');
    		if (auth()->user()->user_type_id == 8)
    			$data = $data->where('facility_id', '=', auth()->user()->facility_id)->orderBy('datereceived', 'desc')->get();
    	}else {
    		return back();
    	}
    	
    	return view('tables.batches', compact('data','testtype'))->with('pageTitle', "$testtypetitle Batches");
    }

    public function specific($ID, $testtype,$type)
    {
    	if (auth()->user()->user_type_id == 8) {
    		if ($type == 'batch') {
	            if ($testtype == 'EID') {
	                $batchID = $ID;
	                $data = Lookup::get_eid_lookups();
	                $batch = Batch::where('id', '=', $batchID)->get()->first();
                    $batch = $batch->load(['sample.patient.mother','view_facility', 'receiver', 'creator.facility']);
                    $data = (object) $data;;
                    $batchID = $batch->original_batch_id;
                    return view('tables.batch_details', compact('data','batch'))->with('pageTitle', "EID Batch :: $batchID");
	            } else if ($testtype == 'VL') {
	                $batchID = $ID;
	                $data = Lookup::get_viral_lookups();
	                $batch = Viralbatch::where('id', '=', $batchID)->get()->first();
                    $batch = $batch->load(['sample.patient','view_facility', 'receiver', 'creator.facility']);
	                $data = (object) $data;
	                $batchID = $batch->original_batch_id;
	                return view('tables.viralbatch_details', compact('data','batch'))->with('pageTitle', "VIRAL LOAD Batch :: $batchID");
	            } else {
	            	return back();
	            }
	        } else {
	        	return back();
	        }
        } else {}
    }

    public function get_incomplient_patient_record($year, $quarter = [1,2,3,4]) {
        $quarters = [1=>'(1,2,3)', 2 =>'(4,5,6)', 3 => '(7,8,9)', 4 => '(10,11,12)'];
        $data = [];
        $data[] = ['Lab', 'Quarter', 'Complient', 'Non-complient'];
        ini_set("memory_limit", "-1");
        foreach ($quarter as $key => $value) {
            $wanted = $quarters[$value];
            $model = VLView::orderBy('month', 'asc')->selectRaw("distinct patient, lab_id, year(datetested) as year, month(datetested) as month")
                        ->whereYear('datetested', $year)->whereRaw("month(datetested) in $wanted")->where('repeatt', 0)->limit(1)->get();
            $collection = $model;
            
            $labs = \App\Lab::get();
            foreach($labs as $lab) {
                $completed = 0;
                $incomplete = 0;
                foreach ($collection as $key => $collectionValue) {
                    if ($lab->id == $collectionValue->lab_id){
                        if (strlen($collectionValue->patient) < 10)
                            $incomplete++;
                        else
                            $completed++;
                    }
                }
                $data[] = [
                    'lab' => $lab->labdesc,
                    'quarter' => $year . ' Q'.$value,
                    'complient' => $completed,
                    'incomplient' => $incomplete
                ];
            }
        }
        $data = collect($data);
        $title = $year;
        if($data->isNotEmpty()) {
            Excel::create($title, function($excel) use ($data, $title) {
                $excel->setTitle($title);
                $excel->setCreator(auth()->user()->surname.' '.auth()->user()->oname)->setCompany('NASCOP.ORG');
                $excel->setDescription($title);

                $excel->sheet($title, function($sheet) use ($data) {
                    $sheet->fromArray($data, null, 'A1', false, false);
                });
            })->download('csv');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }
}
