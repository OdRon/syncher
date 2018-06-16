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
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    {{ Form::open(['url' => '/hei/followup', 'method' => 'post', 'class'=>'form-horizontal']) }}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th class="checklist">Check</th>
                                    <th>#</th>
                                    <th>County</th>
                                    <th>Facility</th>
                                    <th>MFL Code</th>
                                    <th>Sample/Patient ID</th>
                                    <th>Date Collected</th>
                                    <th>Date Tested</th>
                                    <th>Validation (CP,A,VL,RT,UF)</th>
                                    <th>Enrollment Status</th>
                                    <th>Date Initiated on Treatment</th>
                                    <th>Enrollement CCC #</th>
                                    <th>Referred to Site</th>
                                    <th>Other Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $count=0;
                                @endphp
                                @forelse($data->samples as $sample)
                                    @php
                                        $count += 1;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input class="i-checks" type="checkbox" name="id{{ $count }}" id="id{{ $count }}" value="{{ $sample->id }}" checked>
                                        </td>
                                        <td>{{ $count }}</td>
                                        <td>{{ $sample->county }}</td>
                                        <td>{{ $sample->name }}</td>
                                        <td>{{ $sample->facilitycode }}</td>
                                        <td>
                                            {{ $sample->patient }}
                                            <input type="hidden" name="patient{{ $count }}" value="{{ $sample->patient }}">
                                        </td>
                                        <td>{{ $sample->datecollected }}</td>
                                        <td>{{ $sample->datetested }}</td>
                                        <td>
                                            <select class="form-control" name="hei_validation{{ $count }}" id="hei_validation{{ $count }}" required style="width: 150px;">
                                                <option selected disabled value="">Select Validation</option>
                                                @forelse($data->hei_validation as $validation)
                                                    <option value="{{ $validation->id }}">{{ $validation->desc }} - {{ $validation->name }}</option>
                                                @empty
                                                    <option disabled value="">No Validation</option>
                                                @endforelse
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="enrollment_status{{ $count }}" id="enrollment_status{{ $count }}" disabled style="width: 150px;"></select>
                                        </td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" class="form-control input-sm" value="" name="dateinitiatedontreatment{{ $count }}" id="dateinitiatedontreatment{{ $count }}" disabled  style="width: 150px;">
                                            </div>
                                        </td>
                                        <td>
                                            <input class="form-control" type="text" name="enrollment_ccc_no{{ $count }}" id="enrollment_ccc_no{{ $count }}" disabled>
                                        </td>
                                        <td>
                                            <select class="form-control" name="facility_id{{ $count }}" id="facility_id{{ $count }}" disabled style="width: 200px;"></select>
                                        </td>
                                        <td><textarea  class="form-control" name="other_reason{{ $count }}" id="other_reason{{ $count }}" disabled></textarea></td>
                                    </tr>
                                @empty
                                    <tr><td><center>No Data available</center></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($count > 0)
                        <center><button class="btn btn-success" type="submit" name="submit">Save Initiation Dates</button></center>
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
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            buttons: [
                {extend: 'copy',className: 'btn-sm'},
                {extend: 'csv',title: 'Download', className: 'btn-sm'},
                {extend: 'pdf', title: 'Download', className: 'btn-sm'},
                {extend: 'print',className: 'btn-sm'}
            ]
        });

        $(".checklist").click(function(){
            
        });

        @php
            $count=0;
        @endphp
        @foreach($data->samples as $sample)
            @php
                $count += 1;
            @endphp
            $("#hei_validation{{ $count }}").change(function(){
                val = $(this).val();
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
                    
                    if (val == 6) { //Other Reason
                        $("#other_reason{{ $count }}").removeAttr('disabled');
                        $("#other_reason{{ $count }}").attr('required','true');
                    } else {
                        $("#other_reason{{ $count }}").removeAttr('required');
                        $("#other_reason{{ $count }}").attr('disabled','true');
                        $("#other_reason{{ $count }}").val("");
                    }
                }
            });
        @endforeach

    @endcomponent
   
@endsection