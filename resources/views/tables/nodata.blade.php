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
            <div class="row" style="margin-bottom: 1em;">
                <!-- Year -->
                <div class="col-md-6">
                    <center><h5>Year Filter</h5></center>
                    @for ($i = 0; $i <= 9; $i++)
                        @php
                            $year=gmdate('Y')-$i
                        @endphp
                        <a href='{{ url("reports/nodata/$data->testingSystem/$year") }}'>{{ gmdate('Y')-$i }}</a> |
                    @endfor
                </div>
                <!-- Year -->
                <!-- Month -->
                <div class="col-md-6">
                    <center><h5>Month Filter</h5></center>
                    @for ($i = 1; $i <= 12; $i++)
                        <a href='{{ url("reports/nodata/$data->testingSystem/null/$i") }}'>{{ gmdate("F", mktime(null, null, null, $i)) }}</a> |
                    @endfor
                </div>
                <!-- Month -->
            </div>
            <div class="hpanel">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#noage"><strong>A.) No Age</strong></a></li>
                    <li><a data-toggle="tab" href="#nogender"><strong>B.) No Gender</strong></a></li>
                    @if($data->testingSystem == 'VL')
                    <li><a data-toggle="tab" href="#noregimen"><strong>C.) No Regimen</strong></a></li>
                    <li><a data-toggle="tab" href="#noinitiation"><strong>D.) No Initiation Date</strong></a></li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div id="noage" class="tab-pane active">
                        <div class="panel-body">
                            <!-- <div class="alert alert-warning">
                                <center>
                                    Click on Batch Number to View Results for Other Samples in that Batch.
                                    <br />
                                    * You can Download the Result by Clicking on the <img src="{{-- asset('img/print.png') --}}" /> (to Download/Print) Button Below
                                </center>
                            </div> -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover data-table">
                                    <thead>
                                        <tr> 
                                            <th>#</th>
                                            <th>Facility Code</th>
                                            <th>Facility</th>
                                            <th>Partner</th>
                                            <th>County</th>
                                            <th>Sub-County</th>
                                            <th>No. of Samples</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($data->age as $key => $noage)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $noage->facilitycode }}</td>
                                            <td>{{ $noage->facility }}</td>
                                            <td>{{ $noage->partner }}</td>
                                            <td>{{ $noage->county }}</td>
                                            <td>{{ $noage->subcounty }}</td>
                                            <td>{{ $noage->total_samples }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7">No Age Data</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="nogender" class="tab-pane">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr> 
                                            <th>#</th>
                                            <th>Facility Code</th>
                                            <th>Facility</th>
                                            <th>Partner</th>
                                            <th>County</th>
                                            <th>Sub-County</th>
                                            <th>No. of Samples</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($data->gender as $key => $nogender)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $nogender->facilitycode }}</td>
                                            <td>{{ $nogender->facility }}</td>
                                            <td>{{ $nogender->partner }}</td>
                                            <td>{{ $nogender->county }}</td>
                                            <td>{{ $nogender->subcounty }}</td>
                                            <td>{{ $nogender->total_samples }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7">No Gender Data</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if($data->testingSystem == 'VL')
                        <div id="noregimen" class="tab-pane">
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr> 
                                                <th>#</th>
                                                <th>Facility Code</th>
                                                <th>Facility</th>
                                                <th>Partner</th>
                                                <th>County</th>
                                                <th>Sub-County</th>
                                                <th>No. of Samples</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($data->regimen as $key => $noregimen)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $noregimen->facilitycode }}</td>
                                                <td>{{ $noregimen->facility }}</td>
                                                <td>{{ $noregimen->partner }}</td>
                                                <td>{{ $noregimen->county }}</td>
                                                <td>{{ $noregimen->subcounty }}</td>
                                                <td>{{ $noregimen->total_samples }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7">No Regimen Data</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="noinitiation" class="tab-pane">
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover data-table">
                                        <thead>
                                            <tr> 
                                                <th>#</th>
                                                <th>Facility Code</th>
                                                <th>Facility</th>
                                                <th>Partner</th>
                                                <th>County</th>
                                                <th>Sub-County</th>
                                                <th>No. of Samples</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($data->initiation as $key => $noinitiation)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $noinitiation->facilitycode }}</td>
                                                <td>{{ $noinitiation->facility }}</td>
                                                <td>{{ $noinitiation->partner }}</td>
                                                <td>{{ $noinitiation->county }}</td>
                                                <td>{{ $noinitiation->subcounty }}</td>
                                                <td>{{ $noinitiation->total_samples }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7">No Initiation Date Data</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
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
        $(document).ready(function(){});
    </script>

@endsection
