@extends('layouts.master')

@component('/forms/css')
    
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
        <div class="col-lg-6">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Reports</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($categroies as $key => $category)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                    @foreach($category->reports as $report)
                                        <span class="label label-info">{{ $report->name }}</span> 
                                    @endforeach
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="hpanel">
                <div class="panel-body">
                    @include('forms.setup')
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-head">
                    <center><h5>Reports Permissions</h5></center>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Report</th>
                                    <th>Test type</th>
                                    <th>User Types</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($reports as $key => $report)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $report->name }}</td>
                                    <td>
                                    @if($report->testtype == 1)
                                        <label class="label label-primary">EID</label>
                                    @elseif($report->testtype == 2)
                                        <label class="label label-info">VL</label>
                                    @endif
                                    </td>
                                    <td>
                                    @foreach($report->user_types() as $user_type)
                                        <span class="label label-info">{{ $user_type->user_type }}</span> 
                                    @endforeach
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
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
        
    @endcomponent
    <script type="text/javascript">
                
    </script>
   
@endsection
