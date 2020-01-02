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
							<h2 class="bbnone">File
								#{{$file->id}}:
								[{{$file->filename}}]
								{{$file->description}}
							</h2>
							<form action="{{ action("DncController@index") }}" method="get">
							<input type="submit" value="Back" />
							@if (count($all_recs))
								{{ $all_recs->links() }}
								<div class="table-responsive">
									<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>Line#</th>
											<th>Phone</th>
											<th>Error</th>
										</tr>
									</thead>
									<tbody>
									@foreach ($all_recs as $record)
									<tr>
										<td>{{$record['line']}}</td>
										<td>{{$record['phone']}}</td>
										<td>{{$record['error']}}</td>
									</tr>
									@endforeach
									</tbody>
									</table>
								</div>
								{{ $all_recs->links() }}
								<input type="submit" value="Back" />
							@endif
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection