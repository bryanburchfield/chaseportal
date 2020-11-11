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
                            <table class="table table-responsive table-striped table-hover dup_lead_detail_recs">
                                <thead>
                                    <tr>
                                        <th>View</th>
                                        <th>Lead ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Date</th>
                                        <th>Campaign</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($leads as $lead)
                                        <tr>
                                            <td><a href="{{ action('LeadsController@leadDetail',['lead' => $lead]) }}" class="fas fa-external-link-alt"></a></td>
                                            <td>{{ $lead->id }}</td>
                                            <td>{{ $lead->FirstName }}</td>
                                            <td>{{ $lead->LastName }}</td>
                                            <td>{{ $lead->Date }}</td>
                                            <td>{{ $lead->Campaign }}</td>
                                            <td>{{ $lead->LastUpdated }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                    </div>
				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection