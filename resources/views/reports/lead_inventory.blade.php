@extends('layouts.master')
@section('title', 'Report')

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.reportnav')
		
		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
				<div class="row">
					<div class="col-sm-12">

						<h3 class="heading">Lead Inventory</h3>

						<div class="report_filters well col-sm-12">
							{!! Form::open(['method'=>'POST', 'url'=>'#', 'name'=>'report_filter_form', 'id'=>$report]) !!}

								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('campaign', 'Campaign') !!}
											{!! Form::select("campaign[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
										</div>
									</div>
								</div>

								<div class='reporterrors'>
									@include('shared.reporterrors')
								</div>

								{!! Form::hidden('report', $report, ['id'=>'report']) !!}
								{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

							{!! Form::close() !!}
						</div>

						<div class="report_results">
							<div class="reportpag">
								@include('shared.reportpagination')
							</div>
							<div class="reporttable">
								@include('shared.reporttable')
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection