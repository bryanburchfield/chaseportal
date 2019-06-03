@extends('layouts.master')
@section('title', 'Password Reset')

@section('content')
<div class="wrapper">

    {{-- @include('shared.sidenav') --}}

    <div id="content">

        @include('shared.defaultHeader')
        
        <div class="container-fluid hero_bg">
            <div class="container">  
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3 ">
                        <div class="form-holder">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            
                            <h4>Reset Account Password</h4>

                            {!! Form::open(['method' => 'POST', 'route'=> 'password.email', 'class' => 'form']) !!}
                                {{-- @csrf --}}

                                <div class="form-group">
                                    {!! Form::label('email', 'Email Address') !!}

                                    <div class="input-group">
                                        {!! Form::email('email', null, ['class'=> 'form-control {{ $errors->has("email") ? " is-invalid" : "" }}', 'value'=> '{{ old("email") }}', 'required'=>true]) !!}
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-user"></i>
                                        </span>
                                    </div>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                
                                {!! Form::submit('Send Password Reset Link', ['class'=>'btn btn-primary btn-lg']) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
