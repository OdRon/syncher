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
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr> 
                                    <th>#</th>
                                    <th>Batch No</th>
                                    <th>Date Received</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($data as $key => $batch)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $batch->id }} </td>
                                        <td> {{ $batch->datereceived }} </td>
                                        <td>
                                            <a href="{{ url('results/'.$batch->id.'/'.$testtype.'/batch') }}">View Batch</a>
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