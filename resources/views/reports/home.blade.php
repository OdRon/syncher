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
<div class="p-lg">
    <div class="content animate-panel reports" data-child="hpanel">
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="alert alert-success">
                        <center>Test Outcome Report [ All Tested Samples ]</center>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            <center>Please select Overall <strong>or Province or County or District or Facility & Period To generate the report based on your criteria.</strong></center>
                        </div>
                        {{ Form::open(['url'=>'/reports', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'reports_form']) }}
                        <input type="hidden" name="testtype" value="{{ $testtype }}">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks" value="overall">Overall
                                </label>
                                <div class="col-sm-9">
                                    << For all samples tested in Lab >>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="county" class="i-checks">Select County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="county" id="county">
                                        <option value="" selected disabled>Select County</option>
                                    @forelse($countys as $county)
                                        <option value="{{ $county->county_id }}">{{ $county->county }}</option>
                                    @empty
                                        <option value="" disabled>No County available</option>
                                    @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="subcounty" class="i-checks">Select Sub County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="district" id="district">
                                        <option value="" selected disabled>Select Sub-County</option>
                                    @forelse($subcountys as $subcounty)
                                        <option value="{{ $subcounty->subcounty_id }}">{{ $subcounty->subcounty }}</option>
                                    @empty
                                        <option value="" disabled>No Sub-County available</option>
                                    @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="facility" class="i-checks">Select Facility
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="facility" id="facility">
                                        <option value="" selected disabled>Select Facility</option>
                                    @forelse($facilitys as $facility)
                                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                    @empty
                                        <option value="" disabled>No Facility available</option>
                                    @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Period</label>
                            <div class="col-sm-10">
                                <!-- <select class="form-control" id="period">
                                    <option selected="true" disabled="true">Select Time Frame</option>
                                    <option value="weekly">Date Range</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annually">Annually</option>
                                </select> -->
                                <label> <input type="radio" name="period" value="range"> Date Range </label>
                                <label> <input type="radio" name="period" value="monthly"> Monthly </label>
                                <label> <input type="radio" name="period" value="quarterly"> Quarterly </label>
                                <label> <input type="radio" name="period" value="annually"> Annually </label>
                            </div>
                            <div class="row" id="periodSelection" style="display: none;">
                                <div class="col-md-12" id="rangeSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Select Date Range From: </td>
                                                <td>
                                                    <div class="input-group date">
                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                        <input type="text" id="fromDateCat" class="form-control lockable" name="fromDate">
                                                    </div>
                                                </td>
                                                <td><center>To:</center></td>
                                                <td>
                                                    <div class="input-group date">
                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                        <input type="text" id="toDateCat" class="form-control lockable" name="toDate">
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12" id="monthSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Select Year and Month </td>
                                                <td>
                                                    <select class="form-control" id="year" name="year">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 6; $i >= 0; $i--)
                                                            @php
                                                                $year=Date('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="month" name="month">
                                                        <option selected="true" disabled="true">Select a Month</option>
                                                        @for ($i = 1; $i <= 12; $i++)
                                                            <option value="{{ $i }}">{{ date("F", mktime(null, null, null, $i)) }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                                <div class="col-md-12" id="quarterSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Select Year and Quarter </td>
                                                <td>
                                                    <select class="form-control" id="year" name="year">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 6; $i >= 0; $i--)
                                                            @php
                                                                $year=Date('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="quarter" name="quarter">
                                                        <option selected="true" disabled="true">Select a Quarter</option>
                                                        @for ($i = 1; $i <= 4; $i++)
                                                            <option value="Q{{ $i }}">Q{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                                <div class="col-md-12" id="yearSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Select Year </td>
                                                <td>
                                                    <select class="form-control" id="year" name="year">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 6; $i >= 0; $i--)
                                                            @php
                                                                $year=Date('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Report Type</label>
                            <div class="col-sm-9">
                            @if($testtype == 'EID')
                                <label> <input type="radio" name="indicatortype" value="1" class="i-checks"> All Outcomes (+/-) </label>
                                <label> <input type="radio" name="indicatortype" value="2" class="i-checks"> + Outcomes </label>
                                <label> <input type="radio" name="indicatortype" value="3" class="i-checks"> + Outcomes for Follow Up </label>
                                <label> <input type="radio" name="indicatortype" value="4" class="i-checks"> - Outcomes for Validation </label>
                                <label> <input type="radio" name="indicatortype" value="5" class="i-checks"> Rejected Samples </label>
                                <label> <input type="radio" name="indicatortype" value="6" class="i-checks"> Patients <2M </label>
                                <label> <input type="radio" name="indicatortype" value="7" class="i-checks"> High + Burden Sites </label>
                                <label> <input type="radio" name="indicatortype" value="8" class="i-checks"> RHT Testing </label>
                                <label> <input type="radio" name="indicatortype" value="9" class="i-checks"> Dormant Sites ( Not Sent Samples) </label>
                                <label> <input type="radio" name="indicatortype" value="10" class="i-checks"> Sites Doing Remote Data Entry of Samples </label>
                            @elseif($testtype == 'VL')
                                <label> <input type="radio" name="indicatortype" value="2" class="i-checks">Detailed</label>
                                <label> <input type="radio" name="indicatortype" value="3" class="i-checks">Rejected</label>
                                <label> <input type="radio" name="indicatortype" value="4" class="i-checks"> Non Suppressed ( > 1000 cp/ml)</label>
                                <label> <input type="radio" name="indicatortype" value="6" class="i-checks"> Pregnant & Lactating</label>
                                <label> <input type="radio" name="indicatortype" value="7" class="i-checks"> Dormant Sites ( Not Sent Samples)</label>
                                <label> <input type="radio" name="indicatortype" value="10" class="i-checks"> Sites Doing Remote Data Entry of Samples</label>
                            @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <center>
                                <button type="submit" class="btn btn-default" id="generate_report">Generate Report</button>
                                <button class="btn btn-default">Reset Options</button>
                            </center>
                        </div>                  
                        {{ Form::close() }}
                    </div>
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
            format: "yyyy-mm-dd"
        });

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
    <script type="text/javascript">
        $(document).ready(function(){
            // $('.period').click(function(){
            $('input[name="period"]').change(function(){
                period = $(this).val();
                $('#periodSelection').show();
                $('#rangeSelection').hide();
                $('#monthSelection').hide();
                $('#quarterSelection').hide();
                $('#yearSelection').hide();
                if (period == 'range') {
                    $('#rangeSelection').show();
                } else if (period == 'monthly') {
                    $('#monthSelection').show();
                } else if (period == 'quarterly') {
                    $('#quarterSelection').show();
                } else if (period == 'annually') {
                    $('#yearSelection').show();
                }
            });

            $("#generate_report").click(function(e){
                var selValue = $('input[name=category]:checked').val();
                if (selValue == 'county') {
                    category = $("#county").val();
                    cat = 'County';
                } else if (selValue == 'subcounty') {
                    category = $("#district").val();
                    cat = 'Sub-County';
                } else if (selValue == 'facility') {
                    category = $("#facility").val();
                    cat = 'Facility';
                }

                if(category == '' || category == null || category == undefined) {
                    e.preventDefault();
                    set_warning("No "+cat+" Selected</br /></br />Please Select a "+cat+" from the dropdown");
                }

                // var perValue = $('input[name=period]:checked').val();
                // alert(perValue);
                // var $radios = $('input[name="period"]');
            });
        });
    </script>
@endsection