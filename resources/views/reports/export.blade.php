@extends('layouts.reportexport')
@section('title', __('general.reports'))

@section('content')
	<div class="table-responsive report_table report_export_table">
		@include('shared.reporttable')
	</div>
@endsection