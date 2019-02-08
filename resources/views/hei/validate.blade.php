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
    $sessionMonth = (null !== Session('followupMonth')) ? date("F", mktime(null, null, null, Session('followupMonth'))) : '';
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
                    <center>* To Update HEI Enrollment Status below, Click on 'Click Here to Fill Follow Up Details' Link on the ' Infants of NOT Documented Online {{ $sessionMonth }} {{ Session('followupYear') ?? date('Y') }}) Row .</center>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr>
                                <th colspan="2" style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-success">
                                        <center>Infants for Validation
                                            <strong>[{{ $sessionMonth }} {{ Session('followupYear') ?? date('Y') }}]</strong>
                                        </center>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    Infants with Positive Outcomes
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->positives) }}
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/positives') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Initiated onto Treatment 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->enrolled) }}
                                    <strong>[{{ round(@(($data->outcomes->enrolled/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/enrolled') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Lost to Follow up 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->ltfu) }}
                                    <strong>[{{ round(@(($data->outcomes->ltfu/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/ltfu') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Died 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->dead) }}
                                    <strong>[{{ round(@(($data->outcomes->dead/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/dead') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants who were Adult Sample
                                </th>
                                <td>
                                    {{ $data->outcomes->adult }}
                                    <strong>[{{ round(@(($data->outcomes->adult/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/adult') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants Transferred Out 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->transferOut) }}
                                    <strong>[{{ round(@(($data->outcomes->transferOut/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/transferout') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Infants with (Other Reasons) 
                                </th>
                                <td>
                                    {{ number_format($data->outcomes->other) }}
                                    <strong>[{{ round(@(($data->outcomes->other/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="{{ url('hei/followup/outcomes/other') }}" style="color: blue;">Click to View</a>
                                </td>
                            </tr>
                            {{--<tr>
                                <th>
                                    Infants with Other validations
                                </th>
                                <td>
                                    {{ $data->outcomes->othervalidation }}
                                    <strong>[{{ round(@(($data->outcomes->othervalidation/$data->outcomes->positives)*100),1) }}%]</strong>
                                    &nbsp;&nbsp;
                                    <a href="" style="color: blue;">Click to View</a>
                                </td>
                            </tr>--}}
                            <tr>
                                <th style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-warning">
                                        Infants NOT Documented Online 
                                    </div>
                                </th>
                                <td style="padding-top: 0px;padding-bottom: 0px;padding-right: 0px;padding-left: 0px;">
                                    <div class="alert alert-warning">
                                        {{ number_format($data->unknown) }}
                                        <strong>[{{ round(@(($data->unknown/$data->outcomes->positives)*100),1) }}%]</strong>
                                        @if($data->outcomes->unknown > 0)
                                            <a href="{{ url('hei/followup') }}" style="color: blue;">Click to View Full Listing</a>
                                        @endif
                                    </div>
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