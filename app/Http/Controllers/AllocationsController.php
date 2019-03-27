<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Allocation;
use App\AllocationDetail;
use App\AllocationDetailsBreakdown;
use App\Exports\AllocationDrfExport;
use App\Machine;
use App\Lab;
use App\GeneralConsumables;
use App\Kits;

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
     * The years for allocations displayed.
     *
     * @var array
     */
	public $years = NULL;

    /**
     * The NHRL or EDARP user initialized.
     *
     * @var array
     */
    public $lab_id = NULL;

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

    public function drf(Lab $lab) {
        if (!isset($lab->id)) {
            $year = date('Y');
            $month = date('m');
            $labs = Lab::with(array('allocations' => function($query) use($year, $month) {
                            $query->where('allocations.year', $year);
                            $query->where('allocations.month', $month);
                        }, 'allocations.details'))->get();

            $monthname = date('F', mktime(null, null, null, $month));
            return view('tables.allocationdrf', compact('labs'))->with('pageTitle', "Distribution Request Form $year - $monthname");
        } else {
            $allocation = $lab->allocations->where('year', date('Y'))->where('month', date('m'))->first();
            return (new AllocationDrfExport($allocation))->download('DRF.xlsx');
        }        
    }

    public function lab_allocation() {
        $this->initialize_lab_id();

    }

    public function national_allocation(Request $request) {
        $this->initialize_lab_id();
        $lab = Lab::find($this->lab_id);
        if ($request->method() == 'GET' && $this->check_submitted_allocation()){
            $machines = Machine::get();
            return view('tasks.allocation', compact('machines'))->with('pageTitle', $lab->labdesc . ' Allocation::'.date("F", mktime(null, null, null, date('m'))).', '.date('Y'));
        } else if ($request->method() == 'POST') {
            if ($request->has(['machine-form'])){ // This is to fill the allocation form for the previously slected machines
                $testtypes = collect($this->testtypes)->except(['CONSUMABLES']);
                $machines = Machine::whereIn('id',$request->input('machine'))->get();
                $generalconsumables = GeneralConsumables::get();
                $data['machines'] = $machines;
                $data['testtypes'] = $testtypes;
                $data['generalconsumables'] = $generalconsumables;
                $data['lab_id'] = $this->lab_id;
                $data = (object) $data;
                
                return view('forms.nationalallocation', compact('data'))->with('pageTitle',  $lab->labdesc . ' Allocation::'.date("F", mktime(null, null, null, date('m'))).', '.date('Y'));
            } else { // Save the allocations from the previous if section
                $saveAllocation = $this->saveAllocation($request);
                return redirect()->route('lab/allocation');
            }
        }
    }

    private function saveAllocation($request) {
        $this->initialize_lab_id();
        $form = $request->except(['_token', 'kits-form']);
        $allocation = Allocation::create([
                        'year' => date('Y'),
                        'month' => date('m'),
                        'datesubmitted' => date('Y-m-d'),
                        'submittedby' => auth()->user()->full_name,
                        'lab_id' => $this->lab_id,
                    ]);
        $allocation_details = $this->saveAllocationDetails($allocation, $form);
        
        return $allocation;
    }

    private function saveAllocationDetails($allocation, $form_data) {
        foreach ($form_data as $key => $datum) {
            $column = explode('-', $key);
            $build = false;
            $machine_id = NULL;
            $testtype = NULL;
            if ($column[0] == 'allocation') { // Create a new allocation at this point
                $machine_id = $column[1];
                $testtype = $column[2];
                $allocationcomments = 'allocationcomments-'.$machine_id.'-'.$testtype;
                $build = true;
            } else if ($key == 'consumablecomments'){
                $allocationcomments = 'consumablecomments';
                $build = true;
            }
            if ($build) {
                $allocation_details = AllocationDetail::create([
                    'allocation_id' => $allocation->id,
                    'machine_id' => $machine_id,
                    'testtype' => $testtype,
                    'allocationcomments' => $form_data[$allocationcomments]]);
                $allocationDetailsBreakdown = $this->getAllocationDetailsBreakdownData($allocation_details, $machine_id, $testtype, $form_data);
            }
        }
        return $allocation_details;
    }

    // Format the Allocation Details data to fit laravel way to insert
    private function getAllocationDetailsBreakdownData($allocation_details, $machine, $testtype, $form_data) {
        if (!$machine)
            $this->getConsumableAllocationData($allocation_details, $form_data);
        else {
            $kits = Kits::where('machine_id', '=', $machine)->get();
            $allocation_details_array = [];
            foreach ($kits as $key => $kit) {
                foreach ($form_data as $formkey => $form) {
                    $column = 'allocate-'.$testtype.'-'.$kit->id;
                    if ($column == $formkey){
                        $allocation_detail = AllocationDetailsBreakdown::create([
                            'allocation_detail_id' => $allocation_details->id,
                            'breakdown_id' => $kit->id,
                            'breakdown_type' => Kits::class,
                            'allocated' => $form
                        ]);
                    }
                }
            }
        }
        
        return true;
    }

    // Format the consumable allocation data to fit laravel way to insert
    private function getConsumableAllocationData($allocation_detail, $form_data) {
        $consumables = GeneralConsumables::get();
        $consumables_array = [];
        foreach($consumables as $key => $consumable) {
            $column = 'consumable-'.$consumable->id;
            $allocation_details = AllocationDetailsBreakdown::create([
                'allocation_detail_id' => $allocation_detail->id,
                'breakdown_id' => $consumable->id,
                'breakdown_type' => GeneralConsumables::class,
                'allocated' => $form_data[$column]
            ]);
        }
        return true;
    }

    private function check_submitted_allocation() {
        return  Allocation::where('lab_id', '=', $this->lab_id)->where('year', '=', date('Y'))
                                        ->where('month', '=', date('m'))->get()->isEmpty();
    }

    private function initialize_lab_id(){
        if(auth()->user()->user_type_id == 14) // NHRL national commodities user
            $this->lab_id = 7;
        else if (auth()->user()->user_type_id == 15) // EDARP national commodities user
            $this->lab_id = 10;
    }
}
