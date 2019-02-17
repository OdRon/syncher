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

    public function merge(Request $request, $testtype = 'EID', $patient) {
        $testtype = strtolower($testtype);
        if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);

        $prefix = 'eid';
        if ($testtype == 'vl') $prefix = 'viral';
        $patient = Synch::$synch_arrays[$testtype]['patient_class']
                                    ::findOrFail($patient);
        
        if ($request->method() == "PUT") {
            $patients = $request->input('patients');
            $samples = Synch::$synch_arrays[$testtype]['sample_class']
                    ::whereIn('patient_id', $patients)->get();

            foreach ($samples as $key => $sample) {
                $sample->patient_id = $patient->id;
                $sample->pre_update();
            }

            $patient_array = Synch::$synch_arrays[$testtype]['patient_class']
                            ::whereIn('id', $patients)->where('id', '!=', $patient->id)->update(['synched' => 3]);

            session(['toast_message' => "The patient records have been merged."]);
            $redirect = 'patients/' . $testtype;
            return redirect($redirect);
        } else {
            if ($prefix == 'eid') $prefix = '';
            $data['patient'] = $patient;
            $data['url'] = url('patients/search/' . $testtype. '/' . $data['patient']->facility->id);
            $data['submit_url'] = url()->current();
            
            $data['testtype'] = strtoupper($testtype);
            $data = (object)$data;

            return view('forms.merge_patients', compact('data'))->with('pageTitle', '');
        }
    }

    public function transfer(Request $request, $testtype = 'EID', $patient) {
        $testtype = strtolower($testtype);
        if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
        
        if ($request->method() == "PUT"){

        } else {
            $data['testtype'] = strtoupper($testtype);
            $data['patient'] = Synch::$synch_arrays[$testtype]['patient_class']
                                    ::findOrFail($patient);
            $data = (object)$data;
            return view('forms.transfer_patient', compact('data'))->with('pageTitle', '');
        }
    }

    public function search(Request $request, $testtype='EID', $facility_id=null)
    {
        $testtype = strtolower($testtype);
        if(!($testtype == 'eid' || $testtype == 'vl')) abort(404);
       
        $table = 'patients';
        if($testtype == 'vl') $table = 'viralpatients';

        $user = auth()->user();
        $facility_user = false;
        if($user->user_type_id == 5) $facility_user=true;
        $string = "(facility_id='{$user->facility_id}')";
        $search = $request->input('search');
        
        $patients = Synch::$synch_arrays[$testtype]['patient_class']
                    ::select("$table.id", "$table.patient", 'facilitys.name', 'facilitys.facilitycode')
                    ->join('facilitys', 'facilitys.id', '=', "$table.facility_id")
                    ->whereRaw("patient like '" . $search . "%'")
                    ->when($facility_user, function($query) use ($string){
                        return $query->whereRaw($string);
                    })->when($facility_id, function($query) use ($facility_id){
                        return $query->where('facility_id', $facility_id);
                    })->paginate(10);
        
        $patients->setPath(url()->current());
        return $patients;

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
}
