@extends('layouts.dash')

@section('title', __('general.admin_dashboard'))

@section('content')

<div class="preloader"></div>

<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">

@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')
        @include('shared.admindash')
    </div>
</div>

@include('shared.datepicker')

@endsection