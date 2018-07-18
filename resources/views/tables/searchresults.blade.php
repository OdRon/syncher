@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<style type="text/css">
    .spacing-div-form {
        margin-top: 15px;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#eid-results"><strong>A.) EID RESULTS</strong></a></li>
                    <li class=""><a data-toggle="tab" href="#vl-results"><strong>B.) VL RESULTS</strong></a></li>
                </ul>
                <div class="tab-content">
                    <div id="eid-results" class="tab-pane active">
                        <div class="panel-body">
                            <div class="alert alert-warning">
                                EARLY INFANT DIAGNOSIS
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="eidTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">#</th>
                                            <th rowspan="2">Patient ID</th>
                                            <th rowspan="2">Facility</th>
                                            <th rowspan="2">Lab Tested In</th>
                                            <th rowspan="2">Batch No</th>
                                            <th rowspan="2">Received Status</th>
                                            <th colspan="4"><center>Date</center></th>
                                            <th rowspan="2">Result</th>
                                            <th rowspan="2">Action</th>
                                        </tr>
                                        <tr> 
                                            <th>Collected</th>
                                            <th>Received</th>
                                            <th>Tested</th>
                                            <th>Dispatched</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="vl-results" class="tab-pane">
                        <div class="panel-body">
                            <div class="alert alert-warning">
                                VIRAL LOAD
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover data-table" >
                                    <thead>
                                        <tr>
                                            <th rowspan="2">#</th>
                                            <th rowspan="2">Patient ID</th>
                                            <th rowspan="2">Facility</th>
                                            <th rowspan="2">Lab Tested In</th>
                                            <th rowspan="2">Batch No</th>
                                            <th rowspan="2">Received Status</th>
                                            <th colspan="4"><center>Date</center></th>
                                            <th rowspan="2">Result</th>
                                            <th rowspan="2">Action</th>
                                        </tr>
                                        <tr> 
                                            <th>Collected</th>
                                            <th>Received</th>
                                            <th>Tested</th>
                                            <th>Dispatched</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
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
    <script type="text/javascript">
        $(document).ready(function(){
            $('#eidTable').dataTable( {
                dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
                    "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
                buttons: [
                    {extend: 'copy',className: 'btn-sm'},
                    {extend: 'csv',title: 'Download', className: 'btn-sm'},
                    {extend: 'pdf', title: 'Download', className: 'btn-sm'},
                    {extend: 'print',className: 'btn-sm'}
                ],
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route('eidresults') }}",
                "columns": [
                    {
                        "orderable":      false,
                        "name":           "no"
                    },
                    { "name": "patient" },
                    { "name": "facility" },
                    { "name": "lab" },
                    { "name": "batch" },
                    { "name": "received_status" },
                    { 
                        "name": "date_collected", 
                        "searchable":     false  
                    },
                    { 
                        "name": "date_received", 
                        "searchable":     false  
                    },
                    { 
                        "name": "date_tested", 
                        "searchable":     false  
                    },
                    { 
                        "name": "date_dispatched", 
                        "searchable":     false  
                    },
                    { "name": "result" },
                    { 
                        "name": "action",
                        "orderable":      false, 
                        "searchable":     false 
                    }
                ],
                "order": [[ 8, "desc" ]]
            });
        });
    </script>

@endsection
