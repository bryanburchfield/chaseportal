@extends('layouts.report')

@section('title', __('general.compliance_dashboard'))

@section('content')

<div class="container-fluid bg">
    <div class="container mt50">
        <div class="row">
            <div class="col-sm-12">
                <p>
                    <a href="{{ action('MasterDashController@complianceDashboard') }}">Back to Dashboard</a>
                </p>
                @foreach ($pause_codes as $code)
                    {{ $code }}
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
