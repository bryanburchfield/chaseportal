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

                    <div class="lead_details col-sm-12 mt20">
                            <a href="{{ action('LeadsController@leadDetail') }}" class="btn btn-danger mb20">{{ __('tools.back') }}</a>

                            @foreach ($leads as $lead)
                            <div class="bt bb mt0 mb5 pt10 pb10 p20 dup_lead_detail_recs">
                                <a href="{{ action('LeadsController@leadDetail',['lead' => $lead]) }}" class="fas fa-external-link-alt">
                                <span>{{ $lead->id }}
                                {{ $lead->FirstName }}
                                {{ $lead->LastName }}
                                {{ $lead->Date }}
                                {{ $lead->Campaign }}
                                {{ $lead->LastUpdated }}</span>
                                </a>
                            </div>
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