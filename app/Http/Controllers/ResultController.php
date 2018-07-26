<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lookup;
use App\Batch;
use App\Viralbatch;

class ResultController extends Controller
{
    public function index($testtype = 'EID')
    {
    	if ($testtype == 'EID'){
    		$testtypetitle = 'EID';
    		$data = Batch::select('*');
    		if (auth()->user()->user_type_id == 8)
    			$data = $data->where('facility_id', '=', auth()->user()->facility_id)->get();
    	}else if ($testtype == 'VL'){
    		$testtypetitle = 'VIRAL LOAD';
    		$data = Viralbatch::select('*');
    		if (auth()->user()->user_type_id == 8)
    			$data = $data->where('facility_id', '=', auth()->user()->facility_id)->get();
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
}
