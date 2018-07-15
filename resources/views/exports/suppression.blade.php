
<!DOCTYPE html>
<html>
<head>

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
     		font-size: 10px;
     		text-align: center;
		}

		th{
			font-weight: bold;
		}

		h1, h2, h3 {
			margin-top: 6px;
		    margin-bottom: 6px;
     		text-align: center;
		}
	</style>
</head>
<body>
	<div align='center' style='text-align: center; align-content: center;'>
	    <img src="{{ asset('img/naslogo.jpg') }}" alt='NASCOP'>
	    <h3>MINISTRY OF HEALTH</h3>
	    <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
	</div>

	<h3> {{ $title }} </h3>	

	<table style="width: 100%;">
		<thead>
			<tr>
				<th>#</th>
				<th>MFL Code</th>
				<th>Facility Name</th>
				<th>County</th>
				<th>Sub-County</th>
				<th>Total Tests</th>
				<th>&gt;1000cp/ml</th>
				<th>Pregnant</th>
				<th>Breast Feeding</th>
				<th>Adolescent (10-19)</th>
				<th>Children (&lt;10)</th>
				<th>Adults (&gt;20)</th>
				<th>No Data On Age</th>
			</tr>
		</thead>
		<tbody>
			@foreach($summary as $row)
				<tr
					@if($row['non_sup'])
						class='positive'
					@endif 
				>
					<td> {{ $row['no'] }} </td>
					<td> {{ $row['mfl'] }} </td>
					<td> {{ $row['facility'] }} </td>
					<td> {{ $row['county'] }} </td>
					<td> {{ $row['subcounty'] }} </td>
					<td> {{ $row['total'] }} </td>
					<td> {{ $row['non_sup'] }} </td>
					<td> {{ $row['pregnant'] }} </td>
					<td> {{ $row['breast_feeding'] }} </td>
					<td> {{ $row['adolescents'] }} </td>
					<td> {{ $row['children'] }} </td>
					<td> {{ $row['adults'] }} </td>
					<td> {{ $row['no_age'] }} </td>					
				</tr>
			@endforeach			
		</tbody>
	</table>

	@if($adolescents)	
		<p class="breakhere"></p>
		<pagebreak sheet-size='A4-L'>
			
		<div align='center' style='text-align: center; align-content: center;'>
		    <img src="{{ asset('img/naslogo.jpg') }}" alt='NASCOP'>
		    <h3>MINISTRY OF HEALTH</h3>
		    <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
		</div>

		<h3>INDIVIDUAL ADOLESCENT PATIENTS [10-19 Yrs] WITH OUTCOMES >1000cp/ml (Not Suppressed) FOR FOLLOW UP BETWEEN {{ $range }} </h3>

		<table>
			<thead>
				<tr>
					<th>#</th>
					<th>County</th>
					<th>Facility Name</th>
					<th>MFL</th>
					<th>Patient CCC</th>
					<th>Age</th>
					<th>Sex</th>
					<th>Date Drawn</th>
					<th>Date Tested</th>
					<th>Result</th>
					<th>Justification</th>
					<th>Regimen</th>
					<th>Previous VL</th>
					<th>Date Tested</th>
				</tr>
			</thead>
			<tbody>
				@foreach($adolescents as $key => $sample)
					<tr>
						<td> {{ $key+1 }} </td>
						<td> {{ $sample->county }} </td>
						<td> {{ $sample->facility }} </td>
						<td> {{ $sample->facilitycode }} </td>
						<td> {{ $sample->patient }} </td>
						<td> {{ $sample->age }} </td>
						<td> {{ $sample->gender_short }} </td>
						<td> {{ $sample->datecollected }} </td>
						<td> {{ $sample->datetested }} </td>
						<td> {{ $sample->result }} </td>
						<td> {{ $justifications->where('id', $sample->justification)->first()->name ?? '' }} </td>
						<td> {{ $prophylaxis->where('id', $sample->prophylaxis)->first()->name ?? '' }} </td>
						<?php
							$sample->vl_prev_test();
						?>
						<td> {{ $sample->previous->result ?? '' }} </td>
						<td> {{ $sample->previous->datetested ?? '' }} </td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif

	

	@if($non_suppressed)	
		<p class="breakhere"></p>
		<pagebreak sheet-size='A4-L'>
			
		<div align='center' style='text-align: center; align-content: center;'>
		    <img src="{{ asset('img/naslogo.jpg') }}" alt='NASCOP'>
		    <h3>MINISTRY OF HEALTH</h3>
		    <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
		</div>

		<h3>INDIVIDUAL PATIENTS WITH OUTCOMES >1000cp/ml (Not Suppressed) FOR FOLLOW UP BETWEEN {{ $range }} </h3>

		<table>
			<thead>
				<tr>
					<th>#</th>
					<th>County</th>
					<th>Facility Name</th>
					<th>MFL</th>
					<th>Patient CCC</th>
					<th>Age</th>
					<th>Sex</th>
					<th>Date Drawn</th>
					<th>Date Tested</th>
					<th>Result</th>
					<th>Justification</th>
					<th>Regimen</th>
					<th>Previous VL</th>
					<th>Date Tested</th>
				</tr>
			</thead>
			<tbody>
				@foreach($non_suppressed as $key => $sample)
					<tr>
						<td> {{ $key+1 }} </td>
						<td> {{ $sample->county }} </td>
						<td> {{ $sample->facility }} </td>
						<td> {{ $sample->facilitycode }} </td>
						<td> {{ $sample->patient }} </td>
						<td> {{ $sample->age }} </td>
						<td> {{ $sample->gender_short }} </td>
						<td> {{ $sample->datecollected }} </td>
						<td> {{ $sample->datetested }} </td>
						<td> {{ $sample->result }} </td>
						<td> {{ $justifications->where('id', $sample->justification)->first()->name ?? '' }} </td>
						<td> {{ $prophylaxis->where('id', $sample->prophylaxis)->first()->name ?? '' }} </td>
						<?php
							$sample->vl_prev_test();
						?>
						<td> {{ $sample->previous->result ?? '' }} </td>
						<td> {{ $sample->previous->datetested ?? '' }} </td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif


</body>
</html>