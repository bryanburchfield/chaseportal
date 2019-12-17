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
							<h2 class="bbnone">Do Not Call Files</h2>
							@if (count($files))
							<table border="1">
								<thead>
									<tr>
									<th>ID</th>
									<th>Uploaded</th>
									<th>Description</th>
									<th>Records</th>
									<th>Errors</th>
									<th>Processed</th>
									<th>Delete</th>
									</tr>
								<thead>
								<tbody>
									@foreach ($files as $file)
									<tr>
									<td>{{$file['id']}}</td>
									<td>{{$file['uploaded_at']}}</td>
									<td>{{$file['description']}}</td>
									<td>{{$file['recs']}}</td>
									<td>{{$file['errors']}}</td>
									<td>
										@if (empty($file['processed_at']))
										[PROCESS BUTTON]
										@else
										{{$file['processed_at']}}
										@endif
									</td>
									<td>
										@if (empty($file['processed_at']))
											[DELETE BUTTON]
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
						<a href="/tools/dnc_importer/upload">Upload a File</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection