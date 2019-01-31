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
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Allocation Month</th>
                                    <th>Labs Allocated</th>
                                    <th>Allocation Status</th>
                                    <th>Labs Allocations Approved</th>
                                    <th>Approval Satus</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($allocations_data as $key => $allocation)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date("F", mktime(null, null, null, $allocation->month)) }}, {{ $allocation->year }}</td>
                                    <td>{{ $allocation->allocated_labs }} Out of {{ $allocation->all_labs }}</td>
                                    <td>
                                    @if($allocation->allocated_labs < $allocation->all_labs)
                                        Incomplete
                                    @elseif($allocation->allocated_labs > $allocation->all_labs)
                                        Erroneous Data
                                    @else
                                        Complete
                                    @endif
                                    </td>
                                    <td>{{ $allocation->approved_labs}} Out of {{ $allocation->all_labs }}</td>
                                    <td>
                                    @if($allocation->approved_labs < $allocation->all_labs)
                                        Incomplete
                                    @elseif($allocation->approved_labs > $allocation->all_labs)
                                        Erroneous Data
                                    @else
                                        Complete
                                    @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('viewallocation/'.$allocation->testtype.'/'.$allocation->year.'/'.$allocation->month) }}">View</a> 
                                    @if($allocation->approved_labs == $allocation->all_labs)
                                        | <a href="">Download</a>
                                    @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><center>No Allocation Data Available</center></td></tr>
                            @endforelse
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