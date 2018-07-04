<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\SampleView;
use App\Sample;
use App\Patient;

class HEIController extends Controller
{
    //
    public function index($year=null, $month = null)
    {
    	if ($year==null || $year=='null'){
    		// dd(session('followupYear'));
            if (session('followupYear')==null)
                session(['followupYear' => Date('Y')]);
        } else {
            session(['followupYear'=>$year]);
        }

        if ($month==null || $month=='null'){
            session()->forget('followupMonth');
        } else {
            session(['followupMonth'=>(strlen($month)==1) ? '0'.$month : $month]);
        }
        // dd(session('followupYear'));
    	$data['outcomes'] = self::__outcomes(session('followupYear'), session('followupMonth'));
    	$data['cumulative'] = self::__cumulativeOutcomes();

    	$data = (object)$data;

    	return view('hei.validate', compact('data'))->with('pageTitle','HEI Follow Up');
    }

    public function followup(Request $request, $level=null)
    {
    	$year = session('followupYear');
    	$month = session('followupMonth');
    	
    	if ($request->method() == 'POST') {
    		$data = [];
    		$columns = [ 'id', 'patient', 'hei_validation', 'enrollment_status', 'dateinitiatedontreatment', 'enrollment_ccc_no', 'facility_id', 'other_reason'];
    		$sampleCount = (int)$request->DataTables_Table_0_length ?? null;
    		if (isset($sampleCount) || $sampleCount > 0) {
    			for ($i=$sampleCount; $i > 0 ; $i--) { 
    				foreach ($columns as $key => $value) {
    					$name = $value.$i;
    					$data[$i][$value] = $request->$name;
    				}
    			}
    			$save = $this->saveHeis($data);
    			session(['toast_message'=>'Follow up for the '.$sampleCount.' patients complete']);
    			$unvalidated = self::__cumulativeOutcomes();
    			if ($unvalidated->unknown > 0 ) {
    				return redirect('hei/followup/cumulative');
    			} else {
    				return redirect('hei/validate');
    			}
    			
    		}
    	}
    	
    	$data['hei_categories'] = DB::table('hei_categories')->get();
    	$data['hei_validation'] = DB::table('hei_validation')->get();
    	$data['samples'] = self::__getSamples($level,$year,$month);

    	$data = (object)$data;
    	$monthName = "";
    	if (null !== $month) 
    		$monthName = "- ".date("F", mktime(null, null, null, $month));

    	return view('hei.followup', compact('data'))->with('pageTitle', "HEI Folow Up:$year $monthName");
    }

    public function saveHeis($data)
    {
    	foreach ($data as $key => $value) {
    		$value = (object)$value;
    		$sample = Sample::where('id', '=', $value->id)->get()->first();
    		// dd($sample);
    		$sample->hei_validation = $value->hei_validation;
    		if ($value->hei_validation == 1) {
    			$patient = Patient::where('patient', '=', $value->patient)->get()->first();
    			$sample->enrollment_status = $value->enrollment_status;
    			if ($value->enrollment_status == 1) {
    				$patient->ccc_no = $value->enrollment_ccc_no;
    				$patient->dateinitiatedontreatment = $value->dateinitiatedontreatment;
    				$patient->save();
    				$sample->enrollment_ccc_no = $value->enrollment_ccc_no;
    			} else if ($value->enrollment_status == 5) {
    				$patient->facility_id = $value->facility_id;
    				$patient->save();
    				$sample->referredfromsite = $value->facility_id;
    			} else if ($value->enrollment_status == 6) {
    				$sample->otherreason = $value->other_reason;
    			}
    		}
    		$sample->save();
    	}
    	return true;
    }
// 191836
    public static function __outcomes($year=null, $month=null)
    {
    	$positiveOutcomes = self::__getOutcomes(null,$year,$month);
    	$enrolled = self::__getOutcomes(1,$year,$month);
    	$ltfu = self::__getOutcomes(2,$year,$month);
    	$dead = self::__getOutcomes(3,$year,$month);
    	$transferOut = self::__getOutcomes(5,$year,$month);
    	$other = self::__getOutcomes(6,$year,$month);
    	$othervalidation = self::__getOutcomes('others');
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other+$othervalidation));

    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
		    			'othervalidation' => $othervalidation,
		    			'unknown' => $unknown
		    			];
    }

    public static function __cumulativeOutcomes()
    {
    	$positiveOutcomes = self::__getOutcomes(null);
    	$enrolled = self::__getOutcomes(1);
    	$ltfu = self::__getOutcomes(2);
    	$dead = self::__getOutcomes(3);
    	$transferOut = self::__getOutcomes(5);
    	$other = self::__getOutcomes(6);
    	$othervalidation = self::__getOutcomes('others');
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other+$othervalidation));

    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
		    			'othervalidation' => $othervalidation,
		    			'unknown' => $unknown
		    			];
    }

    public static function __getOutcomes($status,$year=null, $month=null)
    {
        $usertype = auth()->user()->user_type_id;
    	return SampleView::selectRaw("COUNT(*) as totalPositives")
					->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
					->where('samples_view.result', '=', 2)->where('samples_view.pcrtype', '=', 1)
                    ->where('samples_view.repeatt', '=', 0)
                    ->when($usertype, function($query) use ($usertype){
                        if($usertype == 3)
                            return $query->where('view_facilitys.partner_id', '=', auth()->user()->level);
                        if ($usertype == 4) 
                            return $query->where('view_facilitys.county_id', '=', auth()->user()->level);
                        if ($usertype == 5) 
                            return $query->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                    })
					->when($year, function($query) use ($year){
						return $query->whereRaw("YEAR(datetested) = $year");
					})
					->when($month, function($query) use ($month){
                        return $query->whereRaw("MONTH(datetested) = $month");
                    })->when($status, function($query) use ($status){
                    	if ($status == 'others') {
                    		return $query->where('samples_view.hei_validation', '<>', '0')->where('samples_view.hei_validation', '<>', '1');
                    	} else {
                        	return $query->where('samples_view.enrollment_status', '=', $status);
                        }
                    })->get()->first()->totalPositives;
    }

    public static function __getSamples($level=null,$year=null,$month=null)
    {
    	$usertype = auth()->user()->user_type_id;
        $model = SampleView::select('samples_view.*')
    				->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
					->where('samples_view.result', '=', 2)->where('samples_view.pcrtype', '=', 1)
					->where('samples_view.repeatt', '=', 0)
                    ->when($usertype, function($query) use ($usertype){
                        if($usertype == 3)
                            return $query->where('view_facilitys.partner_id', '=', auth()->user()->level);
                        if ($usertype == 4) 
                            return $query->where('view_facilitys.county_id', '=', auth()->user()->level);
                        if ($usertype == 5) 
                            return $query->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                    });

		if ($level != 'cumulative')
			$model->when($year, function($query) use ($year){
					return $query->whereRaw("YEAR(datetested) = $year");
				})->when($month, function($query) use ($month){
	                return $query->whereRaw("MONTH(datetested) = $month");
	            })->where('samples_view.hei_validation', '=', 0);

    	return $model->get();
    }

    public function placeResults()
    {
        $patients = Patient::whereNull('hiv_status')->get()->count();
        $data = [];
        foreach ($patients as $key => $patient) {
            $samples = Sample::where('patient_id', '=', $patient->id)->get();
            if ($samples->count() == 1){
                $sample = $samples->first();
                $patient->hiv_status = $sample->result;
                $patient->save();
            } else {
                foreach ($samples as $key => $sample) {
                    $data[$patient->id][] = ['datecollected'=>$sample->datecollected,'result'=>$sample->result];
                }
            }
        }
        dd($data);
        echo('Complete');
    }
}