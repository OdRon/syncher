<!DOCTYPE html>
<html>
<head>
	<title>Print Individual Result</title>
	<style type="text/css">
		table {
			border-collapse: collapse;
			width: 100%;
			margin-bottom: 1em;
		}

		table, th, td {
			border: 1px solid black;
			border-style: solid;
     		font-size: 12px;
		}

		h5 {
			margin-top: 6px;
		    margin-bottom: 6px;
		    font-weight: bolder;
		}

		.notice {
			margin-top: 2px;
			margin-bottom: 2px;
     		/*font-size: 8px;*/
     		font-weight: bold;
		}

		p {
			font-size: 12px;
		}
	</style>
</head>
<body>
	@foreach($samples as $sample)
	<div>
		<table>
			<tr>
				<td colspan="3">
					<center>
					<img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP">
					</center>
				</td>
			</tr>
			<tr>
				<th colspan="3">
					<center>
					<h5>MINISTRY OF HEALTH</h5>
					<h5>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h5>
					<h5>EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM</h5>
					</center>
				</th>
			</tr>
			<tr>
				<th>Batch No. : {{ $sample->original_batch_id ?? '' }} </th>
				<th>{{ $sample->facility->name ?? '' }}</th>
				<th>Testing Lab : {{ $sample->lab->name }}</th>
			</tr>
		</table>
		@if($testSysm == 'EID')
			<table class="table-bordered">
				<thead>
					<tr>
						<th colspan="2">DNA PCR Details</th>
						<th colspan="2">Mother Information</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Sample Code</th>
						<td>{{ $sample->patient ?? '' }}</td>
						<th>HIV Status</th>
						<td>{{ $sample->mother_last_result ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Collected</th>
						<td>{{ ($sample->datecollected) ? date('d-M-Y', strtotime($sample->datecollected)) : '' }}</td>
						<th>PMTCT Intervention</th>
						<td>{{ $sample->mother_prophylaxis_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Received</th>
						<td>{{ ($sample->datereceived) ? date('d-M-Y', strtotime($sample->datereceived)) : '' }}</td>
						<th>Infant Prophylaxis</th>
						<td>{{ $sample->regimen_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Tested</th>
						<td>{{ ($sample->datetested) ? date('d-M-Y', strtotime($sample->datetested)) : '' }}</td>
						<th>Infant Feeding</th>
						<td>{{ $sample->feeding_description ?? '' }}</td>
					</tr>
					<tr>
						<th>Age (Months)</th>
						<td>{{ $sample->age }}</td>
						<th>Entry Point</th>
						<td>
							@foreach($entry_points as $entry_point)
								@if($entry_point->id == $sample->entry_point)
									{{ $entry_point->name ?? '' }}
								@endif
							@endforeach
						</td>
					</tr>
					<tr>
						<th>Test Result</th>
						<td colspan="3">
							@foreach($results as $result)
								@if($result->id == $sample->result)
									{{ $result->name ?? '' }}
								@endif
							@endforeach
						</td>
					</tr>
					<tr>
						<th>Comment</th>
						<td colspan="3">{{ $sample->labcomment ?? '' }}</td>
					</tr>
				</tbody>
			</table>
		@elseif($testSysm == 'VL')
			@if($sample->receivedstatus == 1 || $sample->receivedstatus == 3)
				@php
					$outcome = $sample->result ." ". $sample->units;
					$intresult = (intval($sample->result)) ? intval($sample->result) : $sample->result;
				@endphp
				@if(is_numeric($intresult))
					@php
						$log = round(log10((float)$intresult),1);
					@endphp
				@else
					@php
						$log = 'N/A';
					@endphp
				@endif
			@elseif($sample->receivedstatus == 2)
				@foreach($viral_rejected_reasons as $rejectedreason)
					@if($rejectedreason->id == $sample->rejectedreason)
						@php
							$rejectreason = $rejectedreason->name;
						@endphp
					@endif
				@endforeach
				$outcome = "Sample ".$sample->receivedstatus_name . " Reason:  ".$rejectreason;
			@endif
			<table class="table-bordered">
				<thead>
					<tr>
						<th colspan="2">Viral Load Results</th>
						<th colspan="2">Historical Information</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Patient CCC No</th>
						<td>{{ $sample->patient ?? '' }}</td>
						<th>Sample Type</th>
						<td>{{ $sample->sampletype_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Collected</th>
						<td>{{ ($sample->datecollected) ? date('d-M-Y', strtotime($sample->datecollected)) : '' }}</td>
						<th>ART Initiation Date</th>
						<td>{{ ($sample->initiation_date) ? (($sample->initiation_date != '0000-00-00') ? date('d-M-Y', strtotime($sample->initiation_date)) : '') : '' }}</td>
					</tr>
					<tr>
						<th>Date Received</th>
						<td>{{ ($sample->datereceived) ? date('d-M-Y', strtotime($sample->datereceived)) : '' }}</td>
						<th>Current Regimen</th>
						<td>{{ $sample->prophylaxis_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Tested</th>
						<td>{{ ($sample->datetested) ? date('d-M-Y', strtotime($sample->datetested)) : '' }}</td>
						<th>Justification</th>
						<td>{{ $sample->justification_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Age (Years)</th>
						<td>{{ $sample->age ?? '' }}</td>
						<th>DOB</th>
						<td>{{ $sample->dob ?? '' }}</td>
					</tr>
					<tr>
						<th>Test Result</th>
						<td colspan="2">
							Viral Load : {{ $outcome ?? '' }}
						</td>
						<td>Log 10 : {{ $log ?? '' }}</td>
					</tr>
					<tr>
						<th>Comment</th>
						<td colspan="3">{{ $sample->labcomment ?? '' }}</td>
					</tr>
					@forelse($previousSamples as $previous)
						<tr>
							<th>Previous VL Result</th>
							<td>Viral Load : {{ $previous->result ." ". $previous->units }}</td>
							<th>Date Tested</th>
							<td>{{ ($previous->datetested) ? date('d-M-Y', strtotime($previous->datetested)) : '' }}</td>
						</tr>
					@empty
						<tr>
							<th colspan="2">Previous VL Result</th>
							<td colspan="2"><center>N/A</center></td>
						</tr>
					@endforelse
				</tbody>
			</table>
		@endif
		<p class="notice">NOTICE</p>
		<p class="notice">Results are now delivered electronically . Kindly ensure you indicate the facility's up-to-date email address on the sample requisition form to facilitate this.</p>
		<p class="notice">The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.Call the official EID lines for more information. Thank you..</p>
		<p>If you have questions or problems regarding samples, please contact the KEMRI-BUSIA Lab at eid-alupe@googlegroups.com or through 0726156679 </p>

		<img src="{{ asset('img/but_cut.gif') }}">
	</div>
	@endforeach
</body>
</html>