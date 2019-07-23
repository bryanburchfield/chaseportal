@extends('layouts.dash')

@section('title', 'Reports')

@section('content')

<div class="preloader"></div>

<div class="wrapper">
    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container mt20">
                <div class="row">

                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection