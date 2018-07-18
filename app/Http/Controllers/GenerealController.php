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
    	session(['searchParams'=>['facility_id'=>$facility->id]]);
    	return view('tables.searchresults')->with('pageTitle', "$facility->name");
    }

    public function eidresults(Request $request) {
    	$recordsTotal = 0;
    	$recordsFiltered = 0;
    	$modelCount = null;
    	$model = self::results('eid', $modelCount, $recordsTotal);
    	$model = self::filter('eid',$model,$request,$modelCount,$recordsFiltered);
    	$model = self::order('eid',$model,$request);
    	$model = self::limit($model,$request);
    	$data = self::data_output($model,$request,$recordsTotal,$recordsFiltered);
    	echo json_encode($data);
    }

    public function vlresults(Request $request) {
    	$model = self::results('vl');
    	$model = self::filter('vl',$model,$request);
    	$model = self::order('vl',$model,$request);
    	$model = self::limit($model,$request);
    	// $vlsamples = 
    	// 					->orderBy('viralsample_complete_view.datetested', 'desc')
    	// 					->get();
    }

    public static function results($testingSystem,&$modelCount, &$Total) {
    	if ($testingSystem == 'eid') {
    		$model = SampleCompleteView::select('sample_complete_view.id','sample_complete_view.batch_id','sample_complete_view.patient_id', 'sample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','sample_complete_view.datecollected','sample_complete_view.datereceived','sample_complete_view.datedispatched','sample_complete_view.datetested','results.name as result','sample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'sample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'sample_complete_view.rejectedreason');
    		$modelCount = SampleCompleteView::selectRaw("count(*) as totals")
    						->leftJoin('labs', 'labs.id', '=', 'sample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'sample_complete_view.rejectedreason');
    	} else if ($testingSystem == 'vl') {
    		$model = ViralsampleCompleteView::select('viralsample_complete_view.id','viralsample_complete_view.batch_id','viralsample_complete_view.patient_id', 'viralsample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','viralsample_complete_view.datecollected','viralsample_complete_view.datereceived','viralsample_complete_view.datedispatched','viralsample_complete_view.datetested','results.name as result','viralsample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'viralsample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'viralsample_complete_view.rejectedreason');

    		$modelCount = ViralsampleCompleteView::selectRaw("count(*) as totals")
    						->leftJoin('labs', 'labs.id', '=', 'viralsample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'viralsample_complete_view.rejectedreason');
    	}
    	$Total = $modelCount->get()->first()->totals;

    	return $model;
    }

    public static function data_output($model,$request,$recordsTotal,$recordsFiltered){
    	$data = [];
    	$count = 1;
    	$dataSet = $model->get();
    	foreach ($dataSet as $key => $value) {
    		$data[] = [
    					$count, $value->patient,
    					$value->facility, $value->lab,
    					$value->batch_id, $value->receivedstatus_name,
    					$value->datecollected, $value->datereceived,
    					$value->datetested, $value->datedispatched,
    					$value->result, "Action"
    				];
    		$count++;
    	}
    	return array(
					"draw"            => isset ( $request['draw'] ) ?
						intval( $request['draw'] ) :
						0,
					"recordsTotal"    => intval( $recordsTotal ),
					"recordsFiltered" => intval( $recordsFiltered ),
					"data"            => $data
				);
    }

    public static function limit($model,$request) {
    	$offset = (int) $request['start'];
    	$limit = (int) $request['length'];
    	
    	if ( isset($start) && $length != -1 ) {
			$model = $model->offset($offset)->limit($limit);
		}

		return $model;
    }

    public static function order($testingSystem,$model,$request) {
    	if ($testingSystem == 'eid')
    		$table = "sample_complete_view";
    	if ($testingSystem == 'vl')
    		$table = "viralsample_complete_view";

    	$order = $request['order'] ?? null;
    	$dbcolumns = self::$columns;
		$dtColumns = self::pluck($dbcolumns,'dt');
		
    	if (isset($order) && count($order)) {
    		foreach ($order as $key => $value) {
    			$columnIdx = array_search( $value['column'], $dtColumns );
    			$dbcolumn = $dbcolumns[ $columnIdx ];
    			$column = $dbcolumn['db'];
    			$direction = $value['dir'];
    			$model = $model->orderBy("$table.$column",$direction);
    		}
    	}
    	return $model;
    }

    public static function filter($testingSystem,$model,$request,$modelCount,&$Total) {
    	$dbcolumns = self::$columns;
    	$requestColumns = $request['columns'];
    	$search = $request['search'] ?? null;
    	$searchstr = $search['value'] ?? null;
    	$dtColumns = self::pluck($dbcolumns,'dt');
    	$parameter = (object)session('searchParams');
		
		if ($testingSystem == 'eid')
    		$table = "sample_complete_view";
    	if ($testingSystem == 'vl')
    		$table = "viralsample_complete_view";
    	
    	$model = $model->when($parameter, function($query, $parameter) use ($table){
    						if($parameter->facility_id)
    							return $query->where("$table.facility_id", '=', $parameter->facility_id);
    					})
    					->where("$table.repeatt", '=', 0)
    					->where("$table.flag", '=', 1);
		$modelCount = $modelCount->when($parameter, function($query, $parameter) use ($table){
    						if($parameter->facility_id)
    							return $query->where("$table.facility_id", '=', $parameter->facility_id);
    					})
    					->where("$table.repeatt", '=', 0)
    					->where("$table.flag", '=', 1);    					
    	if (isset($search) && $search['value'] != '') {
    		$str = "%$searchstr%";
    		foreach ($requestColumns as $key => $value) {
    			$columnIdx = array_search( $value['data'], $dtColumns );
    			$dbcolumn = $dbcolumns[ $columnIdx ];
				$dbcol = [];
				if ($value['searchable'] == 'true'){
					$searchable = false;
					$searchable = in_array($value['data'], $dtColumns);
					if ($searchable) {
						$columnIdx = array_search( $value['data'], $dtColumns );
						$column = $dbcolumns[$columnIdx];
						$column = $column['db'];
						if ($column == 'facility'){
							$table = "view_facilitys";
							$column = "name";
						}
						if ($column == 'lab') {
							$table = "labs";
							$column = "name";
						}
						if($key == 1) {
							$model = $model->where("$table.$column", 'like', $str);
							$modelCount = $modelCount->where("$table.$column", 'like', $str);
						} else {
							$model = $model->orWhere("$table.$column", 'like', $str);
							$modelCount = $modelCount->orWhere("$table.$column", 'like', $str);
						}
						if ($testingSystem == 'eid')
				    		$table = "sample_complete_view";
				    	if ($testingSystem == 'vl')
				    		$table = "viralsample_complete_view";
					}
				}
    		}
    	}
    	$Total = $modelCount->get()->first()->totals;
    	
    	return $model;
    }

    static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
}