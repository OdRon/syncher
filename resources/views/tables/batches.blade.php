@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                                              
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr> 
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Batch No</th>
                                    <th rowspan="2">Date Received</th>
                                    <th colspan="3"><center>Action<center></th>
                                </tr>
                                <tr>
                                    <th>View</th>
                                    <th>Print Summary</th>
                                    <th>Print Individual</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($data as $key => $batch)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $batch->original_batch_id }} </td>
                                        <td> {{ $batch->datereceived }} </td>
                                        <td>
                                            <center><a href="{{ url('results/'.$batch->id.'/'.$testtype.'/batch') }}">View Batch</a></center>
                                        </td>
                                        <td>
                                            <center><a href='{{ url("printindividualbatch/$testtype/$batch->id") }}'><img src="{{ asset('img/print.png') }}" />&nbsp;Batch-Individual</a></center>
                                        </td>
                                        <td>
                                            <center><a href='{{ url("printbatchsummary/$testtype/$batch->id") }}'><img src="{{ asset('img/print.png') }}" />&nbsp;Summary</a></center>
                                        </td>
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

    @component('/tables/scripts')

    @endcomponent

@endsection