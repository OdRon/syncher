@extends('layouts.auth')

@section('content')
@php
    $login_error = Session()->pull('login_error');
@endphp
@isset($login_error)
    <div class="alert alert-danger" id="login_error">
        {{ $login_error }}
    </div>
@endisset
<div class="row">
        <div class="col-md-12">
            <div class="hpanel" style="width: 430px;">
                <div class="panel-body" style="padding: 20px;">
                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}" style="padding-bottom: -;padding-right: 20px;padding-left: 20px;margin-bottom: 0px;">
                            <label class="control-label" for="username" style="color: black;">Username:</label>
                            <div class="input-group m-b">
                                <span class="input-group-addon"><span class="fa fa-user-o"></span></span>

                                <input  type="text" placeholder="Username" title="Please enter you username" required="" value="{{ old('username') }}" name="username" id="username" class="form-control">
                            </div>
                            @if ($errors->has('username'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group{{ $errors->has('password') ? 'has-error' : '' }}" style="padding-bottom: -;padding-right: 20px;padding-left: 20px;margin-bottom: 0px;">
                            <label class="control-label" for="password" style="color: black;">Password:</label>
                            <div class="input-group m-b" style="margin-bottom: 0px;">
                                <span class="input-group-addon"><span class="pe-7s-unlock"></span></span>
                                <input type="password" title="Please enter your password" placeholder="Password" required="" value="" name="password" id="password" class="form-control">
                            </div>
                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-block" style="background-color: #16A085;border-color: #16A085;margin-top: 2em;">Login</button>
                    </form>
                    <div class="text-center m-b-md">
                        <a href="{{ url('login/facility') }} " style="color: white;"><button class="btn btn-primary btn-block" style="margin-top: 2em;">Click <strong class="font-extra-bold font-uppercase">here</strong> for facility login</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
           @php
                if (isset($login_error)) {
            @endphp
                    setTimeout(function(){
                        $("#login_error").fadeOut("slow");
                    }, 4000);
            @php
                }
            @endphp
        });

    </script>
@endsection
