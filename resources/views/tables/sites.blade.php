@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
            	    <table class="table table-striped table-bordered table-hover data-table" style="/*font-size: 10px;" >
                        <thead>
                            <tr class="colhead">
                                <th rowspan="2">MFL Code</th>
                                <th rowspan="2">County</th>
                                <th rowspan="2">Sub-County</th>
                                <th rowspan="2">Facility Name</th>
                                <th colspan="2">SMS Printer</th>
                                <th colspan="2">Date Last Result Sent</th>
                            </tr>
                            <tr>
                                <th>Y/N</th>
                                <th>#</th>
                                <th>EID</th>
                                <th>VL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facilitys as $facility)
                        	<tr>
                                <td>{{ $facility->facilitycode }}</td>
                                <td>{{ $facility->county }}</td>
                                <td>{{ $facility->subcounty }}</td>
                                <td>{{ $facility->name }}</td>
                                <td>
                                @if($facility->sms_printer_phoneno)
                                    Y
                                @else
                                    N
                                @endif
                                </td>
                                <td>{{ $facility->sms_printer_phoneno }}</td>
                                <td>N/A</td>
                                <td>N/A</td>
                            </tr>
                            @empty
                                <tr><td colspan="8"><center>No data found</center></td></tr>
                            @endforelse
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