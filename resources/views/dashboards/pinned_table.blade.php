@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

{{-- <div class="preloader"></div> --}}

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		{{-- @include('shared.navbar') --}}

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
                   		<table class="table">
                   			<thead>
                   				<tr>
                   					<th>adf</th>
                   					<th>asdf</th>
                   					<th>adfsdaf</th>
                   					<th>adfasdf</th>
                   					<th>asdfasd</th>
                   				</tr>
                   			</thead>

                   			<tbody>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   				<tr>
                   					<td>adskfjalksjf</td>
                   					<td>jkasldf</td>
                   					<td>kljasdlkj</td>
                   					<td>lkjj</td>
                   					<td>lkj</td>
                   				</tr>
                   			</tbody>
                   		</table>
	            	</div>
				</div>
			</div>
		</div>
	</div>

@include('shared.notifications_bar')

@endsection

