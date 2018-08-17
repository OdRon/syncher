@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="row" style="margin-bottom: 1em;">
                <!-- Year -->
                <div class="col-md-6">
                    <center><h5>Year Filter</h5></center>
                    @for ($i = 0; $i <= 9; $i++)
                        @php
                            $year=gmdate('Y')-$i
                        @endphp
                        <a href='{{ url("reports/utilization/$viewdata->testingSystem/$year") }}'>{{ gmdate('Y')-$i }}</a> |
                    @endfor
                </div>
                <!-- Year -->
                <!-- Month -->
                <div class="col-md-6">
                    <center><h5>Month Filter</h5></center>
                    @for ($i = 1; $i <= 12; $i++)
                        <a href='{{ url("reports/utilization/$viewdata->testingSystem/null/$i") }}'>{{ gmdate("F", mktime(null, null, null, $i)) }}</a> |
                    @endfor
                </div>
                <!-- Month -->
            </div>
            <div class="hpanel">
                <div class="panel-body">
            	    <table class="table table-striped table-bordered table-hover data-table">
                        <thead>
                            <tr class="colhead">
                                <th>Lab Name</th>
                                @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    <th>{{ $machinevalue->machine }}</th>
                                @endforeach
                                <th>Total</th>
                                @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    <th>{{ $machinevalue->machine }}%</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                           @foreach($viewdata->data as $datakey => $datavalue)
                           <tr>
                               <td>{{ $datavalue['lab'] }}</td>
                               @php
                                    $totals = 0;
                               @endphp
                               @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    @php
                                        $machine = $machinevalue->machine;
                                        $totals += (isset($datavalue[$machine])) ? $datavalue[$machine] : 0;
                                    @endphp
                                    <td>{{ (isset($datavalue[$machine])) ? number_format($datavalue[$machine]) : 0 }}</td>
                               @endforeach
                               <td>{{ ($totals) ? number_format($totals) : 0 }}</td>
                               @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    @php
                                        $machine = $machinevalue->machine;
                                    @endphp
                                    <td>{{ (isset($datavalue[$machine])) ? round((@(($datavalue[$machine]/$totals) * 100)), 2) : 0 }} %</td>
                               @endforeach
                           </tr>
                           @endforeach
                        </tbody>
                    </table>
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