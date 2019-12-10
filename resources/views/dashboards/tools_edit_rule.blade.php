@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

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
	                        {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 edit_rule']) !!}

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

            					<div class="form-group">
            						{!! Form::label('filter_type', __('tools.filter_type')) !!}
            						{!! Form::select("filter_type", array(null=>__('general.select_one'), 'lead_age' => 'Lead Age', 'lead_attempts' => '# of Attempts on Lead', 'days_called' => 'Distinct Days Leads are Called'), $lead_rule['filter_type'], ["class" => "form-control", 'id'=> 'update_filter_type', 'required'=>true]) !!}
            					</div>

								<div class="form-group">
									{!! Form::label('filter_value', __('tools.days_to_filter')) !!}
									{!! Form::text('filter_value', $lead_rule['filter_value'], ['class'=>'form-control filter_value', 'required'=>true, 'id'=> 'update_filter_value']) !!}
								</div>

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
								<a href="{{ url('/dashboards/tools/') }}" class="btn btn-default mb0">{{__('general.cancel')}}</a>

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

