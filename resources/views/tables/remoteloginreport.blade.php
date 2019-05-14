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
                        <a href='{{ url("reports/remotelogin/EID/$year") }}'>{{ gmdate('Y')-$i }}</a> |
                    @endfor
                </div>
                <!-- Year -->
                <!-- Month -->
                <div class="col-md-6">
                    <center><h5>Month Filter</h5></center>
                    @for ($i = 1; $i <= 12; $i++)
                        <a href='{{ url("reports/remotelogin/EID/null/$i") }}'>{{ gmdate("F", mktime(null, null, null, $i)) }}</a> |
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
                                <th>Year</th>
                                <th>Month</th>
                                <th>Samples Remote Logged</th>
                                <th>Total Logged Samples</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($sampleslogs as $logs)
                            <tr>
                                <td>{{ $logs->labname ?? '' }}</td>
                                <td>{{ $logs->year ?? '' }}</td>
                                <td>{{ $logs->month ?? '' }}</td>
                                <td>{{ $logs->remotelogged ?? 0 }}</td>
                                <td>{{ $logs->totallogged ?? 0 }}</td>
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