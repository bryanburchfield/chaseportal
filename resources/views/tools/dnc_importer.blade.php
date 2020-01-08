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

							<h2 class="bbnone">{{__('tools.dnc_files')}}</h2>
							<a class="btn btn-primary" href="/tools/dnc_importer/upload">{{__('tools.upload_new_file')}}</a>

							<div class="card instructions">
								<a href="#" class="close_instruc"><i class="fas fa-times-circle"></i></a>
								<h3 class="mb20"><b>{{__('tools.instructions')}}</b></h3>
								<ul class="pl10 paditem5">
									<li>{{__('tools.dnc_instruc1')}}</li>
									<li>{{__('tools.dnc_instruc2')}}</li>
									<li>{{__('tools.dnc_instruc3')}}</li>
									<li>{{__('tools.dnc_instruc4')}}</li>
									<li>{{__('tools.dnc_instruc5')}}</li>
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
											<th class="text-center">{{__('tools.view')}}</th>
											<th class="text-center">ID</th>
											<th>{{__('tools.description')}}</th>
											<th>{{__('tools.file_name')}}</th>
											<th>{{__('tools.uploaded')}}</th>
											<th class="text-center">{{__('tools.records')}}</th>
											<th class="text-center">{{__('tools.errors')}}</th>
											<th>{{__('tools.processed')}}</th>
											<th>{{__('tools.reversed')}}</th>
										</tr>
									</thead>

									<tbody>
										@foreach ($files as $file)
											<tr>
												<td><a class="btn btn-link" href="{{ action("DncController@showRecords", ["id" => $file['id']]) }}"><i class="far fa-eye"></i></a></td>
												<td class="text-center">{{$file['id']}}</td>
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
														<button class="btn btn-success" name="action" value="process:{{$file['id']}}">{{__('tools.process')}}</button>
													@elseif (empty($file['processed_at']))
														{{__('tools.in_process')}}
													@else
														{{$file['processed_at']}}
													@endif
												</td>
												<td>
													@if (empty($file['process_started_at']))
														<a class="btn btn-danger delete_dnc" data-toggle="modal" data-target="#deleteDNCModal" href="#" data-id="{{$file['id']}}"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</a>

													@elseif (!empty($file['processed_at']) && empty($file['reverse_started_at']))
														<a class="btn btn-danger reverse_dnc" data-toggle="modal" data-target="#reverseDNCModal" href="#" data-id="{{$file['id']}}"><i class="fas fa-history"></i> {{__('tools.reverse')}}</a>
													@elseif (!empty($file['processed_at']) && empty($file['reversed_at']))
														{{__('tools.in_process')}}
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

<!-- Delete DNC Modal -->
<div class="modal fade" id="deleteDNCModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.confirm_dnc_removal')}}</h4>
            </div>
            <div class="modal-body">
               <h3>{{__('tools.confirm_delete')}}</h3>
            </div>
	        <div class="modal-footer">
	            <form enctype="multipart/form-data" method="post">
					@csrf
	            	<button class="btn btn-danger" name="action" value=""><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
	            </form>
	        </div>
	    </div>
    </div>
</div>

<!-- Reverse DNC Modal -->
<div class="modal fade" id="reverseDNCModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.confirm_dnc_reversal')}}</h4>
            </div>
            <div class="modal-body">
               <h3>{{__('tools.confirm_reversal')}}</h3>
            </div>
	        <div class="modal-footer">
	            <form enctype="multipart/form-data" method="post">
					@csrf
	            	<button class="btn btn-danger" name="action" value=""><i class="fa fa-trash-alt"></i> {{__('tools.reverse')}}</button>
	            </form>
	        </div>
	    </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection