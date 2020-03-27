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
                        <a class="btn btn-primary btn_flt_rgt" href="{{ URL::previous() }}">Go Back</a>

                        <div class="col-sm-5 pl0">
                            <div class="card">
                                <h4>Current Values</h4>
                                {{ __('general.full_name') }}: {{ $recipient->name}}<br>
                                {{ __('general.email') }}: {{ $recipient->email}}<br>
                                {{ __('general.phone') }}: {{ $recipient->phone}}<br>
                            </div>
                        </div>
					</div>

                    <div class="col-sm-12">
                        @foreach ($audits as $audit)
                            @php
                                $modified = $audit->getModified();
                                $fields = array_keys($modified);
                                $old = array_column($modified, 'old');
                                $new = array_column($modified, 'new');
                            @endphp
                            <hr>
                            ==== {{ $audit->event }} ====<br>
                            At: {{ $audit->created_at }}<br>
                            From: {{ $audit->ip_address }}<br>
                            By: {{ $audit->user->name }} ({{ $audit->user->email }})<br>
                            
                            <div class="table-responsive">
                                <table class="table table-striped audit_table">
                                    <thead>
                                        <th>Field</th>
                                        <th>Old</th>
                                        <th>New</th>
                                    </thead>
                                    <tbody>
                                    @for ($i = 0; $i < count($modified); $i++)
                                        <tr>
                                            <td>{{ $fields[$i] }}</td>
                                            <td>{{ isset($old[$i]) ? $old[$i] : '' }}</td>
                                            <td>{{ isset($new[$i]) ? $new[$i] : '' }}</td>
                                        </tr>
                                    @endfor
                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                        ============== KPI Changes ==================
                        
                        @foreach ($kpi_recipient_audits as $created_at_array)
                            @php
                                // grab first record of array
                                $details = reset($created_at_array);
                            @endphp
                            <hr>
                            At: {{ $details['created_at'] }}<br>
                            From: {{ $details['ip_address'] }}<br>
                            By: {{ $details['user_name'] }} ({{ $details['user_email'] }})<br>
                            @foreach ($created_at_array as $audit)
                                {{ $audit['kpi_event'] }} {{ __('kpi.' . $audit['kpi']->name) }}<br>
                            @endforeach
                        @endforeach
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection
