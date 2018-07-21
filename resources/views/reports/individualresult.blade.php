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
     		/*font-size: 8px;*/
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
	</style>
</head>
<body>
	@foreach($data->samples as $sample)
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
				<th>Batch No. : {{ $sample->batch_id ?? '' }} </th>
				<th>{{ $sample->facility->name ?? '' }}</th>
				<th>Lab : {{ $sample->lab->name }}</th>
			</tr>
		</table>
		@if($data->testSysm == 'EID')
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
						<td>{{ ($sample->datecollected) ? date($sample->datecollected) : '' }}</td>
						<th>PMTCT Intervention</th>
						<td>{{ $sample->mother_prophylaxis_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Received</th>
						<td>{{ ($sample->datereceived) ? date($sample->datereceived) : '' }}</td>
						<th>Infant Prophylaxis</th>
						<td>{{ $sample->regimen_name ?? '' }}</td>
					</tr>
					<tr>
						<th>Date Tested</th>
						<td>{{ ($sample->datetested) ? date($sample->datetested) : '' }}</td>
						<th>Infant Feeding</th>
						<td>{{ $sample->feeding_description ?? '' }}</td>
					</tr>
					<tr>
						<th>Age</th>
						<td>{{ $sample->age }}</td>
						<th>Entry Point</th>
						<td>
							@foreach($data->entry_points as $entry_point)
								@if($entry_point->id == $sample->entry_point)
									{{ $entry_point->name ?? '' }}
								@endif
							@endforeach
						</td>
					</tr>
					<tr>
						<th>Test Result</th>
						<td colspan="3">
							@foreach($data->results as $result)
								@if($result->id == $sample->result)
									{{ $result->name ?? '' }}
								@endif
							@endforeach
						</td>
					</tr>
					<tr>
						<th>Comment</th>
						<td colspan="3">{{ $sample->comments ?? '' }}</td>
					</tr>
				</tbody>
			</table>
		@elseif($data->testSysm == 'VL')
			<table class="table-bordered">
				<thead>
					<tr>
						<th colspan="2">DNA PCR Details</th>
						<th colspan="2">Mother Information</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Comment</th>
						<td colspan="3">{{ $sample->comments ?? '' }}</td>
					</tr>
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