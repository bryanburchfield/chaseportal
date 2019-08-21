@extends('layouts.reportexport')
@section('title', 'Report')

@section('content')
	<div class="table-responsive report_table">
		@include('shared.reporttable')
	</div>
@endsection