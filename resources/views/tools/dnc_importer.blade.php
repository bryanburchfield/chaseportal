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
							<a class="btn btn-primary" href="/tools/dnc_importer/upload">Upload a New File</a>

							<div class="card instructions">
								<h3 class="mb20"><b>Instructions</b></h3>
								<ul>
								<li>Upload a file to the portal.  This only stages the file without inserting the DNC numbers into the server.</li>
								<li>Review the contents to ensure it has the number of records you were expecting, and view any errors.</li>
								<li>At this point, you will either delete the file (so that you can correct and re-upload it) or Process the file.<br />
								Processing inserts the DNC records into the server.  This will run in the background and may take some time to complete.</li>
								<li>After the file has been processed, you can still choose to reverse it, which will delete the DNC records from the server.  This also runs in the background and may take some time.</li>
								</ul>
								</div>

							@if (count($files))
							{{ $files->links() }}
							<div class="table-responsive">
								<form enctype="multipart/form-data" method="post">
								@csrf
                               	<table class="table rules_table mt20">
									<thead>
										<tr>
											<th class="text-center">ID</th>
											<th class="text-center">View</th>
											<th>Description</th>
											<th>File Name</th>
											<th>Uploaded</th>
											<th class="text-center">Records</th>
											<th class="text-center">Errors</th>
											<th>Processed</th>
											<th>Reversed</th>
										</tr>
									</thead>

									<tbody>
										@foreach ($files as $file)
											<tr>
												<td class="text-center">{{$file['id']}}</td>
												<td><a class="btn btn-link" href="{{ action("DncController@showRecords", ["id" => $file['id']]) }}"><i class="far fa-eye"></i></a></td>
												<td>{{$file['description']}}</td>
												<td>{{$file['filename']}}</td>
												<td>{{$file['uploaded_at']}}</td>
												<td class="text-center">{{$file['recs']}}</td>
												@if ($file['errors'] > 0)
													<td><a class="btn btn-link danger text-center" href="{{ action("DncController@showErrors", ["id" => $file['id']]) }}">{{$file['errors']}}</a></td>
												@else
													<td class="text-center">{{$file['errors']}}</td>
												@endif
												<td>
													@if (empty($file['process_started_at']))
														<button class="btn btn-success" name="action" value="process:{{$file['id']}}">Process</button>
													@elseif (empty($file['processed_at']))
														In Process
													@else
														{{$file['processed_at']}}
													@endif
												</td>
												<td>
													@if (empty($file['process_started_at']))
														<button class="btn btn-danger" name="action" value="delete:{{$file['id']}}" onclick="return confirm('Are you sure?')"><i class="fa fa-trash-alt"></i> Delete</button>
													@elseif (!empty($file['processed_at']) && empty($file['reverse_started_at']))
														<button class="btn btn-danger" name="action" value="reverse:{{$file['id']}}" onclick="return confirm('Are you sure?')"><i class="fas fa-history"></i> Reverse</button>
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
							{{ $files->links() }}
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