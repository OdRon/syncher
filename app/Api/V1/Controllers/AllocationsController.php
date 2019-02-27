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
            $saveallocation->original_allocation_id = $allocation->id;
            $saveallocation->synched = 1;
            $saveallocation->datesynched = date('Y-m-d');
            $saveallocation->save();
            $allocations_array[] = ['original_id' => $saveallocation->original_allocation_id, 'national_id' => $saveallocation->id, 'details' => $this->allocationDetails($allocation, null, $saveallocation) ];
		}
		return response()->json([
            'status' => 'ok',
            'allocations' => $allocations_array,
        ], 201);
	}

	public function update(BlankRequest $request) {
		
		// dd($request->all());
		// return $this->update_dash($request, Allocation::class, 'allocations', 'national_id', 'original_allocation_id');
		// $allocations = json_decode($request->input('allocations'));
		// // dd($allocations);
		return response()->json([
			'allocation' => $request->all(),
		]);
	}
	
	protected function update_dash($request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $errors_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));
		
        foreach ($models as $key => $value) {
            if($value->$nat_column)
                $new_model = $update_class::find($value->$nat_column);
            else
                $new_model = $update_class::locate($value)->get()->first();

            if(!$new_model){
                $errors_array[] = $value;
                continue;
            }
			
			$update_data = $value;
            $new_model->$original_column = $value->id;
			$new_model->allocationcomments = $update_data->allocationcomments;
			$new_model->approve = $update_data->approve;
			$new_model->submissions = $update_data->submissions;
            $new_model->synched = 1;
			$new_model->save();
			
			$models_array[] = ['original_id' => $new_model->$original_column, $nat_column => $new_model->id, 'details' => $this->updateAllocationDetails($value->details, $new_model->details, $nat_column, 'original_allocation_detail_id')];
		}

        if(count($errors_array) == 0) $errors_array = null;

		return response()->json([
            'status' => 'ok',
            $input => $models_array,
			'errors_array' => $errors_array,
        ], 201);
    }

	protected function allocationDetails($allocation, $existing = null, $newallocation = null){
		$details_array = [];
		foreach ($allocation->details as $key => $detail) {
			if (isset($existing)) {
				foreach ($existing->details as $key => $existing_details) {
					if ($existing_details->original_allocation_detail_id == $detail->id)
						$details_array[] = ['original_allocation_detail_id' => $detail->id, 'national_id' => $existing_details->id];
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
		$saveallocationdetails->original_allocation_detail_id = $details->id;
		$saveallocationdetails->synched = 1;
		$saveallocationdetails->datesynched = date('Y-m-d');
		unset($saveallocationdetails->id);
		unset($saveallocationdetails->national_id);
		$saveallocationdetails->save();

		return ['original_id' => $saveallocationdetails->original_allocation_detail_id, 'national_id' => $saveallocationdetails->id];
	}

	protected function updateAllocationDetails($lab_allocation_details, $national_allocation_details, $nat_column, $original_column) {
		$return_data = [];
		foreach ($lab_allocation_details as $key => $lab_detail) {
			$nat_details = $national_allocation_details->where('id', $lab_detail->$nat_column)->first();
			$nat_details->allocated = $lab_detail->allocated;
			$nat_details->synched = 1;
			$nat_details->datesynched = date('Y-m-d');
			$nat_details->save();
			$return_data[] = ['national_id' => $nat_details->id, 'original_id' => $lab_detail->id, 'synched' => $nat_details->synched, 'datesynched' => $nat_details->datesynched];
		}
		return $return_data;
	}
}
?>