@extends('layouts.dash')

@section('title', __('general.trend_dashboard'))

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')
        @include('shared.trenddash')
    </div>
</div>
@include('shared.datepicker')
@endsection