@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">

                    <a href="{{ action('LeadsController@leadDetail') }}" class="btn btn-danger">{{ __('tools.back') }}</a>

                    <div>
                        @foreach ($leads as $lead)
                            <a href="{{ action('LeadsController@leadDetail',['lead' => $lead]) }}" class="fas fa-external-link-alt"></a>
                            {{ $lead->id }}
                            {{ $lead->FirstName }}
                            {{ $lead->LastName }}
                            {{ $lead->Date }}
                            {{ $lead->Campaign }}
                            {{ $lead->LastUpdated }}
                            <br>
                        @endforeach
                    </div>

				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection