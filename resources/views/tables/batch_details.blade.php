@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
<<<<<<< HEAD
                            <p><strong>Batch:</strong> {{ $data->batch->id  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Facility:</strong> {{-- ($data->batch->view_facility->facilitycode . ' - ' . $data->batch->view_facility->name . ' (' . $data->batch->view_facility->county . ')') ?? '' --}}</p>
=======
                            <p><strong>Batch:</strong> {{ $batch->id  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Facility:</strong> {{ ($batch->view_facility->facilitycode . ' - ' . $batch->view_facility->name . ' (' . $batch->view_facility->county . ')') ?? '' }}</p>
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                        </div>
                        <div class="col-md-4">
                            <p>
                                <strong>Entry Type: </strong>
<<<<<<< HEAD
                                @switch($data->batch->site_entry)
=======
                                @switch($batch->site_entry)
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                                    @case(0)
                                        {{ 'Lab Entry' }}
                                        @break
                                    @case(1)
                                        {{ 'Site Entry' }}
                                        @break
                                    @case(2)
                                        {{ 'POC Entry' }}
                                        @break
                                    @default
                                        @break
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-4">
<<<<<<< HEAD
                            <p><strong>Date Entered:</strong> {{ $data->batch->created_at ?? '' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Entered By:</strong> 
                                @if($data->batch->creator)
                                    @if($data->batch->creator->full_name != ' ')
                                        {{ $data->batch->creator->full_name }}
                                    @else
                                        {{ $data->batch->creator->facility->name ?? '' }}
=======
                            <p><strong>Date Entered:</strong> {{ $batch->my_date_format('created_at') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Entered By:</strong> 
                                @if($batch->creator)
                                    @if($batch->creator->full_name != ' ')
                                        {{ $batch->creator->full_name }}
                                    @else
                                        {{ $batch->creator->facility->name ?? '' }}
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
<<<<<<< HEAD
                            <p><strong>Date Received:</strong> {{ date('d-m-Y', $data->batch->datereceived)  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Received By:</strong> {{ $data->batch->receiver->full_name ?? '' }}</p>
=======
                            <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Received By:</strong> {{ $batch->receiver->full_name ?? '' }}</p>
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                        </div>                       
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th colspan="17"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="6">Patient Information</th>
                                    <th colspan="3">Sample Information</th>
<<<<<<< HEAD
                                    <th colspan="8">Mother Information</th>
=======
                                    <th colspan="7">Mother Information</th>
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                                </tr>
                                <tr> 
                                    <th>No</th>
                                    <th>Patient ID</th>
                                    <th>Sex</th>
                                    <th>DOB</th>
                                    <th>Age (Months)</th>
                                    <th>Infant Prophylaxis</th>

                                    <th>Date Collected</th>
                                    <th>Status</th>
                                    <th>Spots</th>

                                    <th>CCC #</th>
                                    <th>Age</th>
                                    <th>Last Vl</th>
                                    <th>PMTCT Intervention</th>
                                    <th>Feeding Type</th>
                                    <th>Entry Point</th>
                                    <th>Result</th>
<<<<<<< HEAD
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($data->samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $sample->patient }} </td>
                                        <td> {{ $sample->sex }} </td>
                                        <td> {{ $sample->dob }} </td>
                                        <td> {{ $sample->age ?? '' }} </td>
                                        <td>
                                            @php/*
                                            @foreach($iprophylaxis as $iproph)
                                                @if($sample->regimen == $iproph->id)
                                                    {{ $iproph->name }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
                                        </td>

                                        <td> {{ $sample->datecollected }} </td>
                                        <td>
                                            @php/*
                                            @foreach($received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
=======
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($batch->sample as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $sample->patient->patient }} </td>
                                        <td> 
                                            @foreach($data->genders as $gender)
                                                @if($sample->patient->sex == $gender->id)
                                                    {{ $gender->gender_description }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->patient->my_date_format('dob') }} </td>
                                        <td> {{ $sample->age }} </td>
                                        <td>
                                            @foreach($data->iprophylaxis as $iproph)
                                                @if($sample->regimen == $iproph->id)
                                                    {{ $iproph->name }}
                                                @endif
                                            @endforeach
                                        </td>

                                        <td> {{ $sample->my_date_format('datecollected') }} </td>
                                        <td>
                                            @foreach($data->received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name }}
                                                @endif
                                            @endforeach
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                                        </td>
                                        <td> {{ $sample->spots }} </td>

                                        <td> {{ $sample->patient->mother->ccc_no ?? '' }} </td>
<<<<<<< HEAD
                                        <td> {{ $sample->mother_age ?? '' }} </td>
                                        <td> {{ $sample->mother_last_result ?? '' }} </td>
                                        <td>
                                            @php/*
                                            @foreach($interventions as $intervention)
                                                @if($sample->mother_prophylaxis == $intervention->id)
                                                    {{ $intervention->name }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
                                        </td>
                                        <td>
                                            @php/*
                                            @foreach($feedings as $feeding)
                                                @if($sample->feeding == $feeding->id)
                                                    {{ $feeding->feeding }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
                                        </td>
                                        <td>
                                            @php/*
                                            @foreach($entry_points as $entry_point)
                                                @if($sample->patient->entry_point == $entry_point->id)
                                                    {{ $entry_point->name }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
                                        </td>
                                        <td>
                                            @php/*
                                            @foreach($results as $result)
                                                @if($sample->result == $result->id)
                                                    {{ $result->name }}
                                                @endif
                                            @endforeach*/
                                            @endphp
                                            N/A
                                        </td>
                                        <td>
                                            N/A
=======
                                        <td> {{ $sample->mother_age }} </td>
                                        <td> {{ $sample->mother_last_result }} </td>
                                        <td>
                                            @foreach($data->interventions as $intervention)
                                                @if($sample->mother_prophylaxis == $intervention->id)
                                                    {{ $intervention->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($data->feedings as $feeding)
                                                @if($sample->feeding == $feeding->id)
                                                    {{ $feeding->feeding }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($data->entry_points as $entry_point)
                                                @if($sample->patient->entry_point == $entry_point->id)
                                                    {{ $entry_point->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($data->results as $result)
                                                @if($sample->result == $result->id)
                                                    {{ $result->name }}
                                                @endif
                                            @endforeach
>>>>>>> 5ac1971fe54e86743685d3c274c157db16da65ac
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection