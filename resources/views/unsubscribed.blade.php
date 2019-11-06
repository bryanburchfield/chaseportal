@extends('layouts.dash')

@section('title', 'KPI Dashboard')
@section('content')

<div class="container-fluid bg">
    <div class="container mt50">
        <div class="row">
            <div class="col-sm-12">
            <h2>{{ __('kpi.unsubscribed') }}</h2>
            </div>
        </div>
    </div>
</div>

@endsection