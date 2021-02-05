@extends('layouts.master')
@section('title', 'Spam Check')

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

						<div class="tab-pane" id="spam_check">
							<h2 class="bbnone mb0">{{__('tools.file')}} #{{$file->id}} : <span>{{$file->description}}</span></h2>
                            <h4>Uploaded by: {{$file->user->name}} on {{\Carbon\Carbon::parse($file->uploaded_at)->toDayDateTimeString()}}</h4>

							<form action="{{ action("SpamCheckController@index") }}" method="get">
							<a href="{{url('admin/spam_check')}}" class="btn btn-md btn-warning mt20">{{__('tools.back')}}</a>

							@if (count($records))
								{{ $records->links() }}
								<div class="table-responsive nobdr">
									<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>{{__('tools.line')}} #</th>
											<th>{{__('tools.phone')}}</th>
											<th>{{__('tools.error')}}</th>
											<th>Checked</th>
											<th>Flagged</th>
											<th>Flags</th>
										</tr>
									</thead>
									<tbody>
									@foreach ($records as $record)
									<tr>
										<td>{{$record['line']}}</td>
										<td>{{$record['phone']}}</td>
										<td>{{$record['error']}}</td>
										<td>{{$record['checked']}}</td>
										<td>{{$record['flagged']}}</td>
										<td>{{$record['flags']}}</td>
									</tr>
									@endforeach
									</tbody>
									</table>
								</div>
								{{ $records->links() }}
								<a href="{{url('admin/spam_check')}}" class="btn btn-md btn-warning">{{__('tools.back')}}</a>
							@endif
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection