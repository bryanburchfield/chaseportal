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
						<h3 class="heading">Call Details</h3>
						<div class="report_filters well col-sm-12">
							{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report]) !!}

								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('fromdate', 'From') !!}
											<div class="input-group date">
												{!! Form::text('fromdate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true ]) !!}
												<span class="input-group-addon">
								                    <span class="glyphicon glyphicon-calendar">
								                    </span>
								                </span>
											</div>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('todate', 'To') !!}
											<div class="input-group date">
												{!! Form::text('todate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
												<span class="input-group-addon">
								                    <span class="glyphicon glyphicon-calendar">
								                    </span>
								                </span>
											</div>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('campaigns', 'Campaigns') !!}
											{!! Form::select("campaigns[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('callerids', 'Inbound Sources') !!}
											{!! Form::select("callerids[]", $inbound_sources, null, ["class" => "form-control multiselect", 'id'=> 'inbound_sources_select','multiple'=>true]) !!}
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('reps', 'Reps') !!}
											{!! Form::select("reps[]", $reps, null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('call_statuses', 'Call Statuses') !!}
											{!! Form::select("call_statuses[]", $call_statuses, null, ["class" => "form-control multiselect", 'id'=> 'call_status_select','multiple'=>true]) !!}
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('call_type', 'Call Type') !!}
											{!! Form::select("call_type", $call_types, null, ["class" => "form-control", 'id'=> 'call_type']) !!}
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('durationfrom', 'Duration ( seconds )') !!}
											<div class="input-group">
												{!! Form::text('durationfrom', null, ['class'=>'form-control', 'placeholder'=>'Start']) !!}
											    <span class="input-group-addon">-</span>
											    {!! Form::text('durationto', null, ['class'=>'form-control', 'placeholder'=>'End']) !!}
											</div>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											{!! Form::label('phone', 'Phone') !!}
											{!! Form::tel('phone', null, ['class'=>'form-control', 'required' => false]) !!}
										</div>
									</div>
								</div>
								
								<div class="checkbox">
                                    <label>
                                        {!! Form::checkbox('showonlyterm', null, false, ['id'=>'showonlyterm']) !!}
                                        Show only termination status
                                    </label>
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

@include('shared.reportmodal')
@endsection