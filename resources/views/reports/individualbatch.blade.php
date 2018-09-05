<!DOCTYPE html>
<html>
	<style type="text/css">
		<!--
		.style1 {font-family: "Courier New", Courier, monospace}
		.style4 {font-size: 12}
		.style5 {font-family: "Courier New", Courier, monospace; font-size: 12; }
		.style8 {font-family: "Courier New", Courier, monospace; font-size: 11; }
		.style6 {
			font-size: medium;
			font-weight: bold;
		}
		-->
	</style>
	<style>
		td { }

		.oddrow {
		 	background-color : #CCCCCC;
		}
		.evenrow {
		 	background-color : #F0F0F0;
		}
		#table1 {
			border : solid 1px black;
			width:1000px;
			width:1000px;
		}
		.style7 {font-size: medium}
		.style10 {font-size: 16px}
		p.breakhere {page-break-before: always}
	</style>
	<body>
		@foreach($samples as $key => $sample)
			<table  border="0" id='table1' align="center">
				<tr>
					<td colspan="9" align="center">
						<span class="style6 style1">
							<strong><img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP" align="absmiddle" ></strong> 
						</span>
						<span class="style1"><br>
							<span class="style7">
								MINISTRY OF HEALTH <br />
								NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)
								@if($testingSys == 'EID')
									<br />
									EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM
								@endif
							</span>
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="5" class="comment style1 style4">
						<strong> Batch No.: {{ $sample->batch->original_batch_id }} &nbsp;&nbsp; {{ $sample->batch->view_facility->name }} </strong> 
					</td>
					<td colspan="4" class="comment style1 style4" align="right">
						<strong>LAB: {{ $sample->batch->lab->name }}</strong>
					</td>
				</tr>
				<tr>
					<td colspan="3"  class="evenrow" align="center" >
						<span class="style1 style10">
							<strong>
							@if($testingSys == 'EID')
								DNA PCR TEST RESULTS
							@elseif($testingSys == 'VL')
								Viral Load Results
							@endif
							</strong>
						</span>
					</td>
					<td colspan="4" class="evenrow" align="center">
						<span class="style1 style10">
							<strong>
								@if($testingSys == 'EID')
									Mother & Infant Information
								@elseif($testingSys == 'VL')
									Historical  Information
								@endif
							</strong>
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="style4 style1 comment"><strong>
						@if($testingSys == 'EID') HEI Number @elseif($testingSys == 'VL') Patient CCC No @endif
					</strong></td>
					<td colspan="1"> <span class="style5">{{ $sample->patient->patient }}</span></td>
					<td class="style4 style1 comment" colspan="3"><strong> 
						@if($testingSys == 'EID') Infant Prophylaxis @elseif($testingSys == 'VL') Sample Type @endif
					</strong></td>
					<td colspan="1" class="comment">
						<span class="style5">
							@if($testingSys == 'EID')
			                    @foreach($iprophylaxis as $iproph)
			                        @if($sample->regimen == $iproph->id)
			                            {{ $iproph->name }}
			                        @endif
			                    @endforeach
			                @elseif($testingSys == 'VL')
			                	@foreach($sample_types as $sample_type)
	                        		@if($sample->sampletype == $sample_type->id)
	                            		{{ $sample_type->name }}
			                        @endif
			                    @endforeach
			                @endif						
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="style4 style1 comment"><strong> 
						DOB & Age @if($testingSys == 'EID') (Months) @elseif($testingSys == 'VL') (Years) @endif
					</strong></td>
					<td colspan="1"  ><span class="style5">{{ $sample->patient->my_date_format('dob') }} {{ $sample->age }}</span></td>
					<td class="style4 style1 comment" colspan="3" ><strong>
						@if($testingSys == 'EID') Infant Feeding @elseif($testingSys == 'VL') Justification @endif
					</strong></td>
					<td colspan="1" class="comment">
						<span class="style5">
							@if($testingSys == 'EID')
			                    @foreach($feedings as $feeding)
		                            @if($sample->feeding == $feeding->id)
		                                {{ $feeding->feeding }}
		                            @endif
		                        @endforeach
			                @elseif($testingSys == 'VL')
			                	@foreach($justifications as $justification)
			                        @if($sample->justification == $justification->id)
			                            {{ $justification->name }}
			                        @endif
			                    @endforeach
			                @endif				
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="style4 style1 comment"><strong> Gender</strong></td>
					<td colspan="1"  ><span class="style5"> {{ $sample->patient->gender }} </span></td>
					<td class="style4 style1 comment" colspan="3" ><strong>
						@if($testingSys == 'EID') Entry Point @elseif($testingSys == 'VL') PMTCT @endif
					</strong></td>
					<td colspan="1" class="comment">
						<span class="style5">
	                        @if($testingSys == 'EID')
			                    @foreach($entry_points as $entry_point)
		                            @if($sample->patient->entry_point == $entry_point->id)
		                                {{ $entry_point->name }}
		                            @endif
		                        @endforeach
			                @elseif($testingSys == 'VL')
			                	@foreach($pmtct_types as $pmtct_type)
			                        @if($sample->pmtct == $pmtct_type->id)
			                            {{ $pmtct_type->name }}
			                        @endif
			                    @endforeach	
			                @endif
						</span>
					</td>
				</tr>
				@if($testingSys == 'EID')
					<tr>
						<td colspan="2" class="style4 style1 comment"><strong> PCR Type</strong></td>
						<td colspan="1">
							<span class="style5">
		                        @foreach($pcrtypes as $pcrtype)
		                            @if($sample->pcrtype == $pcrtype->id)
		                                {{ $pcrtype->name }}
		                            @endif
		                        @endforeach	
							</span>
						</td>
						<td class="style4 style1 comment" colspan="3" ><strong> Mother CCC #</strong></td>
						<td colspan="1" class="comment">
							<span class="style5"> {{ $sample->patient->mother->ccc_no ?? '' }} </span>
						</td>
					</tr>
				@endif
				<tr>
					<td colspan="2" class="style4 style1 comment" ><strong>Date	Collected </strong></td>
					<td class="comment" colspan="1">
						<span class="style5">{{ $sample->my_date_format('datecollected') }}</span>
					</td>
					@if($testingSys == 'EID')
						<td class="style4 style1 comment" colspan="3"><strong> Age (Yrs) </strong></td>
						<td colspan="1" > <span class="style5">{{ $sample->mother_age }}</span></td>
					@elseif($testingSys == 'VL')
						<td class="style4 style1 comment" colspan="3"><strong> ART Initiation Date </strong></td>
						<td colspan="1"><span class="style5">{{ $sample->patient->my_date_format('initiation_date') }}</span></td>
					@endif
				</tr>
				<tr>
					<td colspan="2" class="style4 style1 comment"><strong>Date Received </strong></td>
					<td colspan="1" class="comment" >
						<span class="style5">
							{{ $sample->batch->my_date_format('datereceived') }} 
						</span>
					</td>
					@if($testingSys == 'EID')
						<td class="style4 style1 comment" colspan="3"><strong>PMTCT Intervention </strong></td>
						<td colspan="1" >
							<span class="style5">
			                    @foreach($interventions as $intervention)
			                        @if($sample->mother_prophylaxis == $intervention->id)
			                            {{ $intervention->name }}
			                        @endif
			                    @endforeach
							</span>
						</td>
					@elseif($testingSys == 'VL')
						<td class="style4 style1 comment" colspan="3"><strong>Current ART Regimen	</strong></td>
						<td colspan="1" class="comment">
							<span class="style5">
			                    @foreach($prophylaxis as $proph)
			                        @if($sample->prophylaxis == $proph->id)
			                            {{ $proph->name }}
			                        @endif
			                    @endforeach						
							</span>
						</td>
					@endif
				</tr>
				<tr>
					<td colspan="2" class="style4 style1 comment"><strong>Date Test Perfomed </strong></td>
					<td colspan="1" class="comment" >
						<span class="style5">{{ $sample->my_date_format('datetested') }}</span>
					</td>
					@if($testingSys == 'EID')
						<td class="style4 style1 comment" colspan="3"><strong> Mother Last VL </strong></td>
						<td colspan="1" ><span class="style5">{{ $sample->mother_last_result }}
							@if($sample->mother_last_result && is_integer($sample->mother_last_result))
								cp/ml
							@endif
						</span></td>
					@elseif($testingSys == 'VL')
						<td class="style4 style1 comment" colspan="3" ><strong>Date Initiated on Current Regimen </strong></td>
						<td colspan="1" class="comment"><span class="style5">{{ $sample->my_date_format('dateinitiatedonregimen') }} </span></td>
					@endif
				</tr>
				@if($testingSys == 'VL')
					@php
						if($sample->receivedstatus != 2){
							$routcome = '<u>' . $sample->result . '</u> ' . $sample->units;
							$resultcomments="";
							$vlresultinlog='N/A';

							if ($sample->result == '< LDL copies/ml'){
								$resultcomments="<small>LDL:Lower Detectable Limit i.e. Below Detectable levels by machine( Roche DBS <400 copies/ml , Abbott DBS  <550 copies/ml )</small> ";
							}

							if (is_numeric($sample->result) ){
								$vlresultinlog= round(log10($sample->result),1) ;
							}
						}
						else{
							$reason = $viral_rejected_reasons->where('id', $sample->rejectedreason)->first()->name;
							$status = $received_statuses->where('id', $sample->receivedstatus)->first()->name;
							$routcome= "Sample ".$status . " Reason:  ".$reason;
						}
						$sample->prev_tests();

						$s_type = $sample_types->where('id', $sample->sampletype)->first();

						$test_no = $sample->previous_tests->count();
						$test_no++;

						if(($sample->result > 1000 && $s_type->typecode == 2)
							 || ($sample->result > 5000 && $s_type->typecode == 1))
						{
							$outcome_code = "b";
						}

						else if(($sample->result < 1000 && $s_type->typecode == 2)
							 || ($sample->result < 5000 && $s_type->typecode == 1))
						{
							$outcome_code = "a";
						}
						else{
							$outcome_code = "a";
						}

						$vlmessage='';
						if($sample->receivedstatus == 2){
							$vlmessage='';
						}
						else if($sample->receivedstatus != 2 && $sample->result == "Collect New Sample"){
							$vlmessage='Failed Test';
						}
						else{
							$guideline = $vl_result_guidelines->where('test', $test_no)->where('triagecode', $outcome_code)->where('sampletype', $s_type->typecode)->first();

							if($guideline){
								$vlmessage = $guideline->indication;
							}
						}
					@endphp
				@endif
				<tr>
					<td colspan="2" class="evenrow"><span class="style1"><strong>
					Test Result </strong></span></td>
					<td colspan="5" class="evenrow"  >
						<span class="style1">
							<strong> 
							@if($testingSys == 'EID')
			                    @foreach($results as $result)
			                        @if($sample->result == $result->id)
			                            {{ $result->name }}
			                        @endif
			                    @endforeach
			                @elseif($testingSys == 'VL')
			                	@if($sample->receivedstatus == 2)
									{{ $routcome }}
								@else
									&nbsp;&nbsp;&nbsp;&nbsp; Viral Load {!! $routcome !!} &nbsp;&nbsp;&nbsp; Log 10 
									<u>{{ $vlresultinlog}} </u>
								@endif
			                @endif
							</strong>
						</span>
					</td>
				</tr>

				@if($sample->worksheet)
					<tr>
						<td colspan="2"></td>
						<td colspan="7" class="style4 style1 comment">					
							@if($sample->worksheet->machine_type == 1)
								HIV-1 DNA qualitative  assay on Roche CAP/CTM system
							@elseif($sample->worksheet->machine_type == 2)
								HIV-1 DNA qualitative  assay on Abbott M2000 system
							@endif					
						</td>				
					</tr>
				@endif

				<tr>
					<td colspan="2">
					  <span class="style1"><strong>Comments:</strong></span>
					</td>
					<td colspan="7" class="comment" >
						<span class="style5 ">
							@if($testingSys == 'EID') 
								{{ $sample->comments }}
							@elseif($testingSys == 'VL')
								{{ $vlmessage }}
							@endif
							<br> {{ $sample->labcomment }}
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="3" class="style4 style1 comment">
						<strong>Date Dispatched:  </strong>
					</td>
					<td colspan="6" class="style4 style1 comment">
						{{ $sample->batch->my_date_format('datedispatched') }}
					</td>
				</tr>
				@if($testingSys == 'EID')
					<?php $sample->prev_tests();  ?>
				@endif

				@if($sample->previous_tests->count() > 0)
					@foreach($sample->previous_tests as $prev)
						<tr class="evenrow">
							<td colspan="1"> <span class="style1">Previous {{ $testingSys }} Results</span></td>
							<td colspan="7" class="comment style5" >
								<strong><small>
								@if($testingSys == 'EID')
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				                    @foreach($results as $result)
				                        @if($prev->result == $result->id)
				                            {{ $result->name }}
				                        @endif
				                    @endforeach
				                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;
				                    Date Tested
				                    {{ $prev->my_date_format('datetested') }}
								@elseif($testingSys == 'VL')
									Viral Load {{ $prev->result . ' ' . $prev->units }} &nbsp; Date Tested {{ $prev->my_date_format('datetested') }}
				                @endif
								</small></strong> 
							</td>
						</tr>
					@endforeach
				@else
					<tr class="evenrow">
						<td colspan="2">
							<span class="style1"><strong>Previous {{ $testingSys }} Results</strong></span>
						</td>
						<td colspan="5" class="comment" ><span class="style5 "> N/A </span></td>
					</tr>
				@endif
			</table>

			<span class="style8" > 
				If you have questions or problems regarding samples, please contact the {{ $sample->batch->lab->name }}  
				<br> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				at {{ $sample->batch->lab->email }}
				<br> 
				<b> To Access & Download your current and past results go to : <u> http://eiddash.nascop.org/</u> </b>
			</span>
			<br>
			<br>
			<img src="{{ asset('img/but_cut.gif') }}">
			<br>
			<br>
			@if($key % 2 == 1)
				<p class="breakhere"></p>
				<pagebreak sheet-size='A4'>
			@endif
		@endforeach
	</body>
</html>