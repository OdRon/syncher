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
                            <p><strong>Batch:</strong> {{ $batch->original_batch_id  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Facility:</strong> {{ ($batch->view_facility->facilitycode . ' - ' . $batch->view_facility->name . ' (' . $batch->view_facility->county . ')') ?? '' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p>
                                <strong>Entry Type: </strong>
                                @switch($batch->site_entry)
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
                        <div class="col-md-6">
                            <p><strong>Date Entered:</strong> {{ $batch->my_date_format('created_at') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                        </div>                       
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th colspan="17"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="6">Patient Information</th>
                                    <th colspan="3">Sample Information</th>
                                    <th colspan="7">Mother Information</th>
                                    <th></th>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($batch->sample as $key => $sample)
                                    @if($sample->repeatt == 0)
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
                                        </td>
                                        <td> {{ $sample->spots }} </td>

                                        <td> {{ $sample->patient->mother->ccc_no ?? '' }} </td>
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
                                        </td>
                                        <td><center><a href="{{ url('sample/EID/'.$sample->id.'/edit') }}">Edit</a></center></td>
                                    </tr>
                                    @endif
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