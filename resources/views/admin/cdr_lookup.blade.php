@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.admin_sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
				<div class="row">
					<div class="col-sm-12 mt30">
						<div class="report_filters card">
							<h2 class="page_heading">CDR Lookup</h2>
							<form action="#" method="POST" class="form fc_style cdr_lookup_form" name="cdr_lookup_form"
								id="">
								<div class="row">

									<div class="col-sm-4">
										<div class="form-group">
											<label>Phone #</label>
											<input type="tel" name="phone" id="phone" class="form-control"
												required><br>
											<label class="radio-inline"><input class="search_type" type="radio"
													name="search_type" value="number_dialed" checked>Number
												Dialed</label>
											<label class="radio-inline"><input class="search_type" type="radio"
													name="search_type" value="caller_id">Caller ID</label>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											<label>From</label>
											<div class='input-group date '>
												<input type='text' readonly="true" name="fromdate"
													class="form-control datetimepicker fromdate" required
													value="" />
												<span class="input-group-addon">
													<span class="glyphicon glyphicon-calendar">
													</span>
												</span>
											</div>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											<label>To</label>
											<div class='input-group date '>
												<input type='text' readonly="true" name="todate"
													class="form-control datetimepicker todate" required value="" />
												<span class="input-group-addon">
													<span class="glyphicon glyphicon-calendar">
													</span>
												</span>
											</div>
										</div>
									</div>
								</div>

								<div class="alert alert-danger report_errors"></div>
								<input type="submit" class="btn btn-primary mb0" value="Search">
							</form>
						</div> <!-- end report_filters -->

						<div class="table-responsive cdr_table hidetilloaded fc_style">
							<table class="cdr_results_table table table-hover reports_table" id="cdr_dataTable">
								<thead>
									<tr role="row">
										<th>ID</th>
										<th>Server</th>
										<th>Attempt</th>
										<th>Call Date</th>
										<th>Call Status</th>
										<th>Call Type</th>
										<th>Caller ID</th>
										<th>Campaign</th>
										<th>Date</th>
										<th>Duration</th>
										<th>Group ID</th>
										<th>Lead ID</th>
										<th>Phone</th>
										<th>Rep</th>
										<th>Subcampaign</th>
									</tr>
								</thead>

								<tbody>

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection