<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Deliveries;
/**
 * 
 */
class DeliveriesController extends Controller
{
	public function create(BlankRequest $request){
		$deliveries_array = [];
		$deliveries = json_decode($request->input('deliveries'));
		foreach ($deliveries as $key => $delivery) {
			$existing = Deliveries::existing($delivery->year, $delivery->month, $delivery->testtype, $delivery->kit_id)->first();
			if ($existing){
				$deliveries_array[] = ['original_id' => $delivery->id, 'national_id' => $existing->id ];
				continue;
			}

			// New allocation to be saved
			$saveallocation = new Allocation();
			$allocations_data = get_object_vars($delivery);
            $saveallocation->fill($allocations_data);
            $saveallocation->original_id = $delivery->id;
            $saveallocation->synched = 1;
            $saveallocation->datesynched = date('Y-m-d');

            // Unset the ID so that it auto-increments and the national id because it does not exist at national
            unset($saveallocation->id);
            unset($saveallocation->national_id);
            $saveallocation->save();
            $deliveries_array[] = ['original_id' => $saveallocation->original_id, 'national_id' => $saveallocation->id ];
		}
		return response()->json([
            'status' => 'ok',
            'deliveries' => $deliveries_array,
        ], 201);
	}
}
?>