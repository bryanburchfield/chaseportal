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
                            <h2 class="bbnone mb20">Upload a DNC File</h2>
                            <ul class="pl10 paditem5">
								<li>File must be in CSV, XLS, or XLSX format.</li>
								<li>If the file has a header row, there must be a 'Phone' column.</li>
								<li>If the file doesn't have a header row, the phone numbers must be in the first column.</li>
							</ul>

							<div class="col-sm-4 p0">
								<form enctype="multipart/form-data" method="post">
									@csrf

									<label class="btn btn-info btn-file mb20">
									    Upload a File <input type="file" name="myfile" accept=".csv,.xls,.xlsx,.ods,.slk" >
									</label>

									<div class="checkbox">
										<label><input type="checkbox" name="has_headers"><b>Has Header Row:</b></label>
									</div>

									<div class="form-group upload_desc">
										<label>Description</label>
										<input name="description" type="text" class="form-control" />
									</div>

									<input class="btn btn-default btn-cancel mr10" type="submit" name="cancel" value="Cancel" />
									<input class="btn btn-primary mb0" type="submit" value="Submit" />
								</form>
							</div>

							@if($errors->any())
								<div class="alert alert-danger">
									<ul>
										@foreach ($errors->all() as $error)
											<li>{{ $error }}</li>
										@endforeach
									</ul>
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