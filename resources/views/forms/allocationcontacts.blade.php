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
        
    </style>
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
        {{ Form::open(['url' => '/labcontacts/' . $contact->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
            <div class="hpanel">
                <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                    <center>Lab Information</center>
                </div>
                <div class="panel-body" style="padding-bottom: 6px;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Lab Name</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="name" id="name" type="text" value="{{ $contact->lab->labdesc ?? 'KENYA MEDICAL and SUPPLIES AUTHORITY' }}" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Address</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="address" id="address" type="text" value="{{ $contact->address ?? '' }}" >
                        </div>
                    </div>
                </div>           
            </div>
            <div class="hpanel">
                <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                    <center>First Contact Person</center>
                </div>
                <div class="panel-body" style="padding-bottom: 6px;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Name:</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="contact_person" id="contact_person" type="text" value="{{ $contact->contact_person ?? '' }}" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Telephone:</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="telephone" id="telephone" type="text" value="{{ $contact->telephone ?? '' }}" >
                        </div>
                    </div>
                </div>
            </div>
            <div class="hpanel">
                <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                    <center>Second Contact Person</center>
                </div>
                <div class="panel-body" style="padding-bottom: 6px;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Name:</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="contact_person_2" id="contact_person_2" type="text" value="{{ $contact->contact_person_2 ?? '' }}" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Telephone:</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="telephone_2" id="telephone_2" type="text" value="{{ $contact->telephone_2 ?? '' }}" >
                        </div>
                    </div>
                </div>
            </div>
            <center>
                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 2em;margin-bottom: 2em; width: 200px; height: 30px;">Save Lab Allocation Contacts</button>
            </center>
        {{ Form::close() }}
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
                
    </script>
   
@endsection