 
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
     		font-size: 8px;
     		text-align: center;
		}

		th{
			font-weight: bold;
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

	<div align="center">
		<img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP">
		<br />
		<h3>MINISTRY OF HEALTH</h3>
		<h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3>	
		<h3> {{ $title }} </h3>	
	</div>


	<table style="width: 100%;">
		<thead>
			<tr>
				<th> # </th>
				<th> MFL Code </th>
				<th> Facility Name </th>
				<th> County </th>
				<th> Sub-County </th>
				<th> Positives </th>
				<th> On Treatment </th>
				<th> LTFU </th>
				<th> Dead </th>
				<th> Adult </th>
				<th> Transfer </th>
				<th> Other </th>
				<th> Not Documented Online </th>
				<th> % Undocumented </th>
			</tr>
		</thead>
		<tbody>
			@foreach($summary as $row)
				<td> {{ $row->no }} </td>
				<td> {{ $row->mfl }} </td>
				<td> {{ $row->facility }} </td>
				<td> {{ $row->county }} </td>
				<td> {{ $row->subcounty }} </td>
				<td> {{ $row->positives }} </td>
				<td> {{ $row->treatment }} </td>
				<td> {{ $row->ltfu }} </td>
				<td> {{ $row->dead }} </td>
				<td> {{ $row->adult }} </td>
				<td> {{ $row->transfer }} </td>
				<td> {{ $row->otherreasons }} </td>
				<td> {{ $row->unknown }} </td>
				<td> {{ $row->unknown_percentage }} </td>
			@endforeach
		</tbody>
	</table>

	@if($samples->isNotEmpty())	
		<p class="breakhere"></p>
		<pagebreak sheet-size='A4-L'>

		<div align="center">
			<img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP">
			<br />
			<h3>MINISTRY OF HEALTH</h3>
			<h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3>	
			<h3> INDIVIDUAL HIV EXPOSED INFANTS FOR FOLLOW UP & ONLINE DOCUMENTATION IN {{ date('Y') }} </h3>	
		</div>

		<table>
			<thead>
				<tr>
					<th>#</th>
					<th>County</th>
					<th>Facility Name</th>
					<th>MFL</th>
					<th>Sample/Patient ID</th>
					<th>Date Collected</th>
					<th>Date Tested</th>
					<th>Validation(CP,A,VL,RT,UF)</th>
					<th>Status</th>
					<th>Date Initiated on Tx</th>
					<th>Enrolment CCC #</th>
				</tr>
			</thead>
			<tbody>
				@foreach($samples as $key => $sample)
					<td> {{ $key+1 }} </td>
					<td> {{ $sample->county }} </td>
					<td> {{ $sample->facility }} </td>
					<td> {{ $sample->facilitycode }} </td>
					<td> {{ $sample->patient }} </td>
					<td> {{ $sample->my_date_format('datecollected') }} </td>
					<td> {{ $sample->my_date_format('datetested') }} </td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				@endforeach
			</tbody>
			
		</table>
	@endif
</body>

</html>