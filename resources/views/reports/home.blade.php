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
                    @if(Auth::user()->user_type_id == 9)
                        <center>Report Outcomes</center>
                    @else
                        <center>Test Outcome Report [ All Tested Samples ]</center>
                    @endif
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            <center>
                                Please select Overall 
                                <strong>
                                @if(Auth::user()->user_type_id == 9 || Auth::user()->user_type_id == 10 || !(Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 5))
                                    @if(Auth::user()->user_type_id != 7 || Auth::user()->level == 85)
                                        or County 
                                    @endif
                                @endif
                                @if(!(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 5 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7))
                                    or Sub-County 
                                @endif
                                @if(Auth::user()->user_type_id != 6)
                                    or Facility  
                                @endif
                                @if(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 10)
                                    or Partner  
                                @endif
                                @if(Auth::user()->user_type_id == 9 || Auth::user()->user_type_id == 10)
                                    or Lab 
                                @endif
                                    & Period To generate the report based on your criteria.
                                </strong></center>
                        </div>
                        {{ Form::open(['url'=>'/reports', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'reports_form']) }}
                        <input type="hidden" name="testtype" value="{{ $testtype }}">
                        <div class="form-group">
                            @if(!(Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 2))
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks" value="overall" required>Overall
                                </label>
                                <div class="col-sm-9">
                                    @if(Auth::user()->user_type_id == 9)
                                        << By County / Partner / Lab >>
                                    @else
                                        << For all Sites Under {{ $user->name ?? '' }} >>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if(Auth::user()->user_type_id == 9 || Auth::user()->user_type_id == 10)
                                <div class="row">
                                    <label class="col-sm-3 control-label">
                                        <input type="radio" name="category" value="lab" class="i-checks" required>Select Lab
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="lab" id="lab">
                                            <option value="" selected disabled>Select Lab</option>
                                        @forelse($labs as $lab)
                                            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                        @empty
                                            <option value="" disabled>No Lab available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if(Auth::user()->user_type_id != 9)
                                @if(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 10)
                                    <div class="row">
                                        <label class="col-sm-3 control-label">
                                            <input type="radio" name="category" value="partner" class="i-checks" required>Select Partner
                                        </label>
                                        <div class="col-sm-9">
                                            <select class="form-control" name="partner" id="partner">
                                                <option value="" selected disabled>Select Partner</option>
                                            @forelse($partners as $partner)
                                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                            @empty
                                                <option value="" disabled>No Partner available</option>
                                            @endforelse
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                @if(!(Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 5))
                                    @if(Auth::user()->user_type_id != 7 || Auth::user()->level == 85)
                                    <div class="row">
                                        <label class="col-sm-3 control-label">
                                            <input type="radio" name="category" value="county" class="i-checks" required>Select County
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
                                    @endif
                                @endif
                                @if(!(Auth::user()->user_type_id == 5 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7))
                                    <div class="row">
                                        <label class="col-sm-3 control-label">
                                            <input type="radio" name="category" value="subcounty" class="i-checks" required>Select Sub County
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
                                @endif
                                @if(Auth::user()->user_type_id != 6)
                                <div class="row">
                                    <label class="col-sm-3 control-label">
                                        <input type="radio" name="category" value="facility" class="i-checks" required>Select Facility
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="facility" id="facility">
                                            <option value="" selected disabled>Select Facility</option>
                                        @forelse($facilitys as $facility)
                                            <option value="{{ $facility->id }}">{{ $facility->name }} ({{ $facility->county }})</option>
                                        @empty
                                            <option value="" disabled>No Facility available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                @endif
                            @endif
                            <div class="row">
                                    <label class="col-sm-3 control-label">
                                        <input type="radio" name="category" value="poc" class="i-checks" required>POC
                                    </label>
                                    <div class="col-sm-9">
                                        << For all POC Sites >>
                                    </div>
                                </div>
                        </div>
                        @if(Auth::user()->user_type_id == 9)
                        <hr>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                <strong>Selection Level</strong>
                            </label>
                            <div class="col-sm-9">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="level" value="counties" class="i-checks">By Counties
                                </label>
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="level" value="partners" class="i-checks">By Partners
                                </label>
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="level" value="subcounties" class="i-checks">By Sub-Counties
                                </label>
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="level" value="facility" class="i-checks">By Facilities
                                </label>
                            </div>
                        </div>
                        <hr>
                        @endif
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
                                @if(Auth::user()->user_type_id != 9)
                                <label> <input type="radio" name="period" value="range" required> Date Range </label>
                                @endif
                                <label> <input type="radio" name="period" value="monthly" required> Monthly </label>
                                <label> <input type="radio" name="period" value="quarterly" required> Quarterly </label>
                                @if($testtype == 'EID' || Auth::user()->user_type_id == 9 || Auth::user()->user_type_id == 10)
                                @if($testtype == 'EID' || Auth::user()->user_type_id != 7)
                                <label> <input type="radio" name="period" value="annually" required> Annually </label>
                                @endif
                                @endif
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
                                                    <select class="form-control" id="year" name="year" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 0; $i <= 15; $i++)
                                                            @php
                                                                $year=Date('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="month" name="month" style="width: 100%;">
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
                                                    <select class="form-control" id="year" name="year" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 0; $i <= 15; $i++)
                                                            @php
                                                                $year=Date('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="quarter" name="quarter" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Quarter</option>
                                                        @for ($i = 1; $i <= 4; $i++)
                                                            <option value="Q{{ $i }}">
                                                                Q{{ $i }}
                                                                @if($i==1)
                                                                    &nbsp;(Jan - Mar)
                                                                @elseif($i==2)
                                                                    &nbsp;(Apr - Jun)
                                                                @elseif($i==3)
                                                                    &nbsp;(Jul - Sep)
                                                                @else
                                                                    &nbsp;(Oct - Dec)
                                                                @endif
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                                @if($testtype == 'EID' || Auth::user()->user_type_id == 9 || Auth::user()->user_type_id == 10)
                                <div class="col-md-12" id="yearSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Select Year </td>
                                                <td>
                                                    <select class="form-control" id="year" name="year" style="width: 70%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 0; $i <= 15; $i++)
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
                                @endif
                            </div>
                        </div> 
                        @if($testtype == 'VL' && (Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 10))
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Age Group</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="age" name="age" style="width: 70%;">
                                    <option value="1" selected>All ages</option>
                                    <option value="2">Less than 2</option>
                                    <option value="3">2-9</option>
                                    <option value="4">10-14</option>
                                    <option value="5">15-19</option>
                                    <option value="6">20-24</option>
                                    <option value="7">25+</option>
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Report Type</label>
                            <div class="col-sm-9">
                            @if($testtype == 'EID')
                                <label> <input type="radio" name="indicatortype" value="1" class="i-checks" required> All Outcomes (+/-) </label>
                                <label> <input type="radio" name="indicatortype" value="2" class="i-checks" required> + Outcomes </label>
                                @if(!(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7))
                                <label> <input type="radio" name="indicatortype" value="3" class="i-checks" required> + Outcomes for Follow Up </label>
                                @endif
                                @if(Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 10)
                                    <label> <input type="radio" name="indicatortype" value="4" class="i-checks" required> - Outcomes </label>
                                @endif
                                <label> <input type="radio" name="indicatortype" value="5" class="i-checks" required> Rejected Samples </label>
                                @if(!(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 7))
                                <label> <input type="radio" name="indicatortype" value="6" class="i-checks" required> Patients <= 2M </label>
                                    @if(!(Auth::user()->user_type_id == 6))
                                    <label> <input type="radio" name="indicatortype" value="7" class="i-checks" required> High + Burden Sites </label>
                                    @endif
                                @endif
                                @if(Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 10)
                                    @if(Auth::user()->user_type_id != 2)
                                        <!-- <label> <input type="radio" name="indicatortype" value="8" class="i-checks"> RHT Testing </label> -->
                                        <label> <input type="radio" name="indicatortype" value="9" class="i-checks" required> Dormant Sites ( Not Sent Samples) </label>
                                        <label> <input type="radio" name="indicatortype" value="10" class="i-checks" required>Site Entry Samples</label>
                                    @endif
                                @endif
                            @elseif($testtype == 'VL')
                                <label><input type="radio" name="indicatortype" value="2" class="i-checks" required>Detailed</label>
                                <label><input type="radio" name="indicatortype" value="5" class="i-checks" required>Rejected</label>
                                @if(Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 10)
                                    <label><input type="radio" name="indicatortype" value="4" class="i-checks" required>Non Suppressed ( > 1000 cp/ml)</label>
                                    <label><input type="radio" name="indicatortype" value="6" class="i-checks" required>Pregnant & Lactating</label>
                                    <label><input type="radio" name="indicatortype" value="9" class="i-checks" required>Dormant Sites ( Not Sent Samples)</label>
                                    <label><input type="radio" name="indicatortype" value="10" class="i-checks" required>Site Entry Samples</label>
                                @endif
                                @if(Auth::user()->user_type_id == 10)
                                    <label><input type="radio" name="indicatortype" value="17" class="i-checks" required>Test Outcomes</label>
                                @endif
                            @elseif($testtype == 'support' || Auth::user()->user_type_id == 10)
                                {{--
                                <label><input type="radio" name="indicatortype" value="11" class="i-checks" required> EID Remote Log in Report</label>
                                <label><input type="radio" name="indicatortype" value="12" class="i-checks" required> VL Remote Log in Report</label>
                                <label><input type="radio" name="indicatortype" value="14" class="i-checks" required>EID Sample Referral Network</label>
                                <label><input type="radio" name="indicatortype" value="15" class="i-checks" required>VL Sample Referral Network</label>
                                <label><input type="radio" name="indicatortype" value="16" class="i-checks" required>VL Outcomes by Platform</label>
                                <label><input type="radio" name="indicatortype" value="19" class="i-checks" required>EID No Data Summary</label>
                                <label><input type="radio" name="indicatortype" value="20" class="i-checks" required>VL No Data Summary</label>
                                --}}
                                <label><input type="radio" name="indicatortype" value="22" class="i-checks" required>VL Utilization & Outcomes</label>
                                <label><input type="radio" name="indicatortype" value="18" class="i-checks" required>Low Level Viremia (LLV)</label>
                                <label><input type="radio" name="indicatortype" value="21" class="i-checks" required>Lab Tracker</label>
                                <label><input type="radio" name="indicatortype" value="13" class="i-checks" required>Quarterly VL Report (only for labs)</label>
                                <label><input type="radio" name="indicatortype" value="14" class="i-checks" required>EID Sample Referral Network</label>
                                <label><input type="radio" name="indicatortype" value="15" class="i-checks" required>VL Sample Referral Network</label>
                            @endif
                            <br />
                            @if(Auth::user()->user_type_id == 10)
                                <hr>
                                <h4>Maryland Support Reports</h4>
                                <hr>
                                <br>
                                <label><input type="radio" name="indicatortype" value="11" class="i-checks" required> EID Remote Log in Report</label>
                                <label><input type="radio" name="indicatortype" value="12" class="i-checks" required> VL Remote Log in Report</label>
                                <label><input type="radio" name="indicatortype" value="14" class="i-checks" required>EID Sample Referral Network</label>
                                <label><input type="radio" name="indicatortype" value="15" class="i-checks" required>VL Sample Referral Network</label>
                                <label><input type="radio" name="indicatortype" value="13" class="i-checks" required>Quarterly VL Report (only for labs)</label>
                                <label><input type="radio" name="indicatortype" value="16" class="i-checks" required>VL Outcomes by Platform</label>
                                <label><input type="radio" name="indicatortype" value="18" class="i-checks" required>Low Level Viremia</label>
                                <label><input type="radio" name="indicatortype" value="19" class="i-checks" required>EID No Data Summary</label>
                                <label><input type="radio" name="indicatortype" value="20" class="i-checks" required>VL No Data Summary</label>
                            @endif
                            <!-- Highest value 18 -->
                            </div>
                        </div>

                        <div class="form-group">
                            <center>
                                <div class="alert alert-warning">
                                    Reports are currntly unavailable. We apologise for the inconvinience caused. We are working as fast as possible to ensure they are back online. Thank you for your patience.
                                </div>
                                <!-- <button type="submit" class="btn btn-default" id="generate_report">Generate Report</button>
                                <button class="btn btn-default">Reset Options</button> -->
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
                } else if (selValue == 'partner') {
                    category = $("#partner").val();
                    cat = 'Partner';
                } else if (selValue == 'lab') {
                    category = $("#lab").val();
                    cat = 'Lab';
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