<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\MlabRequest;

use \App\SampleView;
use \App\ViralsampleView;
use \App\GeneXpertTest;

class PocController extends Controller
{

	public function genexpert(MlabRequest $request)
	{
		$data = $request->only(['password', 'systemName', 'exportedDate', 'assay', 'assayVersion', 'sampleId', 'patientId', 'user', 'status', 'startTime', 'endTime', 'errorStatus', 'reagentLotId', 'cartridgeExpirationDate', 'cartridgeSerial', 'moduleName', 'moduleSerial', 'instrumentSerial', 'softwareVersion', 'resultId', 'resultIdinterpretation']);

		$row = GeneXpertTest::firstOrCreate($request->only(['startTime', 'endTime', 'sampleId']), $data);

	}


}