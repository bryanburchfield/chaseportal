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
			    	<div class="col-sm-12">
                        <h2>Recipient Audit Trail</h2>
                        <a class="btn btn-primary flt_rgt" href="{{ URL::previous() }}">Go Back</a>

                        <div class="col-sm-5 pl0">
                            <div class="card">
                                {{ __('general.full_name') }}: {{ $recipient->name}}<br>
                                {{ __('general.email') }}: {{ $recipient->email}}<br>
                                {{ __('general.phone') }}: {{ $recipient->phone}}<br>
                            </div>
                        </div>
					</div>

                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-striped audit_table">
                                <thead>
                                    <th>Event</th>
                                    <th>At</th>
                                    <th>From</th>
                                    <th>By</th>
                                    <th>Field</th>
                                    <th>Old Value</th>
                                    <th>New Value</th>
                                </thead>

                                <tbody>
                                    @foreach ($audits as $audit)
                                        @php
                                            $modified = $audit->getModified();
                                            $fields = array_keys($modified);
                                            $old = array_column($modified, 'old');
                                            $new = array_column($modified, 'new');
                                        @endphp
                                        
                                        <tr>
                                            <td>{{ $audit->event }}</td>
                                            <td>{{ $audit->created_at->tz('America/New_York')->toCookieString() }}</td>
                                            <td>{{ $audit->ip_address }}</td>
                                            <td>{{ $audit->user->name }} ({{ $audit->user->email }}</td>
                                            <td>
                                                @foreach ($fields as $val)
                                                    {{ $val }}<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($old as $val)
                                                    {{ $val }}<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($new as $val)
                                                    {{ $val }}<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h2 class="mt20">KPI Changes</h2>
                    </div>

                    @foreach ($kpi_recipient_audits as $created_at_array)
                        @php
                            // grab first record of array
                            $details = reset($created_at_array);
                        @endphp

                        <div class="col-sm-12">
                            <div class="card oauto">
                                <div class="col-sm-4 kpi_change_info_cnt pl0">
                                    <h4 class="mb10"><span class="kpi_change_info">At:</span> {{ $details['created_at']->tz('America/New_York')->toCookieString() }}</h4>
                                    <h4 class="mb10"> <span class="kpi_change_info">From:</span> {{ $details['ip_address'] }}</h4>
                                    <h4 class="mb10"><span class="kpi_change_info">By:</span> {{ $details['user_name'] }} </h4>
                                    <h4 class="mb20"><span class="kpi_change_info"> </span> {{ $details['user_email'] }}</h4>
                                </div>
                                <div class="col-sm-4">
                                    @foreach ($created_at_array as $audit)
                                        <li>{{ $audit['kpi_event'] }} {{ __('kpi.' . $audit['kpi']->name) }}</li>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection
