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
                <a href='{{ url("hei/followup/$data->duration/$data->validation/$year") }}'>{{ Date('Y')-$i }}</a> |
            @endfor
        </div>
        <!-- Year -->
        <!-- Month -->
        <div class="col-md-6">
            <center><h5>Month Filter</h5></center>
            @for ($i = 1; $i <= 12; $i++)
                <a href='{{ url("hei/followup/$data->duration/$data->validation/null/$i") }}'>{{ date("F", mktime(null, null, null, $i)) }}</a> |
            @endfor
        </div>
        <!-- Month -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="alert alert-danger">
                    <center>* You can only update the HEIs of the infants in the present view. If you wish to increase the number, please increase the number from the drop down on the left below.</center>
                    <center>- First select the hei validation</center>
                    <center>- Then select enrollment status, Only Confirmed Positive (CP) will enable the area to enter Enrollment CCC # and Date Initiated on Treatment</center>
                    <center>- Any additional notes/comments be filled in the ‘Notes/Comments’ section</center>
                </div>
                <div class="panel-body">
                    {{ Form::open(['url' => '/hei/followup', 'method' => 'post', 'class'=>'form-horizontal']) }}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th id="check_all">UnCheck All</th>
                                    <th>#</th>
                                    <th>County</th>
                                    <th>Facility</th>
                                    <th>MFL Code</th>
                                    <th>Sample/Patient ID</th>
                                    <th>Patient DOB</th>
                                    <th>PCR Type</th>
                                    <th>Validation (CP,A,VL,RT,UF)</th>
                                    <th>Enrollment Status</th>
                                    <th>Date Initiated on Treatment</th>
                                    <th>Enrollement CCC #</th>
                                    <th>Referred to Site</th>
                                    <th>Notes/Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $count=0;
                                @endphp
                                @forelse($data->patients as $key => $sample)
                                    @php
                                        $count += 1;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input class="checks" type="checkbox" id="check{{ $count }}" value="{{ $sample->patient_id }}" checked>
                                            <input type="hidden" name="id{{ $count }}"  id="id{{ $count }}" value="{{ $sample->patient_id }}" >
                                        </td>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $sample->county }}</td>
                                        <td>{{ $sample->facility }}</td>
                                        <td>{{ $sample->facilitycode }}</td>
                                        <td>
                                            {{ $sample->patient }}
                                            <input type="hidden" name="patient{{ $count }}" id="patient{{ $count }}" value="{{ $sample->patient }}">
                                        </td>
                                        <td>
                                            {{ $sample->dob }}
                                        </td>
                                        <td>
                                            {{ $sample->pcrtype }}
                                        </td>
                                        <td>
                                            <select class="form-control" name="hei_validation{{ $count }}" id="hei_validation{{ $count }}" style="width: 150px;">
                                                @if($data->edit)
                                                    <option selected disabled value="">Select Validation</option>
                                                    @forelse($data->hei_validation as $validation)
                                                        @if($sample->hei_validation == $validation->id)
                                                            <option value="{{ $validation->id }}" selected>{{ $validation->desc }} - {{ $validation->name }}</option>
                                                        @else
                                                            <option value="{{ $validation->id }}">{{ $validation->desc }} - {{ $validation->name }}</option>
                                                        @endif
                                                    @empty
                                                        <option disabled value="">No Validation</option>
                                                    @endforelse
                                                @else
                                                    <option selected disabled value="">Select Validation</option>
                                                    @forelse($data->hei_validation as $validation)
                                                        <option value="{{ $validation->id }}">{{ $validation->desc }} - {{ $validation->name }}</option>
                                                    @empty
                                                        <option disabled value="">No Validation</option>
                                                    @endforelse
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="enrollment_status{{ $count }}" id="enrollment_status{{ $count }}" disabled style="width: 150px;">
                                            @if($data->edit)
                                               <option selected disabled value="">Select Enrollment Status</option>
                                                @forelse($data->hei_categories as $followup)
                                                    @if($followup->id == $sample->enrollment_status)
                                                        <option value="{{ $followup->id }}" selected>{{ $followup->name }}</option>
                                                    @else
                                                        <option value="{{ $followup->id }}">{{ $followup->name }}</option>
                                                    @endif
                                                @empty
                                                    <option value="">No Enrollment Status</option>
                                                @endforelse
                                            @endif 
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" class="form-control input-sm" value="{{ $sample->dateinitiatedontreatment ?? '' }}" name="dateinitiatedontreatment{{ $count }}" id="dateinitiatedontreatment{{ $count }}" @if($sample->enrollment_status != 1) disabled @else required @endif style="width: 150px;">
                                            </div>
                                        </td>
                                        <td>
                                            <input class="form-control" type="text" name="enrollment_ccc_no{{ $count }}" id="enrollment_ccc_no{{ $count }}" value="{{ $sample->enrollment_ccc_no ?? '' }}" @if($sample->enrollment_status != 1) disabled @endif>
                                        </td>
                                        <td>
                                            <select class="form-control" name="facility_id{{ $count }}" id="facility_id{{ $count }}" @if($sample->enrollment_status != 5) disabled @else required @endif style="width: 200px;">
                                                @if($data->edit)
                                                    @isset($data->facilitys[$key])
                                                        <option value="{{ $data->facilitys[$key]->id }}">{{ $data->facilitys[$key]->name }}</option>
                                                    @endisset
                                                @endif
                                            </select>
                                        </td>
                                        <td><textarea  class="form-control" name="other_reason{{ $count }}" id="other_reason{{ $count }}" value="{{ $sample->otherreason ?? '' }}"></textarea></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12"><center>No Data available</center></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($count > 0)
                        <center>
                            <button class="btn btn-success" id="saveBtn" type="submit" name="submit" disabled="true">Save Validations</button>
                        </center>
                    @endif
                    {{ Form::close() }}
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
            <script src="{{ asset('vendor/datatables/media/js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
            <!-- DataTables buttons scripts -->
            <script src="{{ asset('vendor/pdfmake/build/pdfmake.min.js') }}"></script>
            <script src="{{ asset('vendor/pdfmake/build/vfs_fonts.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js') }}"></script>
            
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

        $('.data-table').dataTable({
            // dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "bInfo" : true,
            buttons: [
                {extend: 'copy',className: 'btn-sm'},
                {extend: 'csv',title: 'Download', className: 'btn-sm'},
                {extend: 'pdf', title: 'Download', className: 'btn-sm'},
                {extend: 'print',className: 'btn-sm'}
            ]
        });

        
    @endcomponent
    <script type="text/javascript">
        /************** SET ATTRIBUTES **************/
        @php
            $initialCount = 0;
        @endphp
       @foreach($data->patients as $key => $sample)
            @php
                $initialCount += 1;
            @endphp
            checklist = $("#check{{ $initialCount }}:checked").val();
            
            if (checklist == undefined) {
                $("#id{{ $initialCount }}").attr('disabled', 'true');
                $("#patient{{ $initialCount }}").attr('disabled', 'true');
                $("#hei_validation{{ $initialCount }}").attr('disabled', 'true');
                $("#other_reason{{ $initialCount }}").attr('disabled', 'true');
            } else {
                $("#id{{ $initialCount }}").removeAttr('disabled');
                $("#patient{{ $initialCount }}").removeAttr('disabled');
                $("#hei_validation{{ $initialCount }}").removeAttr('disabled');
                $("#hei_validation{{ $initialCount }}").attr('required','true');
                $("#other_reason{{ $initialCount }}").removeAttr('disabled');
            }
        @endforeach
        /************** EVENTS **************/
        /***** CHECK ALL *****/
        $("#check_all").on('click', function(){
            var str = $(this).html();
            if(str == "Check All"){
                $(".checks").prop('checked', true);
                $(this).html("Uncheck All");
                @php
                    $checkallCount = 0;
                @endphp
                @foreach($data->patients as $key => $sample)
                    @php
                        $checkallCount += 1;
                    @endphp
                    $("#hei_validation{{ $checkallCount }}").removeAttr('disabled');
                    $("#hei_validation{{ $checkallCount }}").attr('required','true');
                    $("#other_reason{{ $checkallCount }}").removeAttr('disabled');
                    @if($sample->enrollment_status == 1)
                        $("#enrollment_status{{ $checkallCount }}").removeAttr('disabled');
                        $("#enrollment_status{{ $checkallCount }}").attr('required','true');
                        $("#dateinitiatedontreatment{{ $checkallCount }}").removeAttr('disabled');
                        $("#dateinitiatedontreatment{{ $checkallCount }}").attr('required','true');
                        $("#enrollment_ccc_no{{ $checkallCount }}").removeAttr('disabled');
                        $("#enrollment_ccc_no{{ $checkallCount }}").attr('required','true');
                    @elseif($sample->enrollment_status == 5)
                        $("#facility_id{{ $checkallCount }}").removeAttr('disabled');
                        $("#facility_id{{ $checkallCount }}").attr('required','true');
                    @endif
                @endforeach
            }
            else{
                $(".checks").prop('checked', false); 
                $(this).html("Check All");
                @php
                    $uncheckallCount = 0;
                @endphp
                @foreach($data->patients as $key => $sample)
                    @php
                        $uncheckallCount += 1;
                    @endphp
                    $("#hei_validation{{ $uncheckallCount }}").removeAttr('required');
                    $("#hei_validation{{ $uncheckallCount }}").attr('disabled','true');
                    $("#other_reason{{ $uncheckallCount }}").attr('disabled','true');
                    @if($sample->enrollment_status == 1)
                        $("#enrollment_status{{ $uncheckallCount }}").removeAttr('required');
                        $("#enrollment_status{{ $uncheckallCount }}").attr('disabled','true');
                        $("#dateinitiatedontreatment{{ $uncheckallCount }}").removeAttr('required');
                        $("#dateinitiatedontreatment{{ $uncheckallCount }}").attr('disabled','true');
                        $("#enrollment_ccc_no{{ $uncheckallCount }}").removeAttr('required');
                        $("#enrollment_ccc_no{{ $uncheckallCount }}").attr('disabled','true');
                    @elseif($sample->enrollment_status == 5)
                        $("#facility_id{{ $uncheckallCount }}").removeAttr('required');
                        $("#facility_id{{ $uncheckallCount }}").attr('disabled','true');
                    @endif
                @endforeach         
            }
        });

        /***** CHECK ONE *****/
        $(".checks").click(function(){
            @php
                $checkCount = 0;
            @endphp
            @foreach($data->patients as $key => $sample)
                @php
                    $checkCount += 1;
                @endphp
                checklist = $("#check{{ $checkCount }}:checked").val();
                if (checklist == undefined) {
                    $("#id{{ $checkCount }}").attr('disabled', 'true');
                    $("#patient{{ $checkCount }}").attr('disabled', 'true');
                    $("#hei_validation{{ $checkCount }}").attr('disabled', 'true');
                    $("#enrollment_status{{ $checkCount }}").attr('disabled','true');
                    $("#dateinitiatedontreatment{{ $checkCount }}").attr('disabled','true');
                    $("#enrollment_ccc_no{{ $checkCount }}").attr('disabled','true');
                    $("#facility_id{{ $checkCount }}").attr('disabled','true');
                    $("#other_reason{{ $checkCount }}").attr('disabled','true');
                } else {
                    $("#id{{ $checkCount }}").removeAttr('disabled');
                    $("#patient{{ $checkCount }}").removeAttr('disabled');
                    $("#hei_validation{{ $checkCount }}").removeAttr('disabled');
                    $("#hei_validation{{ $checkCount }}").attr('required','true');
                    $("#other_reason{{ $checkCount }}").removeAttr('disabled');
                    @if($sample->hei_validation == 1)
                        $("#enrollment_status{{ $checkCount }}").removeAttr('disabled');
                        $("#enrollment_status{{ $checkCount }}").attr('required','true');
                    @endif
                    @if($sample->enrollment_status == 1)
                        $("#dateinitiatedontreatment{{ $checkCount }}").removeAttr('disabled');
                        $("#dateinitiatedontreatment{{ $checkCount }}").attr('required','true');
                        $("#enrollment_ccc_no{{ $checkCount }}").removeAttr('disabled');
                        $("#enrollment_ccc_no{{ $checkCount }}").attr('required','true');
                    @endif
                    @if($sample->enrollment_status == 5)
                        $("#facility_id{{ $checkCount }}").removeAttr('disabled');
                        $("#facility_id{{ $checkCount }}").attr('required','true');
                    @endif
                }
            @endforeach
        });

        /***** DROPDOWNS EVENTS *****/
        @php
            $count=0;
        @endphp
        @foreach($data->patients as $sample)
            @php
                $count += 1;
            @endphp
            $("#hei_validation{{ $count }}").change(function(){
                val = $(this).val();
                $("#enrollment_status{{ $count }}").html("");
                html = "<option value='' disabled selected>Select Hei Categry</option>";
                if (val == 1) {
                    $("#enrollment_status{{ $count }}").removeAttr('disabled');
                    $("#enrollment_status{{ $count }}").attr('required','true');
                    @foreach($data->hei_categories as $category)
                        html += "<option value='{{ $category->id }}'>{{ $category->name }}</option>";
                    @endforeach
                    $("#enrollment_status{{ $count }}").html(html);
                } else {
                    $("#enrollment_status{{ $count }}").removeAttr('required');
                    $("#enrollment_status{{ $count }}").attr('disabled','true');
                    $("#enrollment_status{{ $count }}").html("");
                    $("#enrollment_status{{ $count }}").val("");
                }

                $("#saveBtn").removeAttr('disabled');
            });

            $("#enrollment_status{{ $count }}").change(function(){
                val = $(this).val();
                if (val==1) {
                    $("#dateinitiatedontreatment{{ $count }}").removeAttr('disabled');
                    $("#enrollment_ccc_no{{ $count }}").removeAttr('disabled');
                    $("#dateinitiatedontreatment{{ $count }}").attr('required','true');
                    $("#enrollment_ccc_no{{ $count }}").attr('required','true');
                } else {
                    $("#dateinitiatedontreatment{{ $count }}").removeAttr('required');
                    $("#enrollment_ccc_no{{ $count }}").removeAttr('required');
                    $("#dateinitiatedontreatment{{ $count }}").attr('disabled','true');
                    $("#enrollment_ccc_no{{ $count }}").attr('disabled','true');
                    $("#dateinitiatedontreatment{{ $count }}").val("");
                    $("#enrollment_ccc_no{{ $count }}").val("");
                    
                    if (val == 5) { //Transferred out
                        $("#facility_id{{ $count }}").removeAttr('disabled');
                        $("#facility_id{{ $count }}").attr('required','true');
                        set_select_facility("facility_id{{ $count }}", "{{ url('/facility/search') }}", 3, "Search for facility", false);
                    } else {
                        $("#facility_id{{ $count }}").removeAttr('required');
                        $("#facility_id{{ $count }}").attr('disabled','true');
                        $("#facility_id{{ $count }}").html("");
                    }
                    
                    // if (val == 6) { //Other Reason
                    //     $("#other_reason{{ $count }}").removeAttr('disabled');
                    //     $("#other_reason{{ $count }}").attr('required','true');
                    // } else {
                    //     $("#other_reason{{ $count }}").removeAttr('required');
                    //     $("#other_reason{{ $count }}").attr('disabled','true');
                    //     $("#other_reason{{ $count }}").val("");
                    // }
                }
            });
        @endforeach

        
    </script>
   
@endsection