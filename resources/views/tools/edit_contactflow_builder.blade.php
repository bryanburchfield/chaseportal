@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>
<?php
	//dd($lead_rule);
	//dd($source_subcampaign_list);
?>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container mt50 tools edit_ruleparent">
			    <div class="row">
			        <div class="col-sm-9 col-sm-offset-3 pl0">
			            <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.edit_rule')}}</h2>
			            {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 edit_rule']) !!}
							<input type="hidden" value="{{$lead_rule->id}}" name="id" class="rule_id">
			            <div class="card">
			                <div class="form-group">
			                    {!! Form::label('rule_name', __('tools.rule_name')) !!}
			                    {!! Form::text('rule_name', $lead_rule['rule_name'], ['class'=>'form-control rule_name', 'required'=>true]) !!}
			                </div>
			            </div>
			        </div>
			    </div>

			    <div class="row">
			        <div class="col-sm-3 pr0">
			            <div class="flowchart_element when"><span>{{__('general.where')}}</span></div>
			            <div class="vertical-line"></div>
			        </div>

			        <div class="col-sm-9 pl0">
			            <div class="card" id="when">
			                <div class="form-group">
        						{!! Form::label('source_campaign',  __('tools.campaign')) !!}
        						{!! Form::select("source_campaign", [null=>__('general.select_one')] + $campaigns, $lead_rule['source_campaign'], ["class" => "form-control", 'id'=> 'update_campaign_select', 'required'=>true]) !!}
        					</div>

			                <div class="form-group">
			                    <label for="subcamps">{{__('tools.subcampaign')}}</label>
			                    <input autocomplete="off" list="subcamps" name="subcamps" class="form-control source_subcampaign" value="{{$lead_rule->source_subcampaign}}" />

		                    	<datalist id="subcamps">
			                    	@foreach($source_subcampaign_list as $subcamp)
										<option value="{{$subcamp}}">{{$subcamp}}</option>
			                    	@endforeach
		                    	</datalist>
			                </div>
			            </div>
			        </div>
			    </div>

				@foreach($lead_rule->LeadRuleFilters as $key => $value)

				    <div class="row leadfilter_row">
				        <div class="col-sm-3 pr0">
				            <div class="flowchart_element condition mt35"><span>@if($key){{__('general.and')}}@else{{__('general.when')}}@endif</span></div>
				            <div class="vertical-line"></div>
				        </div>

				        <div class="col-sm-9 pl0">
				            <div class="card" id="condition">

				                <div class="form-group">
            						<label>{{__('tools.filter_type')}}</label>
									<select name="filter_type" class="form-control update_filter_type lead_rule_filter_type">
										<option value="">{{__('general.select_one')}}</option>
										<option {{$value->type=='lead_age' ? 'selected' :'' }} value="lead_age">{{__('tools.lead_age')}}</option>
										<option {{ $value->type=='lead_attempts' ? 'selected' :'' }} value="lead_attempts">{{__('tools.lead_attempts')}}</option>
										<option {{ $value->type=='days_called' ? 'selected' :'' }} value="days_called">{{__('tools.days_called')}}</option>
									</select>
            					</div>

								<div class="form-group">
									<label>{{ __('tools.days_to_filter')}}</label>
									<input type="text" class="form-control filter_value" id="update_filter_value" name="filter_value" value="{{$value->value}}">
								</div>

								{{-- Need to get count of available filters --}}
								@if($key == count($lead_rule->LeadRuleFilters) -1)
				                	<a href="#" class="add_leadrule_filter edit_addrule"><i class="fas fa-plus-circle"></i> Add Another Filter</a>
								@endif

								@if($key)
									<a href="#" class="remove_filter"><i class="fas fa-trash-alt"></i> Remove Filter</a>
								@endif

				                <div class="alert alert-danger filter_error mt20">Please select a filter and value before adding another one</div>

				            </div>
				        </div>
				    </div>
				@endforeach

			    <div class="row">
			        <div class="col-sm-3 pr0">
			            <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
			        </div>

			        <div class="col-sm-9 pl0">
			            <div class="card" id="action">
			                <div class="form-group">
        						{!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
        						{!! Form::select("destination_campaign", [null=>__('general.select_one')] +$campaigns, $lead_rule['destination_campaign'], ["class" => "form-control", 'id'=> 'update_destination_campaign', 'required'=>true]) !!}
        					</div>

			                <div class="form-group">
			                    <label for="subcamps">{{__('tools.destination_subcampaign_ques')}}</label>
			                    <input autocomplete="off" list="destination_subcampaign" name="destination_subcampaign" class="form-control destination_subcampaign" value="{{$lead_rule->destination_subcampaign}}"/>
			                    <datalist id="destination_subcampaign">
			                    	@foreach($destination_subcampaign_list as $subcamp)
										<option value="{{$subcamp}}">{{$subcamp}}</option>
			                    	@endforeach
			                    </datalist>
			                </div>
			            </div>
			        </div>
			    </div>

			    <div class="row">
			        <div class="col-sm-9 col-sm-offset-3 pl0">
			            <div class="card">
			                <div class="form-group">
			                    {!! Form::label('description', __('tools.description')) !!}
			                    {!! Form::textarea("description", $lead_rule['description'], ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
			                </div>

			                <a href="{{url('/tools/contactflow_builder')}}" class="btn btn-default btn-reset">{{__('general.cancel')}}</a>
			                {!! Form::submit(__('tools.save_changes'), ['class'=>'btn btn-primary mb0'] ) !!}
			                <div class="alert alert-danger edit_rule_error mt20"></div>
			            </div>
			        {!! Form::close() !!}
			        </div>
			    </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection

