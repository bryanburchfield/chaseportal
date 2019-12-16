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
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#contactflow_builder">Contact Flow Builder</a></li>
                            <li><a href="{{url('tools.dnc_importer')}}">DNC Importer</a></li>
                        </ul>

						<div class="tab-pane mt30" id="dnc_importer">
						    <h2 class="bbnone">DNC Importer</h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection