<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Allocation;
use App\Machine;
use App\Lab;

class AllocationsController extends Controller
{
	/**
     * The test types available.
     *
     * @var array
     */
	public $testtypes = NULL;

	/**
     * The months for allocations.
     *
     * @var array
     */
	public $allocation_months = NULL;


	/**
     * The years for allocations.
     *
     * @var array
     */
	public $allocation_years = NULL;

	/**
     * The years for allocations.
     *
     * @var array
     */
	public $years = NULL;

	public function __construct() {
		$this->testtypes = ['EID' => 1, 'VL' => 2];
		$this->years = [date('Y'), date('Y')-1];
	}

    public function index($testtype = null) {
        $testtype = strtoupper($testtype);
    	if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL'))
    		$testtype = 'EID';
    	$labs = Lab::get();
    	$allocations = Allocation::where('testtype', '=', $this->testtypes[$testtype])
    							->whereIn('year', $this->years)
    							->orderBy('year', 'desc')->orderBy('month', 'desc')
    							->get();
    	$allocations_data = [];
    	$this->allocation_years = $allocations->unique('year')->pluck('year');
    	$this->allocation_months = $allocations->unique('month')->pluck('month');
    	
    	foreach ($this->allocation_years as $key => $year) {
    		foreach ($this->allocation_months as $key => $month) {
    			$filtered = $allocations->where('year', $year)->where('month', $month);
    			$allocated_labs = 0;
    			if ($filtered->count() > 0){
                    $allocated_labs = $filtered->unique('lab_id');
                    $approved_labs = $allocated_labs->where('approve', 1);
                }
    			
    			$allocations_data[] = (object)[
                    'testtype' => $testtype,
    				'year' => $year,
    				'month' => $month,
    				'all_labs' => $labs->count(),
    				'allocated_labs' => $allocated_labs->count(),
                    'approved_labs' => $approved_labs->count(),
    			];
    		}
    	}
    	$allocations_data = (object)$allocations_data;
    	
    	return view('tables.allocations', compact('allocations_data'))->with('pageTitle',"$testtype Allocation List");
    }

    public function view_allocations($testtype = null, $year = null, $month = null) {
        $testtype = strtoupper($testtype);
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL'))
            $testtype = 'EID';
        if (!isset($year))
            $year = $this->year[0];
        if (!isset($month))
            $month = date('m');
        $columntesttype = $this->testtypes[$testtype];
        $labs = Lab::with(array('allocations' => function($query) use($year, $month, $columntesttype) {
                         $query->where('allocations.year', $year);
                         $query->where('allocations.month', $month);
                         $query->where('allocations.testtype', $columntesttype);
                    }))->get();
        
        $month_name = date("F", mktime(null, null, null, $month));
        $data = (object)['year' => $year, 'month' => $month, 'labs' => $labs, 'testtype' => $testtype];
        
        return view('tables.viewallocations', compact('data'))->with('pageTitle',"$testtype Allocations $month_name, $year");
    }

    public function approve_allocations(Lab $lab, $testtype = null, $year = null, $month = null) {
        if(empty($lab)){
            session(['toast_message'=>'This lab does not exist', 'toast_error' => 1]);
            return back();
        }
        $testtype = strtoupper($testtype);
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL'))
            $testtype = 'EID';
        if (!isset($year))
            $year = $this->year[0];
        if (!isset($month))
            $month = date('m');

        $columntesttype = $this->testtypes[$testtype];
        $allocations = $lab->allocations->where('testtype', $columntesttype);
        $lab_name = $lab->labdesc;
        $month_name = date("F", mktime(null, null, null, $month));
        $data = (object)['allocations' => $allocations, 'testtype' => $testtype];
        
        return view('forms.allocations', compact('data'))->with('pageTitle',"$lab_name Allocation Approval ($month_name, $year)");
    }
}
