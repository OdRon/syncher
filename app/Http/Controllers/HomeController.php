<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleView;
use App\ViralsampleView;
use App\Batch;
use App\Viralbatch;
use App\Lookup;
use DB;
use Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->user_type_id == 1)
            return redirect('users');
            // return view('dashboard.home')->with('pageTitle', 'Dashboard');
        if (auth()->user()->user_type_id == 8) {
            $batch = session('batcheLoggedInWith');
            if (isset($batch['eid'])) {
                $batchID = $batch['eid'];
                $data = Lookup::get_eid_lookups();
                $batch = Batch::where('original_batch_id', '=', $batchID)->where('facility_id', '=', auth()->user()->facility_id)->first();
                $batch = $batch->load(['sample.patient.mother','view_facility', 'receiver', 'creator.facility']);
                $data = (object) $data;
                // dd($batch);
                return view('tables.batch_details', compact('data','batch'))->with('pageTitle', "EID Batch :: $batchID");
            } else {
                $batchID = $batch['vl'];
                $data = Lookup::get_viral_lookups();
                $batch = Viralbatch::where('original_batch_id', '=', $batchID)->where('facility_id', '=', auth()->user()->facility_id)->first();
                $batch = $batch->load(['sample.patient','view_facility', 'receiver', 'creator.facility']);
                $data = (object) $data;
                // dd($batch);
                return view('tables.viralbatch_details', compact('data','batch'))->with('pageTitle', "VIRAL LOAD Batch :: $batchID");
            }
        }

        if(auth()->user()->user_type_id == 9)
            return redirect('reports/support');

        if (auth()->user()->user_type_id == 12)
            return redirect('allocations');
        if (auth()->user()->user_type_id == 14 || auth()->user()->user_type_id == 14)
        	return redirect('national/allocation');
        
        return redirect('reports/EID');
    }

    public function test($year = null, $month = null) {
        // echo "Method start \n";
        ini_set("memory_limit", "-1");
    	if($year==null){
    		$year = Date('Y');
    	}

    	$raw = "samples_view.id, samples_view.patient, samples_view.facility_id, labs.name as lab, view_facilitys.name as facility_name, view_facilitys.county, samples_view.pcrtype, datetested";
    	$raw2 = "samples_view.id, samples_view.patient, samples_view.facility_id, samples_view.pcrtype, datetested";

    	$data = DB::table("samples_view")
		->select(DB::raw($raw))
		->join('view_facilitys', 'samples_view.facility_id', '=', 'view_facilitys.id')
		->join('labs', 'samples_view.lab_id', '=', 'labs.id')
		->orderBy('samples_view.facility_id', 'desc')
		->whereYear('datetested', $year)
		->when($month, function($query) use ($month){
			if($month != null || $month != 0){
				return $query->whereMonth('datetested', $month);
			}
		})
		->where('result', 2)
		->where('samples_view.repeatt', 0)
		->where('samples_view.flag', 1)
		->where('samples_view.eqa', 0)
		->get();

		// return $data;


		$i = 0;
		$result = null;

		foreach ($data as $patient) {
			

	    	$d = DB::table("samples_view")
			->select(DB::raw($raw2))
			->where('facility_id', $patient->facility_id)
			->where('patient', $patient->patient)
			->where('datetested', '<', $patient->datetested)
			->where('result', 1)
			->where('repeatt', 0)
			->where('flag', 1)
			->where('eqa', 0)
			->first();

			if($d){
				$result[$i]['laboratory'] = $patient->lab;
                $result[$i]['facility'] = $patient->facility_name;
                $result[$i]['county'] = $patient->county;
				$result[$i]['patient_id'] = $patient->patient;
				$result[$i]['negative_sample_id'] = $d->id;
				$result[$i]['negative_date'] = $d->datetested;
				$result[$i]['negative_pcr'] = $d->pcrtype;
				$result[$i]['positive_sample_id'] = $patient->id;
				$result[$i]['positive_date'] = $patient->datetested;
				$result[$i]['positive_pcr'] = $patient->pcrtype;
				$i++;

				// echo "Found 1 \n";
				$d = null;
			}


		}

		Excel::create('Negative_to_Positive', function($excel) use($result)  {

		    // Set sheets

		    $excel->sheet('Sheetname', function($sheet) use($result) {

		        $sheet->fromArray($result);

		    });

		})->download('csv');

		// return $result;
    }

    

    public function negatives_report($year=null, $month=null){
        // echo "Method start \n";
        ini_set("memory_limit", "-1");
    	if($year==null){
    		$year = Date('Y');
    	}

    	$raw = "samples_view.id, samples_view.patient, samples_view.facility_id, labs.name as lab, view_facilitys.name as facility_name, view_facilitys.county, samples_view.pcrtype,  datetested";
    	$raw2 = "samples_view.id, samples_view.patient, samples_view.facility_id, samples_view.pcrtype, datetested";

    	$data = DB::table("samples_view")
		->select(DB::raw($raw))
		->join('view_facilitys', 'samples_view.facility_id', '=', 'view_facilitys.id')
		->join('labs', 'samples_view.lab_id', '=', 'labs.id')
		->orderBy('samples_view.facility_id', 'desc')
		->whereYear('datetested', $year)
		->when($month, function($query) use ($month){
			if($month != null || $month != 0){
				return $query->whereMonth('datetested', $month);
			}
		})
		->where('result', 1)
		->where('samples_view.repeatt', 0)
		->where('samples_view.flag', 1)
		->where('samples_view.eqa', 0)
		->get();

		// echo "Total {$data->count()} \n";

		$i = 0;
		$result = null;

		foreach ($data as $patient) {

	    	$d = DB::table("samples_view")
			->select(DB::raw($raw2))
			->where('facility_id', $patient->facility_id)
			->where('patient', $patient->patient)
			->where('datetested', '<', $patient->datetested)
			->where('result', 2)
			->where('repeatt', 0)
			->where('flag', 1)
			->where('eqa', 0)
			->first();

			if($d){
				$result[$i]['laboratory'] = $patient->lab;
                $result[$i]['facility'] = $patient->facility_id;
                $result[$i]['county'] = $patient->county;
				$result[$i]['patient_id'] = $patient->patient;

				$result[$i]['negative_sample_id'] = $patient->id; 
				$result[$i]['negative_date'] = $patient->datetested;
				$result[$i]['negative_pcr'] = $patient->pcrtype;

				$result[$i]['positive_sample_id'] = $d->id;
				$result[$i]['positive_date'] =  $d->datetested;
				$result[$i]['positive_pcr'] = $d->pcrtype;
				$i++;

				// echo "Found 1 \n";
				$d = null;
			}


		}

		Excel::create('Positive_to_Negative', function($excel) use($result)  {

		    // Set sheets

		    $excel->sheet('Sheetname', function($sheet) use($result) {

		        $sheet->fromArray($result);

		    });

		})->store('csv');

		// return $result;
    }
}