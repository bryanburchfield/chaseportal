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
						@include('tools.shared.topnav', ['toolpage' => 'dnc'])

						<div class="tab-pane mt30" id="dnc_importer">
							<h2 class="bbnone">{{__('tools.file')}}
								#{{$file->id}}:
								[{{$file->filename}}]
								{{$file->description}}
							</h2>

							<form action="{{ action("DncController@index") }}" method="get">
							<a href="{{url('tools/dnc_importer')}}" class="btn btn-sm btn-secondary">{{__('tools.back')}}</a>

							@if (count($records))
								{{ $records->links() }}
								<div class="table-responsive nobdr">
									<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>{{__('tools.line')}} #</th>
											<th>{{__('tools.phone')}}</th>
											<th>{{__('tools.error')}}</th>
										</tr>
									</thead>
									<tbody>
									@foreach ($records as $record)
									<tr>
										<td>{{$record['line']}}</td>
										<td>{{$record['phone']}}</td>
										<td>{{$record['error']}}</td>
									</tr>
									@endforeach
									</tbody>
									</table>
								</div>
								{{ $records->links() }}
								<a href="{{url('tools/dnc_importer')}}" class="btn btn-sm btn-secondary">{{__('tools.back')}}</a>
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