@extends('layouts.master')
@section('title', 'Report')

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')
	<div id="content">

		@include('shared.reportnav')

		<div class="container-fluid bg dashboard p20">
			<h3 class="heading">Agent Time Sheet</h3>
			
			<div class="report_filters well col-sm-12">
				{!! Form::open(['method'=>'POST', 'url'=>'#', 'name'=>'report_filter_form', 'id'=>$report]) !!}

					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								{!! Form::label('fromdate', 'From') !!}
								<div class="input-group date">
									{!! Form::text('fromdate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
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
								{!! Form::label('rep', 'Rep') !!}
								{!! Form::select("rep[]", $rep, null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
							</div>
						</div>
					</div>

					@if(count($errors))
						<div class="alert alert-danger report_errors">
							@foreach($errors as $error)
								{{$error}}
							@endforeach
						</div>
					@endif

					{!! Form::hidden('report', $report, ['id'=>'report']) !!}
					{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
@endsection