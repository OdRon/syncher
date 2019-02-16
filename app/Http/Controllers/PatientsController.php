<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Synch;
use App\Patient;
use App\Viralpatient;
use App\Lookup;

class PatientsController extends Controller
{
    public function index($testtype = 'EID') {
    	$testtype = strtolower($testtype);
    	if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);

    	$data['testtype'] = strtoupper($testtype);
    	$data['patients'] = Synch::$synch_arrays[$testtype]['patient_class']
    				::where('facility_id', '=', auth()->user()->facility_id)
    				->withCount(['sample' => function ($query){
			            $query->where('repeatt', 0);
			        }])->limit(20)->get();
    	$data = (object)$data;
    	return view('tables.patients', compact('data'))->with('pageTitle','');
    }

    public function edit(Request $request, $testtype = 'EID', $patient) {
    	$testtype = strtolower($testtype);
    	if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
        
        $prefix = 'eid';
        if ($testtype == 'vl') $prefix = 'viral';

    	if($request->method() == "PUT") { // When form data is submitted
    		$patient = Synch::$synch_arrays[$testtype]['patient_class']
	    						::findOrFail($patient);
            $samples_array = $prefix.'samples_arrays';
            $data = $request->only(Lookup::$samples_array()['patient']);
	    	$patient->fill($data);
            if ($testtype == 'eid') { // Set infant ccc_no and update mother details.
                $patient->ccc_no = $request->input('enrollment_ccc_no');

                $data = $request->only(Lookup::$samples_array()['mother']);
                $mother = $patient->mother;
                $mother->mother_dob = Lookup::calculate_mother_dob(date('Y-m-d'), $request->input('mother_age')); 
                $mother->fill($data);

                $viralpatient = Viralpatient::existing($mother->facility_id, $mother->ccc_no)->first();
                if($viralpatient) $mother->patient_id = $viralpatient->id;

                $mother->pre_update();
            }
            $patient->pre_update();
    		$redirect = 'patients/' . $testtype;
            return redirect($redirect);
    	} else {  // For loading the form
            $lookups = 'get_'.$prefix.'_lookups';
    		$data = Lookup::$lookups();
    		$data['testtype'] = strtoupper($testtype);
    		$data['patient'] = Synch::$synch_arrays[$testtype]['patient_class']
    								::findOrFail($patient);
            $data = (object)$data;
            
	    	if ($testtype == 'eid'){
                $data->patient->mother->calc_age();
	    		return view('forms.patients', compact('data'))->with('pageTitle', '');
            } else
	    		return view('forms.viralpatients', compact('data'))->with('pageTitle', '');
    	}
    }

    public function merge(Request $request, $testtype = 'EID', $patient) {
    	$testtype = strtolower($testtype);
    	if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
		
		if ($request->mehtod() == "PUT") {

		} else {
    		return view('forms.merge_patients', compact('data'))->with('pageTitle', '');
    	}
    }

    public function transfer(Request $request, $testtype = 'EID', $patient) {
    	$testtype = strtolower($testtype);
    	if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
		
		if ($request->mehtod() == "PUT"){

		} else {
    		return view('forms.transfer_patient', compact('data'))->with('pageTitle', '');
    	}
    }
}
