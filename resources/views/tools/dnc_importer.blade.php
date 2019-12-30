@php
if (Auth::user()->isType('demo')) {
	$demo = true;
} else {
	$demo = false;
}
@endphp
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
							@if ($message = Session::get('flash'))
							<div class="alert alert-info alert-block">
								<button type="button" class="close" data-dismiss="alert">×</button>	
								<strong>{{ $message }}</strong>
							</div>
							@endif
							<h2 class="bbnone">Do Not Call Files</h2>
							@if (count($files))
							<div class="table-responsive">
                               	<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>ID</th>
											<th>Uploaded</th>
											<th>Description</th>
											<th>Records</th>
											<th>Errors</th>
											<th>Processed</th>
											<th>Reversed</th>
										</tr>
									</thead>

									<tbody>
										@foreach ($files as $file)
											<tr>
												<td>{{$file['id']}}</td>
												<td>{{$file['uploaded_at']}}</td>
												<td>{{$file['description']}}</td>
												<td>{{$file['recs']}}</td>
												<td>{{$file['errors']}}</td>
												<td>
													@if (empty($file['process_started_at']))
														[PROCESS BUTTON]
													@elseif (empty($file['processed_at']))
														In Process
													@else
														{{$file['processed_at']}}
													@endif
												</td>
												<td>
													@if (empty($file['process_started_at']))
														[DELETE BUTTON]
													@elseif (!empty($file['processed_at']) && empty($file['reverse_started_at']))
														[REVERSE BUTTON]
													@elseif (!empty($file['processed_at']) && empty($file['reversed_at']))
														In Process
													@else
														{{$file['reversed_at']}}
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
									@else
									No files have been uploaded yet
									@endif
								</table>
							</div>
						</div>
						<a href="/tools/dnc_importer/upload">Upload a File</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection