@extends('layouts.reportexport')
@section('title', __('general.reports'))

@section('content')
	<div class="pinned_table table-responsive report_table report_export_table">
		@include('shared.reporttable')
	</div>
@endsection