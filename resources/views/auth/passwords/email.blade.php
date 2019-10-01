@extends('layouts.master')
@section('title', 'Password Reset')

@section('content')

    @include('shared.defaultHeader')
    
    <div class="container-fluid hero_bg">
        <div class="container">  
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3 ">
                    <div class="form-holder">
                        
                        <h4>Reset Account Password</h4>

                        {!! Form::open(['method' => 'POST', 'route'=> 'password.email', 'class' => 'form']) !!}
                            {{-- @csrf --}}

                            <div class="form-group">
                                {!! Form::label('email', 'Email Address') !!}

                                <div class="input-group">
                                    {!! Form::email('email', null, ['class'=> 'form-control ', 'value'=> '{{ old("email") }}', 'required'=>true]) !!}
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-user"></i>
                                    </span>
                                </div>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    @foreach($errors->all() as $error)
                                        <li>{{$error}}</li>
                                    @endforeach
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            
                            {!! Form::submit('Send Password Reset Link', ['class'=>'btn btn-primary btn-lg']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
