<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Allocation;
/**
 * 
 */
class AllocationsController extends Controller
{
	public function create(BlankRequest $request) {
		$allocations_array = [];
		$allocations = json_decode($request->input('allocations'));
		foreach ($allocations as $key => $allocation) {
			$existing = Allocation::existing($allocation->year, $allocation->month, $allocation->testtype, $allocation->kit_id)->first();
			if ($existing){
				$allocations_array[] = ['original_id' => $allocation->id, 'national_id' => $existing->id ];
				continue;
			}

			// New allocation to be saved
			$saveallocation = new Allocation();
			$allocations_data = get_object_vars($allocation);
            $saveallocation->fill($allocations_data);
            $saveallocation->original_id = $allocation->id;
            $saveallocation->synched = 1;
            $saveallocation->datesynched = date('Y-m-d');

            // Unset the ID so that it auto-increments and the national id because it does not exist at national
            unset($saveallocation->id);
            unset($saveallocation->national_id);
            $saveallocation->save();
            $allocations_array[] = ['original_id' => $saveallocation->original_id, 'national_id' => $saveallocation->id ];
		}
		return response()->json([
            'status' => 'ok',
            'allocations' => $allocations_array,
        ], 201);
	}	
}
?>