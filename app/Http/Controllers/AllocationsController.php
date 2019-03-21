<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Allocation;
use App\AllocationDetail;
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
     * The last month of consumption.
     *
     * @var array
     */
    public $last_month = NULL;


	/**
     * The years for allocations.
     *
     * @var array
     */
	public $allocation_years = NULL;

    /**
     * The last year of consumption.
     *
     * @var array
     */
    public $last_year = NULL;

	/**
     * The years for allocations.
     *
     * @var array
     */
	public $years = NULL;

	public function __construct() {
		$this->testtypes = ['EID' => 1, 'VL' => 2, 'CONSUMABLES' => NULL];
		$this->years = [date('Y'), date('Y')-1];
        $this->last_month = date('m')-1;
        $this->last_year = date('Y');
        if (date('m') == 1) {
            $this->last_year -= 1;
            $this->last_month = 12;
        }
	}

    public function index($testtype = null) {
        $testtype = strtoupper($testtype);
    	if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL' || $testtype == 'CONSUMABLES'))
            $testtype = 'EID';
        
    	$labs = Lab::get();
    	$allocations_data = [];
        $allocations = Allocation::whereIn('year', $this->years)->get();
        $this->allocation_years = $allocations->unique('year')->pluck('year');
        $this->allocation_months = $allocations->unique('month')->pluck('month');
        foreach ($this->allocation_years as $key => $year) {
            foreach ($this->allocation_months as $key => $month) {
                $filtered = $allocations->where('year', $year)->where('month', $month);
                $allocated_labs = $filtered->count();
                $reviewed_labs = 0;
                foreach ($filtered as $lab_allocation) {
                    if ($lab_allocation->reviewed($testtype))
                        $reviewed_labs ++;
                }
                $allocations_data[] = (object)[
                        'testtype' => strtolower($testtype),
                        'year' => $year,
                        'month' => $month,
                        'all_labs' => $labs->count(),
                        'allocated_labs' => $allocated_labs,
                        'approved_labs' => $reviewed_labs,
                    ];
            }
        }
    	
    	return view('tables.allocations', compact('allocations_data'))->with('pageTitle',"$testtype Allocation List");
    }

    public function view_allocations($testtype = null, $year = null, $month = null) {
        $testtype = strtoupper($testtype);
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL' || $testtype == 'CONSUMABLES'))
            $testtype = 'EID';
        if (!isset($year))
            $year = $this->year[0];
        if (!isset($month))
            $month = date('m');
        $columntesttype = $this->testtypes[$testtype];
        $labs = Lab::with(array('allocations' => function($query) use($year, $month) {
                        $query->where('allocations.year', $year);
                        $query->where('allocations.month', $month);
                    }, 'allocations.details' => function($childQuery) use ($columntesttype) {
                            $childQuery->where('testtype', $columntesttype);
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
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL' || $testtype == 'CONSUMABLES'))
            $testtype = 'EID';
        if (!isset($year))
            $year = $this->year[0];
        if (!isset($month))
            $month = date('m');
            
        $columntesttype = $this->testtypes[$testtype];
        $allocation = $lab->allocations->where('year', $year)->where('month', $month)->first();
        $allocations = $allocation->load(array('details' => function($query) use ($columntesttype) {
                $query->where('testtype', '=', $columntesttype);                    
            }))->details;
        
        $lab_name = $lab->labdesc;
        $forapproval = $allocations->contains('approve', 0);
        $month_name = date("F", mktime(null, null, null, $month));
        $data = (object)['allocations' => $allocations, 'testtype' => $testtype, 'last_year' => $this->last_year, 'last_month' => $this->last_month, 'lab' => $lab, 'forapproval' => $forapproval];
        // dd($data);
        return view('forms.allocations', compact('data'))->with('pageTitle',"$lab_name Allocation Approval ($month_name, $year)");
    }

    public function save_allocation_approval(Request $request) {
        $collection = collect($request->except(['_token', 'allocation-form']));
        foreach ($collection['id'] as $key => $value) {
            if (isset($collection['approve'][$key])) {
                $allocation = AllocationDetail::find($value);
                $allocation->approve = $collection['approve'][$key];
                if ($collection['approve'][$key] == 2)
                    $allocation->disapprovereason = $collection['issuedcomments'][$key];
                $allocation->issuedcomments = $collection['issuedcomments'][$key];
                $allocation->synched = 2;
                $allocation->update();

                $parent = $allocation->allocation;
                $parent->synched = 2;
                $parent->update();

                $children = $allocation->breakdowns;
                foreach($children as $child){
                    $child->synched = 2;
                    $child->update();
                }
            }
        }
        $testtype = collect($this->testtypes)->search($allocation->testtype);
        $url = 'allocations/'.$testtype;
        session(['toast_message' => 'Allocation Review successfull for '. $testtype .' and the approvals propagated to the lab']);
        \App\Synch::synch_allocations();
        return redirect($url);
    }

    public function drf() {
        
    }
}
