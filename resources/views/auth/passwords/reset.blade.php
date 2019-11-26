@extends('layouts.master')

@section('content')

    @include('shared.defaultHeader')
    
    <div class="container-fluid hero_bg">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-holder">
                        <h4>{{__('general.reset_pw')}}</h4>

                        <div class="card-body">
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group">
                                    <label for="email">{{__('general.email')}}</label>
                                    <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" readonly autofocus>
                                </div>

                                <div class="form-group">
                                    <label for="password">{{__('general.password')}}</label>
                                    <input id="password" type="password" class="form-control" name="password" required>
                                </div>

                                <div class="form-group">
                                    <label for="password-confirm">{{__('general.confirm_password')}}</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                </div>

                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        @foreach($errors->all() as $error)
                                            <li>{{$error}}</li>
                                        @endforeach
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-primary">
                                    {{ __('general.reset_pw') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
