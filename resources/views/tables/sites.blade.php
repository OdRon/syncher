@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
@php
    $rowspan = "";
@endphp
@if(Auth::user()->user_type_id == 3)
    @php
        $rowspan = "rowspan='2'";
    @endphp
@endif
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
            	    <table class="table table-striped table-bordered table-hover data-table" style="/*font-size: 10px;" >
                        <thead>
                            <tr class="colhead">
                                <th {{ $rowspan }}>MFL Code</th>
                                <th {{ $rowspan }}>County</th>
                                <th {{ $rowspan }}>Sub-County</th>
                                <th {{ $rowspan }}>Facility Name</th>
                                @if(Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 5)
                                <th>Implementing Partner</th>
                                @else
                                <th colspan="2">SMS Printer</th>
                                <th colspan="2">Date Last Result Sent</th>
                                @endif
                            </tr>
                            @if(Auth::user()->user_type_id != 4 || Auth::user()->user_type_id != 5)
                            <tr>
                                <th>Y/N</th>
                                <th>#</th>
                                <th>EID</th>
                                <th>VL</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($facilitys as $facility)
                        	<tr>
                                <td>{{ $facility->facilitycode }}</td>
                                <td>{{ $facility->county }}</td>
                                <td>{{ $facility->subcounty }}</td>
                                <td>{{ $facility->name }}</td>
                                @if(Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 5)
                                <td>{{ $facility->partner ?? 'No Partner' }}</td>
                                @else
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
                                @endif
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