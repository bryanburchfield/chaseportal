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
                            <h2 class="bbnone">Upload a DNC File</h2>
							File must be in CSV, XLS, or XLSX format.<br>
							If file has headers, there must be a 'Phone' column.  If file doesn't have
							headers, the phone has to be the first column.
                            <p>
<form enctype="multipart/form-data" method="post">
	@csrf
	<input name="myfile" type="file" />
	<br>
	Description: <input name="description" type="text" />
	<br>
	Has Headers: <input name="has_headers" type="checkbox" />
	<br>
	<input type="submit" value="Submit" />
</form>
							</p>
							@if($errors->any())
								<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
								</ul>
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