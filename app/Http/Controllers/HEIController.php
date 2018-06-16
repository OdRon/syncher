<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\SampleView;

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
    	
    	if ($request->method() == 'POST')
    		dd($request->all());
    	
    	$data['hei_categories'] = DB::table('hei_categories')->get();
    	$data['hei_validation'] = DB::table('hei_validation')->get();
    	$data['samples'] = self::__getSamples($level,$year,$month);

    	$data = (object)$data;
    	$monthName = "";
    	if (null !== $month) 
    		$monthName = "- ".date("F", mktime(null, null, null, $month));

    	return view('hei.followup', compact('data'))->with('pageTitle', "HEI Folow Up:$year $monthName");
    }

    public static function __outcomes($year=null, $month=null)
    {
    	$positiveOutcomes = self::__getOutcomes(null,$year,$month);
    	$enrolled = self::__getOutcomes(1,$year,$month);
    	$ltfu = self::__getOutcomes(2,$year,$month);
    	$dead = self::__getOutcomes(3,$year,$month);
    	$transferOut = self::__getOutcomes(5,$year,$month);
    	$other = self::__getOutcomes(6,$year,$month);
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other));

    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
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
    	$unknown = ($positiveOutcomes - ($enrolled+$ltfu+$dead+$transferOut+$other));

    	return (object)['positiveOutcomes' => $positiveOutcomes,
		    			'enrolled' => $enrolled,
		    			'ltfu' => $ltfu,
		    			'dead' => $dead,
		    			'transferOut' => $transferOut,
		    			'other' => $other,
		    			'unknown' => $unknown
		    			];
    }

    public static function __getOutcomes($status,$year=null, $month=null)
    {
    	return SampleView::selectRaw("COUNT(*) as totalPositives")
					->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
					->where('samples_view.result', '=', 2)->where('samples_view.pcrtype', '=', 1)
					->where('samples_view.repeatt', '=', 0)->where('view_facilitys.partner_id', '=', auth()->user()->partner)
					->when($year, function($query) use ($year){
						return $query->whereRaw("YEAR(datetested) = $year");
					})
					->when($month, function($query) use ($month){
                        return $query->whereRaw("MONTH(datetested) = $month");
                    })->when($status, function($query) use ($status){
                        return $query->where('samples_view.enrollment_status', '=', $status);
                    })->get()->first()->totalPositives;
    }

    public static function __getSamples($level=null,$year=null,$month=null)
    {
    	$model = SampleView::join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
					->where('samples_view.result', '=', 2)->where('samples_view.pcrtype', '=', 1)
					->where('samples_view.repeatt', '=', 0)->where('view_facilitys.partner_id', '=', auth()->user()->partner);

		if ($level != 'cumulative')
			$model->when($year, function($query) use ($year){
					return $query->whereRaw("YEAR(datetested) = $year");
				})->when($month, function($query) use ($month){
	                return $query->whereRaw("MONTH(datetested) = $month");
	            })->where('samples_view.enrollment_status', '=', 0);

    	return $model->get();
    }
}
