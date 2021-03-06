@extends('layouts.master')

@section('title', __('general.master_dashboard'))

@section('content')

<div class="preloader"></div>
{{-- <input type="hidden" value="{{ $campaign }}" id="campaign" name="campaign"> --}}
<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

<div class="wrapper">

    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
        @include($dashbody)
        </div>
    </div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@include('shared.datepicker')
@endsection