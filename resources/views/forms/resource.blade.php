@extends('layouts.master')

@component('/forms/css')
    
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
        
    </style>
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
        @isset($resource)
            {{ Form::open(['url' => '/resource/' . $resource->id, 'method' => 'put', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) }}
        @else
            {{ Form::open(['url' => '/resource/', 'method' => 'post', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) }}
        @endisset
            <div class="hpanel">
                <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                    <center>Lab Information</center>
                </div>
                <div class="panel-body" style="padding-bottom: 6px;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Resource Name</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="name" id="name" type="text" value="{{ $resource->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Resource</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="resource" id="resource" type="file">
                        </div>
                    </div>
                </div>           
            </div>
            <center>
                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 2em;margin-bottom: 2em; width: 200px; height: 30px;">Save Resource</button>
            </center>
        {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot
        
    @endcomponent
    <script type="text/javascript">
                
    </script>
   
@endsection