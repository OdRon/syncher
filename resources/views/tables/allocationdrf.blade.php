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
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lab</th>
                                    <th>DRF Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($labs as $key => $lab)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $lab->labdesc }}</td>                                    
                                    @php
                                        $pending = 0;
                                        $complete = 0;
                                        if (!$lab->allocations->isEmpty()) {
                                            foreach($lab->allocations as $allocation) {
                                                if (($allocation->details->where('approve', 0)->count() > 0) || ($allocation->details->where('approve', 2)->count() > 0))
                                                    $pending ++;
                                                if($allocation->details->where('approve', 1)->count() > 0)
                                                    $complete ++;
                                            }
                                        }
                                    @endphp
                                    <td>
                                    @if(($pending == 0) && ($complete > 0))
                                        <span class="label label-success">Available</span>
                                    @else
                                        <span class="label label-warning">Unavailable</span>
                                    @endif
                                    </td>
                                    <td>
                                    @if(($pending == 0) && ($complete > 0))
                                        <a href="{{ url('allocationdrfs/'.$lab->id) }}" class="btn btn-info">Generate DRF</a>
                                    @else
                                        DRF Not ready for download
                                    @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><center>No Allocation Data Available</center></td></tr>
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