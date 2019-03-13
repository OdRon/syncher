<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Allocation;
use App\AllocationDetail;
use App\AllocationDetailsBreakdown;
/**
 * 
 */
class AllocationsController extends Controller
{
	public function create(BlankRequest $request) {
		$allocations_array = [];
		$allocations_data = json_decode($request->input('allocations'));
		foreach($allocations_data as $allocation) {
			$allocation_details = $allocation->details;
			unset($allocation->details);
			$saveallocation = Allocation::existing($allocation->year, $allocation->month, $allocation->lab_id)
									->with(['details', 'details.breakdown'])->first();
			if(empty($saveallocation)) { // If allocation was never synched synch it
				$saveallocation = new Allocation();
				$saveallocation->fill(get_object_vars($allocation));
				$saveallocation->original_allocation_id = $allocation->id;
				$saveallocation->synched = 1;
				$saveallocation->datesynched = date('Y-m-d');
				unset($saveallocation->id);
				unset($saveallocation->national_id);
				$saveallocation->save();
				
				$saveallocation = [
					'original_allocation_id' => $allocation->id,
					'id' => $saveallocation->id,
					'details' => $this->saveAllocationDetails($saveallocation, $allocation_details)
				];
			}
			
			$allocations_array[] = $saveallocation;
		}
		return response()->json([
            'status' => 'ok',
            'allocations' => $allocations_array,
        ], 201);
	}

	protected function saveAllocationDetails($allocation, $details) {
		$allocation_details_array = [];
		foreach($details as $allocation_details) {
			$allocation_details_breakdown = $allocation_details->breakdowns;
			unset($allocation_details->breakdowns);
			$saveallocationdetails = new AllocationDetail();
			$saveallocationdetails->fill(get_object_vars($allocation_details));
			$saveallocationdetails->original_allocation_detail_id = $allocation_details->id;
			$saveallocationdetails->allocation_id = $allocation->id;
			$saveallocationdetails->synched = 1;
			$saveallocationdetails->datesynched = date('Y-m-d');
			unset($saveallocationdetails->id);
			unset($saveallocationdetails->national_id);
			$saveallocationdetails->save();
			$allocation_details_array[] = [
						'original_allocation_detail_id' => $allocation_details->id,
						'id' => $saveallocationdetails->id,
						'breakdown' => $this->saveAllocationDetailBreakdown($saveallocationdetails, $allocation_details_breakdown)
					];
		}
		return $allocation_details_array;
	}

	protected function saveAllocationDetailBreakdown($allocation_details, $breakdown) {
		$allocation_details_breakdown_array = [];
		foreach($breakdown as $allocation_details_breakdown) {
			$saveallocationdetailsbreakdown = new AllocationDetailsBreakdown();
			$saveallocationdetailsbreakdown->fill(get_object_vars($allocation_details_breakdown));
			$saveallocationdetailsbreakdown->original_allocation_details_breakdown_id = $allocation_details_breakdown->id;
			$saveallocationdetailsbreakdown->allocation_detail_id = $allocation_details->id;
			$saveallocationdetailsbreakdown->synched = 1;
			$saveallocationdetailsbreakdown->datesynched = date('Y-m-d');
			unset($saveallocationdetailsbreakdown->id);
			unset($saveallocationdetailsbreakdown->national_id);
			$saveallocationdetailsbreakdown->save();
			$allocation_details_breakdown_array[] = [
						'original_allocation_details_breakdown_id' => $allocation_details_breakdown->id,
						'id' => $saveallocationdetailsbreakdown->id
					];
		}
		return $allocation_details_breakdown_array;
	}

	public function update(BlankRequest $request) {
		return $this->update_dash($request, Allocation::class, 'allocations', 'national_id', 'original_allocation_id');
		// $allocations = json_decode($request->input('allocations'));
		// // dd($allocations);
		// return response()->json([
		// 	'allocation' => json_decode($request->all()),
		// ]);
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
            $new_model->synched = 1;
			$new_model->datesynched = date('Y-m-d');
			$new_model->save();
			
			$models_array[] = [
				'original_allocation_id' => $new_model->$original_column, 
				$nat_column => $new_model->id, 
				'details' => $this->updateAllocationDetails($value->details, $new_model->details, $nat_column, 'original_allocation_detail_id')];
		}

        if(count($errors_array) == 0) $errors_array = null;

		return response()->json([
            'status' => 'ok',
            $input => $models_array,
			'errors_array' => $errors_array,
        ], 201);
    }

	protected function updateAllocationDetails($lab_allocation_details, $national_allocation_details, $nat_column, $original_column) {
		$return_data = [];
		foreach ($lab_allocation_details as $key => $lab_detail) {
			$nat_details = $national_allocation_details->where('id', $lab_detail->$nat_column)->first();
            // $nat_details->$original_column = $lab_detail->id;
			$nat_details->allocationcomments = $lab_detail->allocationcomments;
			$nat_details->approve = $lab_detail->approve;
			$nat_details->submissions = $lab_detail->submissions;
			// $nat_details->allocated = $lab_detail->allocated;
			$nat_details->synched = 1;
			$nat_details->datesynched = date('Y-m-d');
			$nat_details->save();
			$return_data[] = [
						$nat_column => $nat_details->id, 
						$original_column => $lab_detail->id, 
						'breakdowns' => $this->updateAllocationDetailsBreakdown($lab_detail->breakdowns, $nat_details->breakdowns, $nat_column, 'original_allocation_details_breakdown_id')
					];
		}
		return $return_data;
	}

	protected function updateAllocationDetailsBreakdown($lab_allocation_breakdown, $nat_allocation_breakdown, $nat_column, $original_column) {
		$return_data = [];
		foreach ($lab_allocation_breakdown as $eky => $breakdown) {
			$nat_breakdown = $nat_allocation_breakdown->where('id', $breakdown->$nat_column)->first();
			$nat_breakdown->allocated = $breakdown->allocated;
			$nat_breakdown->synched = 1;
			$nat_breakdown->datesynched = date('Y-m-d');
			$nat_breakdown->save();
			$return_data[] = [
					$nat_column => $nat_breakdown->id,
					$original_column => $breakdown->id,
				];
		}
		return $return_data;
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
}
?>