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
							<h2 class="bbnone">Errors in file: ({{$file->id}}) {{$file->description}}</h2>
							<form action="{{ action("DncController@index") }}" method="get">
							<input type="submit" value="Back" />
							@if (count($file->errorRecs()))
								<div class="table-responsive">
									<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>ID</th>
											<th>Phone</th>
											<th>Error</th>
										</tr>
									</thead>
									<tbody>
									@foreach ($file->errorRecs() as $error_rec)
									<tr>
										<td>{{$error_rec['id']}}</td>
										<td>{{$error_rec['phone']}}</td>
										<td>{{$error_rec['error']}}</td>
									</tr>
									@endforeach
									</tbody>
									</table>	
								</div>
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