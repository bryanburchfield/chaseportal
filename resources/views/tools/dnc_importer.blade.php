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
								<button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>	
								<strong>{{ $message }}</strong>
							</div>
							@endif
							<h2 class="bbnone">Do Not Call Files</h2>
							<a href="/tools/dnc_importer/upload">Upload a New File</a>
							@if (count($files))
							<div class="table-responsive">
								<form enctype="multipart/form-data" method="post">
								@csrf
                               	<table class="table rules_table mt20">
									<thead>
										<tr>
											<th>ID</th>
											<th>Description</th>
											<th>File Name</th>
											<th>Uploaded</th>
											<th>Records</th>
											<th>Errors</th>
											<th>Processed</th>
											<th>Reversed</th>
										</tr>
									</thead>

									<tbody>
										@foreach ($files as $file)
											<tr>
												<td><a href="{{ action("DncController@showRecords", ["id" => $file['id']]) }}">{{$file['id']}}</a></td>
												<td>{{$file['description']}}</td>
												<td>{{$file['filename']}}</td>
												<td>{{$file['uploaded_at']}}</td>
												<td>{{$file['recs']}}</td>
												@if ($file['errors'] > 0)
													<td><a href="{{ action("DncController@showErrors", ["id" => $file['id']]) }}">{{$file['errors']}}</a></td>
												@else
													<td>{{$file['errors']}}</td>
												@endif
												<td>
													@if (empty($file['process_started_at']))
														<button name="action" value="process:{{$file['id']}}">Process</button>
													@elseif (empty($file['processed_at']))
														In Process
													@else
														{{$file['processed_at']}}
													@endif
												</td>
												<td>
													@if (empty($file['process_started_at']))
														<button name="action" value="delete:{{$file['id']}}" onclick="return confirm('Are you sure?')">Delete</button>
													@elseif (!empty($file['processed_at']) && empty($file['reverse_started_at']))
														<button name="action" value="reverse:{{$file['id']}}" onclick="return confirm('Are you sure?')">Reverse</button>
													@elseif (!empty($file['processed_at']) && empty($file['reversed_at']))
														In Process
													@else
														{{$file['reversed_at']}}
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
								</form>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection