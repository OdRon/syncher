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
@php
    $globaltesttype = $data->testtype;
    $replace = 'Quantitative';
    if($globaltesttype == 'EID')
        $replace = 'Quanlitative';
    $globaltesttypevalue = 1;
    if($globaltesttype == 'VL')
        $globaltesttypevalue = 2;
@endphp
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
            {{ Form::open(['url' => '/approveallocation', 'method' => 'post', 'class'=>'form-horizontal']) }}
            @foreach($data->allocations as $allocation)
                <div class="panel-body">
                    <div class="alert alert-info">
                        <center>Allocation for {{ $allocation->machine->machine}}, {{ $globaltesttype }}</center>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name of Commodity</th>
                                    <th>Average Monthly Consumption(AMC)</th>
                                    <th>Months of Stock(MOS)</th>
                                    <th>Ending Balance</th>
                                    <th>Recommended Quantity to Allocate (by System)</th>
                                    <th>Quantity Allocated by Lab</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $tests = $allocation->machine->testsforLast3Months()->$globaltesttype;
                                $qualamc = 0;
                            @endphp
                            @foreach($allocation->details as $detail)
                                @php
                                    $test_factor = json_decode($detail->kit->testFactor);
                                    $factor = json_decode($detail->kit->factor);
                                    if ($detail->kit->alias == 'qualkit')
                                        $qualamc = (($tests / $test_factor->$globaltesttype) / 3);

                                    if ($allocation->machine->id == 2)
                                        $amc = $qualamc * $factor->$globaltesttype;
                                    else
                                        $amc = $qualamc * $factor;

                                    $ending = 0;
                                    $consumption = $detail->kit->consumption
                                                        ->where('month', $data->last_month)->where('year', $data->last_year)
                                                        ->where('testtype', $globaltesttypevalue)->where('lab_id', $data->lab->id)
                                                        ->pluck('ending');
                                    foreach($consumption as $value) {
                                        $ending += $value;
                                    }
                                    $mos = @($ending / $amc);
                                @endphp
                                <tr>
                                    <td>{{ str_replace("REPLACE", $replace, $detail->kit->name) }}</td>
                                    <td>{{ $amc }}</td>
                                    <td>
                                    @if(is_nan($mos))
                                        {{ 0 }}
                                    @else
                                        {{ $mos }}
                                    @endif
                                    </td>
                                    <td>{{ $ending }}</td>
                                    <td>{{ ($amc * 2) - $ending }}</td>
                                    <td>{{ $detail->allocated }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Lab Allocation Comments</label>
                        <div class="col-md-8">
                            <textarea disabled class="form-control">{{ $allocation->allocationcomments }}</textarea>
                        </div>                            
                    </div>
                </div>
            @endforeach
            {{ Form::close() }}
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