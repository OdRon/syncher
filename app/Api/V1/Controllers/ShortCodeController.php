<?php
namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ShortCodeRequest;
use App\SampleCompleteView;
use App\ViralSampleCompleteView;
use App\Patient;
use App\ViralPatient;
use App\Facility;
use App\ShortCodeQueries;
use App\Http\Controllers\GenerealController;
/**
 * 
 */
class ShortCodeController extends Controller
{
	public function shortcode(ShortCodeRequest $request) {
		$message = $request->input('smsmessage');
		$phone = $request->input('smsphoneno');
		$patient = null;
		$facility = null;
		$testtype = null;
		$status = 1;
		$messageBreakdown = $this->messageBreakdown($message);
		$patientTests = $this->getPatientData($messageBreakdown, $patient, $facility);
		$textMsg = $this->buildTextMessage($patientTests, $status, $testtype);
		$sendTextMsg = $this->sendTextMessage($textMsg, $patient, $facility, $status, $message, $phone, $testtype);
		return response()->json($sendTextMsg);
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
	private function getPatientData($message = null, &$patient, &$facility){
		if(empty($message))
			return null;
		$facility = Facility::select('id', 'facilitycode')->where('facilitycode', '=', $message->mflcode)->first();
		$patient = Patient::select('id', 'patient')->where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get(); // EID patient
		$class = SampleCompleteView::class;
		$table = 'sample_complete_view';
		if ($patient->isEmpty()) { // Check if VL patient
			$patient = Viralpatient::select('id', 'patient')->where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get();
			$class = ViralSampleCompleteView::class;
			$table = 'viralsample_complete_view';
		}
		if ($patient->isEmpty())
			return null;
		return $this->getTestData($patient->first(), $class, $table);
	}

	private function getTestData($patient, $class, $table) {
		$select = "$table.*, view_facilitys.name as facility, view_facilitys.facilitycode, labs.labdesc as lab";
		return $class::selectRaw($select)
						->join('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
						->join('labs', 'labs.id', '=', "$table.lab_id")
						->where('patient_id', '=', $patient->id)
						->where('repeatt', '=', 0)
						->orderBy("$table.id", 'desc')
						->limit(5)->get();
	}

	private function buildTextMessage($tests = null, &$status, &$testtype){
		$msg = '';
		$inprocessmsg="Sample Still In process at the ";
		$inprocessmsg2=" The Result will be automatically sent to your number as soon as it is Available.";
		if (empty($tests))
			return $msg;
		foreach ($tests as $key => $test) {
			$testtype = (get_class($test) == 'App\ViralSampleCompleteView') ? 2 : 1;
			$msg .= "Facility: " . $test->facility . " [ " . $test->facilitycode . " ]\n";
			$msg .= (get_class($test) == 'App\ViralSampleCompleteView') ? "CCC #: " : "HEI #:";
			$msg .= $test->patient . "\n";
			$msg .= "Batch #: " . $test->original_batch_id . "\n";
			$msg .= "Date Drawn: " . $test->datecollected . "\n";
			if ($test->receivedstatus != 2) {
				if ($test->result){
					$msg .= "Date Tested: " . $test->datetested . "\n";
				} else{
					$msg .= $inprocessmsg . "\n";
					$status = 0;
				}
				if (isset($test->result) && get_class($test) == 'App\ViralSampleCompleteView')
					$msg .= "VL Result: " . $test->result . "\n";
				else if (isset($test->result) && get_class($test) == 'App\SampleCompleteView')
					$msg .= "EID Result: " . $test->result_name . "\n";
			} else {
				$msg .= (get_class($test) == 'App\ViralSampleCompleteView') ? " VL" : " EID";
				$msg .= " Rejected Sample: " . $test->rejected_reason->name . " - Collect New Sample.\n";
			}

			$msg .= "Lab Tested In: " . $test->lab;
			$msg .= (!$test->result && $test->receivedstatus != 2) ? "\n" . $inprocessmsg2 : "";
		}
		return $msg;
	}

	private function sendTextMessage($msg, $patient = null, $facility = null, $status, $receivedMsg, $phone, $testtype) {
		if (empty($patient))
			$msg = "The Patient Idenfier Provided Does not Exist in the Lab. Kindly confirm you have the correct one as on the Sample Request Form. Thanks.";
		date_default_timezone_set('Africa/Nairobi');
        $dateresponded = date('Y-m-d H:i:s');
		$responceCode = GenerealController::__sendMessage($phone, $msg);
		$shortcode = new ShortCodeQueries;
		$shortcode->testtype = $testtype;
		$shortcode->phoneno = $phone;
		$shortcode->message = $receivedMsg;
		$shortcode->facility_id = $facility->id ?? null;
		$shortcode->patient_id = $patient->first()->id ?? null;
		$shortcode->datereceived = $dateresponded;
		$shortcode->status = $status;
		if ($responceCode =='201')
			$shortcode->dateresponded = $dateresponded;
		$shortcode->save();
		return $shortcode;
	}
}

?>