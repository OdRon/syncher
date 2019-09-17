<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;
use Exception;

class Poc
{

	public static function alereq_old()
	{
		$client = new Client(['base_uri' => 'https://datapoint.alere.com/api/v1/measurements/alereq']);
		$response = $client->request('get', '', [
			// 'debug' => true,
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

	public static function alereq()
	{
		$client = new Client(['base_uri' => 'https://dp3admintest.aleredatapoint.com/api/v1/results/GetByDateAdded']);
		// $client = new Client(['base_uri' => 'https://admin.datapoint.abbott/api/v1/results/GetByDateAdded']);

		$response = $client->request('get', '', [
			// 'debug' => true,
			// 'auth' => [
			// 	env('ALERE_USERNAME'), env('ALERE_PASSWORD')
			// ],
			'headers' => [
				'Authorization' => env('ALERE_KEY'),
			],
			'query' => [
				'StartDate' => date('Y-m-d', strtotime('-1 year')),
				'DeviceType' => 'DataLogger',
				'CartridgeTypes' => 'DetermineHiv',
				'pagesize' => 1
			],
		]);
		$body = json_decode($response->getBody());
		// dd($body);

		foreach ($body->Results as $key => $row) {
			$data = [
				str_random(15) => $row->Id,

				str_random(15) => $row->DeviceSerialNumber,
				str_random(15) => $row->DeviceType,
				str_random(15) => $row->DeviceTypeString,

				str_random(15) => $row->Operator,
				str_random(15) => $row->SiteName,
				str_random(15) => $row->TestId, //same
				str_random(15) => $row->Sample, //same

				str_random(15) => $row->SpecimenSource, 
				str_random(15) => $row->SpecimenSourceString, 


				str_random(15) => $row->IsEidSample, // boolean
				str_random(15) => $row->ResultValue, 				
				str_random(15) => $row->ResultDate,
				str_random(15) => $row->ResultDateString, 
				str_random(15) => $row->ResultValue, 
				str_random(15) => $row->ResultType, 
				str_random(15) => $row->ResultTypeString, 
				str_random(15) => $row->ResultStatus, 
				str_random(15) => $row->ResultStatusString, 


				str_random(15) => $row->SoftwareVersion, 
				str_random(15) => $row->Disease, 
				str_random(15) => $row->DiseaseString, 

				str_random(15) => $row->CartridgeType, 
				str_random(15) => $row->CartridgeTypeString, 
				str_random(15) => $row->CartridgeId, 
				str_random(15) => $row->CartridgeLot, 
				str_random(15) => $row->CartridgeLotNumberAndId, 
				str_random(15) => $row->CartridgeExpiryDate, 
				str_random(15) => $row->CartridgeExpiryDateString, 


				str_random(15) => $row->Level, 		
				str_random(15) => $row->Qc, 		
				str_random(15) => $row->QcType, 
				str_random(15) => $row->QcStatus,

				str_random(15) => $row->HasErrors, 
				str_random(15) => $row->ErrorValue, 

				str_random(15) => $row->IsSuppressed, 
				str_random(15) => $row->DateApproved, 
				str_random(15) => $row->ApprovedBy, 
				str_random(15) => $row->Latitude, 
				str_random(15) => $row->Longitude, 
				str_random(15) => $row->DateAddedString, 

				


				// '' => $row->SampleId,
				// '' => $row->MeasurementType,
				// '' => $row->CartridgeId,
				// '' => $row->DateAdded,
				// '' => $row->LastUpdated,
				// '' => $row->ErrorCode,
				// '' => $row->HIVType1GroupMN,
				// '' => $row->HIVType1GroupO,
				// '' => $row->HIVType2,
				// '' => $row->QualityControls->SampleDetection,
				// '' => $row->QualityControls->Device,
				// '' => $row->QualityControls->Hiv1PositiveControl,
				// '' => $row->QualityControls->Hiv2PositiveControl,
				// '' => $row->QualityControls->NegativeControl,
				// '' => $row->QualityControls->Analysis,
			];

			dd($data);
		}
	}
}
