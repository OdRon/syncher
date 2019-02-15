<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Synch;
use App\Patient;
use App\Viralpatient;

class PatientsController extends Controller
{
    public function index($testtype = 'EID') {
    	$testtype = strtolower($testtype);
    	if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);

    	$data['prefix'] = ($testtype == 'vl') ? 'viral' : '';
    	$data['patients'] = Synch::$synch_arrays[$testtype]['patient_class']
    				::where('facility_id', '=', auth()->user()->facility_id)
    				->withCount(['sample' => function ($query){
			            $query->where('repeatt', 0);
			        }])->limit(20)->get();
    	$data = (object)$data;
    	return view('tables.patients', compact('data'))->with('pageTitle','');
    }
}
