@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
        .reports {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 0px;
            /*padding-bottom: 0px;*/
        }
    </style>
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="alert alert-success">
                    <center>Test Outcome Report [ All Tested Samples ]</center>
                </div>
                <div class="panel-body">
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            dateFormat: 'MM yy'
        });

    @endcomponent
   
@endsection