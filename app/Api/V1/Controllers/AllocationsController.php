<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Allocation;
use App\AllocationDetail;
/**
 * 
 */
class AllocationsController extends Controller
{
	public function create(BlankRequest $request) {
		$allocations_array = [];
		$allocations = json_decode($request->input('allocations'));

		foreach ($allocations as $key => $allocation) {
			$existing = Allocation::existing($allocation->year, $allocation->month, $allocation->testtype, $allocation->machine_id)->with(['details'])->first();
			if ($existing){
				$allocations_array[] = ['original_id' => $allocation->id, 'national_id' => $existing->id, 'details' => $this->allocationDetails($allocation, $existing)];
				continue;
			}
			
			// New allocation to be saved
			$saveallocation = new Allocation();
            $saveallocation->machine_id = $allocation->machine_id;
            $saveallocation->testtype = $allocation->testtype;
            $saveallocation->year = $allocation->year;
            $saveallocation->month = $allocation->month;
            $saveallocation->datesubmitted = $allocation->datesubmitted;
            $saveallocation->submittedby = $allocation->submittedby;
            $saveallocation->lab_id = $allocation->lab_id;
            $saveallocation->allocationcomments = $allocation->allocationcomments;
            $saveallocation->issuedcomments = $allocation->issuedcomments;
            $saveallocation->approve = $allocation->approve;
            $saveallocation->disapprovereason = $allocation->disapprovereason;
            $saveallocation->original_id = $allocation->id;
            $saveallocation->synched = 1;
            $saveallocation->datesynched = date('Y-m-d');
            $saveallocation->save();
            $allocations_array[] = ['original_id' => $saveallocation->original_id, 'national_id' => $saveallocation->id, 'details' => $this->allocationDetails($allocation, null, $saveallocation) ];
		}
		return response()->json([
            'status' => 'ok',
            'allocations' => $allocations_array,
        ], 201);
	}

	protected function allocationDetails($allocation, $existing = null, $newallocation = null){
		$details_array = [];
		foreach ($allocation->details as $key => $detail) {
			if (isset($existing)) {
				foreach ($existing->details as $key => $existing_details) {
					if ($existing_details->original_id == $detail->id)
						$details_array[] = ['original_id' => $detail->id, 'national_id' => $existing_details->id];
					else 
						$details_array[] = $this->saveAllocationDetails($detail, $allocation);
				}
			} else {
				$details_array[] = $this->saveAllocationDetails($detail, $newallocation);;
			}			
		}
		return $details_array;
	}

	protected function saveAllocationDetails($details, $allocation) {
		$saveallocationdetails = new AllocationDetail();
		$allocation_details_data = get_object_vars($details);
		$saveallocationdetails->fill($allocation_details_data);
		$saveallocationdetails->allocation_id = $allocation->id;
		$saveallocationdetails->original_id = $details->id;
		$saveallocationdetails->synched = 1;
		$saveallocationdetails->datesynched = date('Y-m-d');
		unset($saveallocationdetails->id);
		unset($saveallocationdetails->national_id);
		$saveallocationdetails->save();

		return ['original_id' => $saveallocationdetails->original_id, 'national_id' => $saveallocationdetails->id];
	}
}
?>