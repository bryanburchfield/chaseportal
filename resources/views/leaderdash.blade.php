@extends('layouts.dash')

@section('title', 'Leaderboard Dashboard')

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')
        @include('shared.leaderdash')
    </div>
</div>
@include('shared.datepicker')
@endsection