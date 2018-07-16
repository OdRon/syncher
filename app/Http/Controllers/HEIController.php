<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\SampleView;
use App\SampleCompleteView;
use App\Sample;
use App\Patient;
use App\ViewFacility;

class HEIController extends Controller
{
    //
    public function index($year=null, $month = null)
    {
    	if ($year==null || $year=='null'){
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
        
    	$data['outcomes'] = self::__outcomes(session('followupYear'), session('followupMonth'));
        $data['cumulative'] = self::__cumulativeOutcomes();

    	$data = (object)$data;
        return view('hei.validate', compact('data'))->with('pageTitle','HEI Follow Up');
    }

    public function followup(Request $request,$duration='outcomes',$validation=null)
    {
        $year = session('followupYear');
    	$month = session('followupMonth');
        $data['edit'] = false;
    	
    	if ($request->method() == 'POST') {
            $data = [];
    		$columns = [ 'id', 'patient', 'hei_validation', 'enrollment_status', 'dateinitiatedontreatment', 'enrollment_ccc_no', 'facility_id', 'other_reason'];
    		$sampleCount = (int)$request->DataTables_Table_0_length ?? null;
            $actualCount = 0;
    		if (isset($sampleCount) || $sampleCount > 0) {
    			for ($i=$sampleCount; $i > 0 ; $i--) { 
    				foreach ($columns as $key => $value) {
                        $name = $value.$i;
                        if (isset($request->$name)) {
                            $actualCount++;
    					    $data[$i][$value] = $request->$name;
                        }
    				}
    			}
                
    			$save = $this->saveHeis($data);
    			session(['toast_message'=>'Follow up for the '.$actualCount.' patients complete']);
    			
                return redirect('hei/followup');
    		}
    	}
    	
    	$data['hei_categories'] = DB::table('hei_categories')->get();
    	$data['hei_validation'] = DB::table('hei_validation')->get();
    	$data['patients'] = self::__getPatients($year,$month,$duration,$validation);
        if (isset($validation))
            $data['edit'] = true;

        if ($data['edit']) {
            foreach ($data['patients'] as $key => $value) {
                $data['facilitys'][] = ViewFacility::where('id','=',$value->referredfromsite)->get()->first() ?? null;
            }
        }
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
    		$patient = Patient::where('id', '=', $value->id)->get()->first();
            $patient->hei_validation = $value->hei_validation;
    		if ($value->hei_validation == 1) {
    			$patient->enrollment_status = $value->enrollment_status;
    			if ($value->enrollment_status == 1) {
    				$patient->ccc_no = $value->enrollment_ccc_no;
    				$patient->dateinitiatedontreatment = $value->dateinitiatedontreatment;
    				$patient->enrollment_ccc_no = $value->enrollment_ccc_no;
    			} else if ($value->enrollment_status == 5) {
    				$patient->facility_id = $value->facility_id;
    				$patient->referredfromsite = $value->facility_id;
    			} else if ($value->enrollment_status == 6) {
    				$patient->otherreason = $value->other_reason;
    			}
    		}
    		$patient->save();
    	}
    	return true;
    }
// 191836
    public static function __outcomes($year=null, $month=null)
    {
    	$positiveOutcomes = self::__getOutcomes(null,null,$year,$month);
    	$enrolled = self::__getOutcomes(1,null,$year,$month);
    	$ltfu = self::__getOutcomes(2,null,$year,$month);
    	$dead = self::__getOutcomes(3,null,$year,$month);
        $adult = self::__getOutcomes(null,2,$year,$month);
    	$transferOut = self::__getOutcomes(5,null,$year,$month);
    	$other = self::__getOutcomes(6,null,$year,$month);
    	$othervalidation = self::__getOutcomes('others',null);
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other+$adult));
        
    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
                        'adult' => $adult,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
		    			'othervalidation' => $othervalidation,
		    			'unknown' => $unknown
		    			];
    }

    public static function __cumulativeOutcomes()
    {
    	$positiveOutcomes = self::__getOutcomes(null,null);
    	$enrolled = self::__getOutcomes(1,null);
    	$ltfu = self::__getOutcomes(2,null);
    	$dead = self::__getOutcomes(3,null);
        $adult = self::__getOutcomes(null,2);
    	$transferOut = self::__getOutcomes(5,null);
    	$other = self::__getOutcomes(6,null);
    	$othervalidation = self::__getOutcomes('others',null);
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other+$adult));
        
    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
                        'adult' => $adult,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
		    			'othervalidation' => $othervalidation,
		    			'unknown' => $unknown
		    			];
    }

    public static function __getOutcomes($status,$validate,$year=null, $month=null)
    {
        $usertype = auth()->user()->user_type_id;
    	return SampleCompleteView::selectRaw("COUNT(distinct sample_complete_view.patient_id) as totalPositives")
					->join('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
					->where('sample_complete_view.repeatt', '=', 0)
                    ->whereIn('sample_complete_view.pcrtype', [1,2,3])
                    ->where('sample_complete_view.result', '=', 2)
                    ->when($year, function($query) use ($year){
                        return $query->whereRaw("YEAR(sample_complete_view.datetested) = $year");
                    })
                    ->when($month, function($query) use ($month){
                        return $query->whereRaw("MONTH(sample_complete_view.datetested) = $month");
                    })
                    ->when($usertype, function($query) use ($usertype){
                        if($usertype == 3)
                            return $query->where('view_facilitys.partner_id', '=', auth()->user()->level);
                        if ($usertype == 4) 
                            return $query->where('view_facilitys.county_id', '=', auth()->user()->level);
                        if ($usertype == 5) 
                            return $query->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                        if ($usertype == 8) 
                            return $query->where('view_facilitys.id', '=', auth()->user()->facility_id);
                    })
                    ->when($validate, function($query) use ($validate){
                        return $query->where('sample_complete_view.hei_validation', '=', $validate);
                    })
					->when($status, function($query) use ($status){
                    	if ($status == 'others') {
                    		return $query->where('sample_complete_view.hei_validation', '<>', '0')->where('sample_complete_view.hei_validation', '<>', '1');
                    	} else {
                        	return $query->where('sample_complete_view.enrollment_status', '=', $status);
                        }
                    })->get()->first()->totalPositives;
    }

    public static function __getPatients($year=null,$month=null,$duration=null,$validation=null)
    {
        if(!($duration == 'outcomes' || $duration || 'cumulative' || $duration == null))
            return back();
        
    	$usertype = auth()->user()->user_type_id;
        $model = SampleCompleteView::selectRaw("distinct sample_complete_view.patient_id, sample_complete_view.patient, sample_complete_view.original_patient_id, sample_complete_view.ccc_no, sample_complete_view.patient, sample_complete_view.gender_description, sample_complete_view.dob, sample_complete_view.dateinitiatedontreatment, sample_complete_view.hei_validation, sample_complete_view.enrollment_ccc_no, sample_complete_view.enrollment_status, sample_complete_view.referredfromsite, sample_complete_view.otherreason, view_facilitys.name as facility, view_facilitys.county, view_facilitys.facilitycode")
    				->join('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
                    ->where('sample_complete_view.repeatt', '=', 0)
                    ->whereIn('sample_complete_view.pcrtype', [1,2,3])
                    ->where('sample_complete_view.result', '=', 2)
                    ->when($usertype, function($query) use ($usertype){
                        if($usertype == 3)
                            return $query->where('view_facilitys.partner_id', '=', auth()->user()->level);
                        if ($usertype == 4) 
                            return $query->where('view_facilitys.county_id', '=', auth()->user()->level);
                        if ($usertype == 5) 
                            return $query->where('view_facilitys.subcounty_id', '=', auth()->user()->level);
                        if ($usertype == 8) 
                            return $query->where('view_facilitys.id', '=', auth()->user()->facility_id);
                    });

        if ($duration == 'outcomes') 
            $model = $model->when($year, function($query) use ($year){
                        return $query->whereRaw("YEAR(sample_complete_view.datetested) = $year");
                    })
                    ->when($month, function($query) use ($month){
                        return $query->whereRaw("MONTH(sample_complete_view.datetested) = $month");
                    });
        

        if(isset($validation)) {
            $model = $model->when($validation, function($query) use  ($validation){
                            if (strtolower($validation) == 'positives') {}

                            if (strtolower($validation) == 'enrolled')
                                return $query->where('sample_complete_view.enrollment_status', '=', 1);
                            if (strtolower($validation) == 'ltfu')
                                return $query->where('sample_complete_view.enrollment_status', '=', 2);
                            if (strtolower($validation) == 'dead')
                                return $query->where('sample_complete_view.enrollment_status', '=', 3);
                            if (strtolower($validation) == 'adult')
                                return $query->where('sample_complete_view.hei_validation', '=', 2);
                            if (strtolower($validation) == 'transferout')
                                return $query->where('sample_complete_view.enrollment_status', '=', 5);
                            if (strtolower($validation) == 'transferout')
                                return $query->where('sample_complete_view.enrollment_status', '=', 5);
                            if (strtolower($validation) == 'other')
                                return $query->where('sample_complete_view.enrollment_status', '=', 6);
                        });
        } else {
            $model = $model->where('sample_complete_view.hei_validation', '=', 0)
                    ->orWhereNull('sample_complete_view.hei_validation');
        }
        return $model->get();
    }
}