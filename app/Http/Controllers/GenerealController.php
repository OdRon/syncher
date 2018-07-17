<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\ViewFacility;
use App\SampleCompleteView;
use App\ViralsampleCompleteView;

class GenerealController extends Controller
{
	public static $columns = array(
				array( 'db' => 'patient','dt' => 1 ),
				array( 'db' => 'facility', 'dt' => 2 ),
				array( 'db' => 'lab', 'dt' => 3 ),
				array( 'db' => 'batch_id', 'dt' => 4 ),
				array( 'db' => 'receivedstatus_name', 'dt' => 5 ),
				array( 'db' => 'datecollected', 'dt' => 6),
				array( 'db' => 'datereceived', 'dt' => 7),
				array( 'db' => 'datetested', 'dt' => 8),
				array( 'db' => 'datedispatched', 'dt' => 9),
				array( 'db' => 'result', 'dt' => 10)
			);
	public function patientSearch(){

    }

    public function batchSearch(){

    }

    public function countySearch(Request $request)
    {
        $search = $request->input('search');
        $county = DB::table('countys')->select('id', 'name', 'letter as facilitycode')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $county;
    }

    public function facilitySearch(Request $request) {
    	$usertype = auth()->user()->user_type_id;
    	$level = auth()->user()->level;
    	$search = $request->input('search');

    	return ViewFacility::select('ID as id', 'name', 'facilitycode', 'county')
            	->whereRaw("(name like '%" . $search . "%' OR  facilitycode like '" . $search . "%')")
				->when($usertype, function($query) use ($usertype, $level){
                    if ($usertype == 2 || $usertype == 3)
                        return $query->where('partner_id', '=', $level);
                    if ($usertype == 4)
                        return $query->where('county_id', '=', $level);
                    if ($usertype == 5)
                        return $query->where('subcounty_id', '=', $level);
                    if ($usertype == 7)
                        return $query->where('partner_id', '=', $level);
                })->paginate(10);
    }


    public function facilityresult($facility) {
    	$facility = ViewFacility::where('id', '=', $facility)->get()->first();
    	session(['searchParams'=>['facility_id'=>$facility->id]);
    	return view('tables.searchresults')->with('pageTitle', "$facility->name");
    }

    public function eidresults(Request $request) {
    	$model = self::results();
   		print_r($request->all());
    }

    public function vlresults() {
    	$vlsamples = ViralsampleCompleteView::select('viralsample_complete_view.id','viralsample_complete_view.batch_id','viralsample_complete_view.patient_id', 'viralsample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','viralsample_complete_view.datecollected','viralsample_complete_view.datereceived','viralsample_complete_view.datedispatched','viralsample_complete_view.datetested','results.name as result','viralsample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'viralsample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'viralsample_complete_view.rejectedreason')
    						->where('viralsample_complete_view.facility_id', '=', $facility)
    						->where('viralsample_complete_view.repeatt', '=', 0)
    						->where('viralsample_complete_view.flag', '=', 1)
    						->orderBy('viralsample_complete_view.datetested', 'desc')
    						->get();
    }

    public function results() {
    	print_r(self::$columns);die();
    	$eidsamples = SampleCompleteView::select('sample_complete_view.id','sample_complete_view.batch_id','sample_complete_view.patient_id', 'sample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','sample_complete_view.datecollected','sample_complete_view.datereceived','sample_complete_view.datedispatched','sample_complete_view.datetested','results.name as result','sample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'sample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'sample_complete_view.rejectedreason')
    						->where('sample_complete_view.facility_id', '=', $facility)
    						->where('sample_complete_view.repeatt', '=', 0)
    						->where('sample_complete_view.flag', '=', 1)
    						->orderBy('sample_complete_view.datetested', 'desc')
    						->get();
    }
}
