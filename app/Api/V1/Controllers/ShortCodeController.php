<?php
namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ShortCodeRequest;
use App\SampleCompleteView;
use App\ViralSampleCompleteView;
use App\Patient;
use App\ViralPatient;
use App\Facility;
/**
 * 
 */
class ShortCodeController extends Controller
{
	public function shortcode(ShortCodeRequest $request) {
		$message = $request->input('smsmessage');
		$phone = $request->input('smsphoneno');
		$messageBreakdown = $this->messageBreakdown($message);
		$patientTests = $this->getPatientData($messageBreakdown);
		return response()->json($patientTests);
	}

	private function messageBreakdown($message = null) {
		if (!$message)
			return null;
		$data['querytype'] = substr($message,0,1);
		$data['mflcode'] = substr($message,1,5);
		$querytypeplusmfl = substr($message,0,6);
		$data['sampleID'] = substr($message, ($pos = strpos($message, $querytypeplusmfl)) !== false ? $pos + 7 : 0);

		return (object) $data;
	}
	private function getPatientData($message = null){
		if(empty($message))
			return null;
		$facility = Facility::select('id', 'facilitycode')->where('facilitycode', '=', $message->mflcode)->first();
		$patient = Patient::where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get(); // EID patient
		$class = SampleCompleteView::class;
		$table = 'sample_complete_view';
		if ($patient->isEmpty()) { // Check if VL patient
			$patient = Viralpatient::where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get();
			$class = ViralSampleCompleteView::class;
			$table = 'viralsample_complete_view';
		}
		if ($patient->isEmpty())
			return null;
		return $this->getTestData($patient->first(), $class, $table);
	}

	private function getTestData($patient, $class, $table) {
		$samples = $class::join('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
							->where('patient_id', '=', $patient->id)
							->where('repeatt', '=', 0)
							->orderBy("$table.id", 'desc')
							->limit(5)->get();
		
		// foreach ($samples as $key => $sample) {
		// 	return get_class($sample);	
		// }
		return $samples;
	}
}

?>