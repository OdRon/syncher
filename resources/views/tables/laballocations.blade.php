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
                <div class="panel-body">
                    <div class="alert alert-warning">
                        <!-- Please select the parameters from the options below to generate the Submitted Kits Consumption query. -->
                    </div>
                    <div class="table-responsive" style="margin-top: 2em;">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Allocation Period</th>
                                    <th>Test Type</th>
                                    <th>Pending Approval</th>
                                    <th>Approved</th>
                                    <th>Rejected</th>
                                    <th>Tasks</th>
                                </tr>
                            </thead>
                            <tbody> 
                            @foreach($data['allocations'] as $key => $allocation)
                                <tr>
                                    @php
                                        $type = 'consumables';
                                        if ($allocation->testtype == 1)
                                            $type = 'EID';
                                        else if ($allocation->testtype == 2)
                                            $type = 'VL';
                                    @endphp
                                    <td> {{ $key + 1 }} </td>
                                    <td> 
                                        {{ date("F", mktime(null, null, null, $allocation->month)) }}, 
                                        {{ $allocation->year }}
                                    </td>
                                    <td>{{ strtoupper($type) }}</td>
                                    <td><center><span class="label label-{{ $data['badge']($allocation->pending, 1) }}">{{ $allocation->pending }}</span></center></td>
                                    <td><center><span class="label label-{{ $data['badge']($allocation->approved, 2) }}">{{ $allocation->approved }}</span></center></td>
                                    <td><center><span class="label label-{{ $data['badge']($allocation->rejected, 3) }}">{{ $allocation->rejected }}</span></center></td>
                                    <td>
                                        <a href="{{ url('lab/allocation/'.$allocation->id.'/'.$type) }}" class="btn btn-default">
                                            View
                                        </a>
                                        @if($allocation->rejected > 0)
                                         | 
                                        <a href="{{ url('lab/allocation/'.$allocation->id.'/'.$type.'/1') }}" class="btn btn-warning">
                                            Update Rejected
                                        </a>
                                        @endif
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
    <script type="text/javascript">
        $(document).ready(function(){
            
        });
    </script>
@endsection