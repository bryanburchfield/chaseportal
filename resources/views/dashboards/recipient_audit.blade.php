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

						<div class="tab-pane mt30" id="audit_trail">

                            <h2 class="bbnone mb20">Recipient Audit Trail</h2>
                            <a class="btn btn-primary" href="{{ URL::previous() }}">Go Back</a>

                            <h4>Current Values</h4>
                            {{ __('general.full_name') }}: {{ $recipient->name}}<br>
                            {{ __('general.email') }}: {{ $recipient->email}}<br>
                            {{ __('general.phone') }}: {{ $recipient->phone}}<br>

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

                                <table border=1>
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
                            @endforeach

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection
