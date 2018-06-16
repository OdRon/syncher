@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
        .reports {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 0px;
            /*padding-bottom: 0px;*/
        }
    </style>
@endsection

@section('content')
@php
    $defaultmonth = date('Y');
@endphp
<div class="content">
    <div class="row" style="margin-bottom: 1em;">
        <!-- Year -->
        <div class="col-md-6">
            <center><h5>Year Filter</h5></center>
            @for ($i = 0; $i <= 9; $i++)
                @php
                    $year=Date('Y')-$i
                @endphp
                <a href='{{ url("hei/validate/$year") }}'>{{ Date('Y')-$i }}</a> |
            @endfor
        </div>
        <!-- Year -->
        <!-- Month -->
        <div class="col-md-6">
            <center><h5>Month Filter</h5></center>
            @for ($i = 1; $i <= 12; $i++)
                <a href='{{ url("hei/validate/null/$i") }}'>{{ date("F", mktime(null, null, null, $i)) }}</a> |
            @endfor
        </div>
        <!-- Month -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="alert alert-danger">
                    <center>* To Update HEI Enrollment Status below, Click on 'Click Here to Fill Follow Up Details' Link on the ' Infants of NOT Documented Online ({{ $defaultmonth }}) Row .</center>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr>
                                <th colspan="2" style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-success">
                                        <center>Initial PCR Outcomes
                                            <strong>[
                                            @if(null !== Session::pull('followupMonth'))
                                                {{ date("F", mktime(null, null, null, Session::pull('followupMonth'))) }}
                                            @endif
                                             {{ Session('followupYear') ?? date('Y') }}]
                                            </strong>
                                        </center>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    Positive Outcomes 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->positiveOutcomes) }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Initiated onto Treatment 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->enrolled) }}
                                    <strong>[{{ round((@($data->outcomes->enrolled/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Lost to Follow up 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->ltfu) }}
                                    <strong>[{{ round((@($data->outcomes->ltfu/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Died 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->dead) }}
                                    <strong>[{{ round((@($data->outcomes->dead/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <!-- <tr>
                                <th>
                                    Infants who were Adult Sample (2018 )
                                </th>
                                <td>
                                    {{ $data->outcomes->enrolled }}
                                    {{ round((@($data->outcomes->enrolled/$data->outcomes->positiveOutcomes)/100),1) }}
                                </td>
                            </tr> -->
                            <tr>
                                <th>
                                    Infants Transferred Out 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->transferOut) }}
                                    <strong>[{{ round((@($data->outcomes->transferOut/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants with (Other Reasons) 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->other) }}
                                    <strong>[{{ round((@($data->outcomes->other/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-warning">
                                        Infants NOT Documented Online 
                                    </div>
                                </th>
                                <td style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-warning">
                                        {{ number_format($data->outcomes->unknown) }}
                                        <strong>[{{ round((@($data->outcomes->unknown/$data->outcomes->positiveOutcomes)*100),1) }}%]</strong>
                                        @if($data->outcomes->unknown > 0)
                                            <a href="{{ url('hei/followup') }}" style="color: blue;">Click to View Full Listing</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr>
                                <th colspan="2" style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-success">
                                        <center>Initial PCR Cumulative Outcomes</center>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    Positive Outcomes 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->positiveOutcomes) }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Initiated onto Treatment 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->enrolled) }}
                                    <strong>[{{ round((@($data->cumulative->enrolled/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Lost to Follow up 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->ltfu) }}
                                    <strong>[{{ round(@(($data->cumulative->ltfu/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Died 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->dead) }}
                                    <strong>[{{ round(@(($data->cumulative->dead/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <!-- <tr>
                                <th>
                                    Infants who were Adult Sample (2018 )
                                </th>
                                <td>
                                    {{ $data->outcomes->enrolled }}
                                    {{ round((@($data->outcomes->enrolled/$data->outcomes->positiveOutcomes)/100),1) }}
                                </td>
                            </tr> -->
                            <tr>
                                <th>
                                    Infants Transferred Out 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->transferOut) }}
                                    <strong>[{{ round((@($data->cumulative->transferOut/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants with (Other Reasons) 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->other) }}
                                    <strong>[{{ round((@($data->cumulative->other/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants NOT Documented Online 
                                </th>
                                <td>
                                    {{ number_format($data->cumulative->unknown) }}
                                    <strong>[{{ round((@($data->cumulative->unknown/$data->cumulative->positiveOutcomes)*100),1) }}%]</strong>
                                    @if($data->cumulative->unknown > 0)
                                        <a href="{{ url('hei/followup/cumulative') }}" style="color: blue;">Click to View Full Listing</a>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
            dateFormat: 'MM yy'
        });

    @endcomponent
   
@endsection