@extends('layouts.dash')

@section('title', 'Chase Data Login')

@section('content')

@include('shared.defaultHeader')

    <div class="container-fluid hero_bg">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="form-holder">
                        <h4>{{__('general.login')}}</h4>
                        <form class="form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <label for="email">{{__('general.email')}}</label>
                                <div class="input-group">
                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password">{{__('general.password')}}</label>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('general.remember_me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">
                                {{ __('general.login') }}
                            </button>

                            @if (Route::has('password.request'))
                            <a class="btn btn-link mt-1" href="{{ route('password.request') }}">
                                {{ __('general.forgot_pw') }}
                            </a>
                            @endif

                            @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $e)
                                    {{ $e }}
                                @endforeach
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection()