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
                                    <th>Consumption Period</th>
                                    <th>Tasks</th>
                                </tr>
                            </thead>
                            <tbody> 
                            @foreach($data->consumptions as $key => $consumption)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                    	{{ date("F", mktime(null, null, null, $consumption->month)) }}, 
                                        {{ $consumption->year }}
                                    </td>
                                    <td>
                                    	<a href="{{ url('lab/consumption/'.$consumption->id) }}" class="btn btn-primary">
                                            View Consumption Breakdown
                                        </a>
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