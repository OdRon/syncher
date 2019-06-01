<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;
use Exception;

class Poc
{

	public static function alereq()
	{
		$client = new Client(['base_uri' => 'https://datapoint.alere.com/api/v1/measurements/alereq']);
		$response = $client->request('get', '', [
			'debug' => true,
			'auth' => [
				env('ALERE_USERNAME'), env('ALERE_PASSWORD')
			],
			'query' => [
				'StartDate' => date('Y-m-d', strtotime('-1 year')),
				'pagesize' => 1
			],
		]);
		$body = json_decode($response->getBody());
		dd($body);

		foreach ($body->Measurements as $key => $row) {
			$data = [
				'' => $row->Id,
				'' => $row->AssayName,
				'' => $row->SerialNumber,
				'' => $row->TestId,
				'' => $row->SampleId,
				'' => $row->MeasurementType,
				'' => $row->CartridgeId,
				'' => $row->ResultDate,
				'' => $row->DateAdded,
				'' => $row->LastUpdated,
				'' => $row->ErrorCode,
				'' => $row->Operator,
				'' => $row->HIVType1GroupMN,
				'' => $row->HIVType1GroupO,
				'' => $row->HIVType2,
				'' => $row->QualityControls->SampleDetection,
				'' => $row->QualityControls->Device,
				'' => $row->QualityControls->Hiv1PositiveControl,
				'' => $row->QualityControls->Hiv2PositiveControl,
				'' => $row->QualityControls->NegativeControl,
				'' => $row->QualityControls->Analysis,
			];
		}
	}
}
