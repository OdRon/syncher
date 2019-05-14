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
                @forelse($consumption->details as $key => $detail)
                    <div class="alert alert-info">
                        <center>
                            {{ $detail->machine->machine ?? '' }}
                            @if($detail->testtype == 1) EID @elseif($detail->testtype == 2) VL @else Commodities @endif
                            Consumptions
                        </center>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Commodity Name</th>
                                    <th>Units</th>
                                    <th>Beginning Balance</th>
                                    <th>Quantity Received</th>
                                    <th>Quantity Consumed</th>
                                    <th>Positive Adjustment</th>
                                    <th>Negative Adjustment</th>
                                    <th>Wastage</th>
                                    <th>Closing Balance</th>
                                    <th>Quantity Requested</th>
                                </tr>
                            </thead>
                            <tbody> 
                            @foreach($detail->breakdown as $key => $breakdown)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $breakdown->consumption_breakdown->name ?? '' }}</td>
                                    <td>{{ $breakdown->consumption_breakdown->unit ?? '' }}</td>
                                    <td>{{ $breakdown->opening }}</td>
                                    <td>{{ $breakdown->qty_received }}</td>
                                    <td>{{ $breakdown->consumed }}</td>
                                    <td>{{ $breakdown->issued_in }}</td>
                                    <td>{{ $breakdown->issued_out }}</td>
                                    <td>{{ $breakdown->wasted }}</td>
                                    <td>{{ $breakdown->closing }}</td>
                                    <td>{{ $breakdown->requested }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="alert alert-warning">
                        <center>No consumption data found.</center>
                    </div>
                @endforelse
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