<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		body {
			font-weight: 1px;
		}

		table {
			border-collapse: collapse;
			margin-bottom: .5em;
		}

		table, th, td {
			border: 1px solid black;
			border-style: solid;
     		font-size: 8px;
		}

		h5 {
			margin-top: 6px;
		    margin-bottom: 6px;
		}

		p {
			margin-top: 2px;
     		font-size: 8px;
		}
		* {
			font-size: 8px;
		}
	</style>
</head>
<body>
@foreach($batches as $batch)
	<table border="0" style="border: 0px; width: 100%;">
		<tr>
			<td colspan="9" align="center">
				<img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP">
			</td>
		</tr>
		<tr>
			<td colspan="9" align="center">
				<h5>MINISTRY OF HEALTH</h5>
				<h5>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h5>
				<h5>
				@if($testingSys == 'EID') 
					EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM 
				@elseif($testingSys == 'VL')
					VIRAL LOAD TEST RESULTS SUMMARY
				@endif
				</h5>
			</td>
		</tr>
		<tr>
			<td colspan="5">
				<strong> Batch No.: {{ $batch->original_batch_id }} &nbsp;&nbsp; {{ $batch->facility->name }} </strong> 
			</td>
			<td colspan="4">
				<strong>LAB: {{ $batch->lab->name ?? '' }}</strong>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<strong>NOTICE:</strong>
				<strong>The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.Call the official EID lines for more information. Thank you.</strong>
			</td>
		</tr>
	</table>

	<table style="width: 100%;">
		<tr>
			<td colspan='3'>Date Samples Were Dispatched :  {{ $batch->my_date_format('datedispatched')  }}</td>				
		</tr>
		<tr>
			<td>Facility Name: {{ $batch->facility->name }} </td>
			<td>Contact: {{ $batch->facility->contactperson ?? '' }} </td>
			<td>Tel(personal): {{ $batch->facility->contacttelephone ?? '' }} </td>
		</tr>
		<tr>
			<td colspan='3'>Receiving Address (via Courier): {{ $batch->facility->PostalAddress }}</td>
		</tr>
		<tr>
			<td colspan='3'>Email (optional-where provided results will be emailed and also sent by courier ):  {{ $batch->facility->email }}</td>
		</tr>
	</table>
	<br />
	<table style="width: 100%;">
		@php
			if($testingSys == 'EID') {
				$colspan = 19;
				$pcolspan = 8;
				$mcolspan = 4;
				$scolspan = 7;
			} else if ($testingSys == 'VL') {
				$colspan = 16;
				$pcolspan = 6;
				$hcolspan = 2;
				$scolspan = 4;
				$lcolspan = 4;
			}
		@endphp
		<tr>
			<th colspan="{{ $colspan }}" style="text-align: center;">SAMPLE LOG </th>
		</tr>
		<tr>
			<th colspan="{{ $pcolspan }}" style="text-align: center;">Patient Information</th>
			@if($testingSys == 'EID')
				<th colspan="{{ $mcolspan }}" style="text-align: center;">Mother Information</th>
			@endif
			<th colspan="{{ $scolspan }}" style="text-align: center;">Samples Information</th>
			@if($testingSys == 'VL')
				<th colspan="{{ $hcolspan }}">History Information</th>
			@endif
			@if($testingSys == 'VL')
				<th colspan="{{ $lcolspan }}">Lab Information</th>
			@endif
		</tr>
		<tr>
			<th>No</th>
			<th>Patient @if($testingSys == 'EID') ID @elseif($testingSys == 'VL') CCC No @endif</th>
			<th>DOB</th>
			<th>Age @if($testingSys == 'EID') (in months) @elseif($testingSys == 'VL') (yrs) @endif</th>
			<th>Sex</th>
			@if($testingSys == 'EID')
				<th>Entry Point</th>
				<th>Prophylaxis</th>
				<th>Feeding</th>
			@elseif($testingSys == 'VL')
				<th>ART Initiation Date</th>
			@endif

			@if($testingSys == 'EID')
				<th>Age</th>
				<th>CCC No</th>
				<th>Regimen</th>
				<th>Last Vl</th>
				<th>Test Type</th>
			@endif

			<th>Date Collected</th>
			<th>Date Received</th>

			@if($testingSys == 'VL')
				<th>Status</th>
				<th>Sample Type</th>
				<th>Current Regimen</th>
				<th>Justification</th>
			@endif

			<th>Date Tested</th>
			<th>Date Dispatched</th>
			<th>Test Result</th>
			<th>TAT</th>
		</tr>
		@foreach($batch->sample as $key => $sample)
			@if($sample->receivedstatus == 2)
				@php  
					$rejection = true;
					continue;
				@endphp
			@endif
			@continue($sample->repeatt == 1)
			<tr>
				<td>{{ ($key+1) }}</td>
				<td>{{ $sample->patient->patient ?? '' }} </td>
				<td>{{ $sample->patient->dob ?? '' }} </td>
				<td>{{ $sample->age ?? '' }} </td>
				<td>{{ $sample->patient->gender ?? '' }} </td>
				@if($testingSys == 'EID')
	                <td>
						@foreach($entry_points as $entry_point)
	                        @if($sample->patient->entry_point == $entry_point->id)
	                            {{ $entry_point->name }}
	                        @endif
						@endforeach
					</td>
					<td>{{ $sample->regimen }} </td>
					<td>
	                    @foreach($feedings as $feeding)
	                        @if($sample->feeding == $feeding->id)
	                            {{ $feeding->feeding }}
	                        @endif
	                    @endforeach		
	                </td>
				@elseif($testingSys == 'VL')
					<td>{{ $sample->patient->my_date_format('initiation_date') }} </td>
				@endif

				@if($testingSys == 'EID')
					<td>{{ $sample->mother_age }} </td>
					<td>{{ $sample->patient->mother->ccc_no }} </td>
					<td>{{ $sample->mother_prophylaxis }} </td>
					<td>{{ $sample->mother_last_result }} </td>
					<td>
	                    @foreach($pcrtypes as $pcrtype)
	                        @if($sample->pcrtype == $pcrtype->id)
	                            {{ $pcrtype->alias }}
	                        @endif
	                    @endforeach	

						@if($sample->redraw) 
							(redraw) 
						@endif 
					</td>
				@endif
				<td>{{ $sample->my_date_format('datecollected') }} </td>
				<td>{{ $batch->my_date_format('datereceived') }} </td>

				@if($testingSys == 'VL')
					<td>
	                    @foreach($received_statuses as $received_status)
	                        @if($sample->receivedstatus == $received_status->id)
	                            {{ $received_status->name ?? '' }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->sampletype }} </td>
					<td>
	                    @foreach($prophylaxis as $proph)
	                        @if($sample->prophylaxis == $proph->id)
	                            {{ $proph->name }}
	                        @endif
	                    @endforeach
	                </td>
					<td>{{ $sample->justification }} </td>
				@endif

				<td>{{ $sample->my_date_format('datetested') }} </td>
				<td>{{ $batch->my_date_format('datedispatched') }} </td>
				<td>
				@if($testingSys == 'EID')
                    @foreach($results as $result)
                        @if($sample->result == $result->id)
                            {{ $result->name }}
                        @endif
                    @endforeach
                @elseif($testingSys == 'VL')
                	{{ $sample->result }}
                @endif
				</td>
				<td>{{ $sample->tat($batch->datedispatched) }} </td>
			</tr>
		@endforeach
	</table>

	<p>Result Reviewed By: {{ $sample->approver->full_name ?? '' }}  Date Reviewed: {{ $sample->my_date_format('dateapproved') }}</p>

	@isset($rejection)

	@endisset
@endforeach
</body>
</html>