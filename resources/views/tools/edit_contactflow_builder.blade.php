@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>
<?php
	//dd($lead_rule->LeadRuleFilters);
echo count($lead_rule->LeadRuleFilters);
?>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
	                <div class="col-sm-6">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.edit_rule')}}</h2>
	                        {!! Form::open(['method'=>'POST', 'url'=>'/tools/contactflow_builder/update_rule', 'class'=>'form mt20 edit_rule']) !!}

	                            <div class="form-group">
	                            	{!! Form::label('rule_name', __('tools.rule_name')) !!}
	                            	{!! Form::text('rule_name', $lead_rule['rule_name'], ['class'=>'form-control rule_name', 'required'=>true]) !!}
	                            </div>

	                            <div class="form-group">
            						{!! Form::label('source_campaign',  __('tools.campaign')) !!}
            						{!! Form::select("source_campaign", [null=>__('general.select_one')] + $campaigns, $lead_rule['source_campaign'], ["class" => "form-control", 'id'=> 'update_campaign_select', 'required'=>true]) !!}
            					</div>

	                            <div class="form-group">

            						{!! Form::label('source_subcampaign',  __('tools.subcampaign')) !!}
            						{!! Form::text("source_subcampaign", $lead_rule['source_subcampaign'], ["class" => "form-control"]) !!}
            					</div>

								@foreach($lead_rule->LeadRuleFilters as $lr)
	            					<div class="form-group">
	            						<label>{{__('tools.filter_type')}}</label>
										<select name="filter_type" class="form-control update_filter_type">
											<option value="">{{__('general.select_one')}}</option>
											<option {{$lr->type=='lead_age' ? 'selected' :'' }} value="lead_age">{{__('tools.lead_age')}}</option>
											<option {{ $lr->type=='lead_attempts' ? 'selected' :'' }} value="lead_attempts">{{__('tools.lead_attempts')}}</option>
											<option {{ $lr->type=='days_called' ? 'selected' :'' }} value="days_called">{{__('tools.days_called')}}</option>
										</select>
	            					</div>

									<div class="form-group">
										<label>{{ __('tools.days_to_filter')}}</label>
										<input type="text" class="form-control filter_value" id="update_filter_value" name="filter_value" value="{{$lr->value}}">
									</div>
								@endforeach

								<div class="form-group">
            						{!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
            						{!! Form::select("destination_campaign", [null=>__('general.select_one')] +$campaigns, $lead_rule['destination_campaign'], ["class" => "form-control", 'id'=> 'update_destination_campaign', 'required'=>true]) !!}
            					</div>

            					<div class="form-group">

            						{!! Form::label('destination_subcampaign', __('tools.destination_subcampaign_ques')) !!}
            						{!! Form::text("destination_subcampaign", $lead_rule['destination_subcampaign'], ["class" => "form-control"]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('description', __('tools.description')) !!}
            						{!! Form::textarea("description", $lead_rule['description'], ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
            					</div>

            					{!! Form::hidden('id', $lead_rule['id'], ['id'=>'id']) !!}
								<a href="{{ url('/tools/contactflow_builder') }}" class="btn btn-default mb0">{{__('general.cancel')}}</a>

								{!! Form::submit(__('tools.save_changes'), ['class'=>'btn btn-primary mb0'] ) !!}

    							@if($errors->any())
                                    <div class="alert alert-danger mt20">
                                        @foreach($errors->all() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </div>
    							@endif
	                        {!! Form::close() !!}
	                    </div>
	                </div>

	            </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection

