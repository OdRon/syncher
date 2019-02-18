@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Edit Sample Details
                </h2>
            </div>
        </div>
    </div>

   <div class="content">
        <div>

        {{ Form::open(['url' => 'sample/' . $data->testtype . '/' . $data->sample->id . '/update', 'method' => 'put', 'class'=>'form-horizontal confirmSubmit', 'confirm_message' => 'Are you sure you would like to edit these sample details?']) }}

        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center> </center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Facility</label>
                            <div class="col-sm-8">
                                <input class="form-control" disabled type="text" value="{{ $data->sample->batch->facility->name ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient</label>
                            <div class="col-sm-8">
                                <input class="form-control" disabled type="text" value="{{ $data->sample->patient->patient ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Birth</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dob" required class="form-control lockable" value="{{ $data->sample->patient->dob ?? '' }}" name="dob">
                                </div>
                            </div>                            
                        </div>

                        @if($data->testtype == 'EID')
                            <div class="form-group">
                                <label class="col-sm-4 control-label">PCR Type:</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="pcrtype" id="pcrtype">
                                        @foreach($data->pcrtypes as $pcrtype)
                                            @if($data->sample->pcrtype == $pcrtype->id)
                                                <option value="{{ $pcrtype->id }}" selected>{!! $pcrtype->name !!}</option>
                                            @else
                                                <option value="{{ $pcrtype->id }}">{!! $pcrtype->name !!}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @elseif($data->testtype == 'VL')
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date Initiated on Treatment</label>
                                <div class="col-sm-8">
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="initiation_date" required class="form-control lockable" value="{{ $data->sample->patient->initiation_date ?? '' }}" name="initiation_date">
                                    </div>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Justification:</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="justification" id="justification">
                                        @foreach($data->justifications as $justification)
                                            @if($data->sample->justification == $justification->id)
                                                <option value="{{ $justification->id }}" selected>{!! $justification->displaylabel !!}</option>
                                            @else
                                                <option value="{{ $justification->id }}">{!! $justification->displaylabel !!}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Regimen:</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="prophylaxis" id="prophylaxis">
                                        @foreach($data->prophylaxis as $regimen)
                                            @if($data->sample->prophylaxis == $regimen->id)
                                                <option value="{{ $regimen->id }}" selected>{{ $regimen->displaylabel }}</option>
                                            @else
                                                <option value="{{ $regimen->id }}">{!! $regimen->displaylabel !!}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button class="btn btn-success" type="submit">Update Sample</button>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        {{ Form::close() }}

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });
    @endcomponent

@endsection
