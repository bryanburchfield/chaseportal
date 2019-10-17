@extends('layouts.master')
@section('title', 'Tools')

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
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> Edit Rule</h2>
	                        {!! Form::open(['method'=>'POST', 'url'=>'/dashboards/tools/update_rule', 'class'=>'form mt20']) !!}

	                            <div class="form-group">
	                            	{!! Form::label('rule_name', 'Rule Name') !!}
	                            	{!! Form::text('rule_name', $lead_rule['rule_name'], ['class'=>'form-control rule_name', 'required'=>true]) !!}
	                            </div>
								
	                            <div class="form-group">
            						{!! Form::label('source_campaign', 'Campaigns') !!}
            						{!! Form::select("source_campaign", [null=>'Select One'] + $campaigns, $lead_rule['source_campaign'], ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
            					</div>

	                            <div class="form-group">
            						{!! Form::label('source_subcampaign', 'Sub Campaigns') !!}
            						{!! Form::select("source_subcampaign", [null=>'Select One'], $lead_rule['source_subcampaign'], ["class" => "form-control", 'id'=> 'subcampaign_select']) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('filter_type', 'Filter Type') !!}
            						{!! Form::select("filter_type", array(null=>'Select One', 'lead_age' => 'Lead Age', 'lead_attempts' => '# of Attempts on Lead', 'days_called' => 'Distinct Days Leads are Called'), $lead_rule['filter_type'], ["class" => "form-control", 'id'=> 'filter_type', 'required'=>true]) !!}
            					</div>
								
								<div class="form-group">
									{!! Form::label('filter_value', 'Days to Filter By') !!}
									{!! Form::text('filter_value', $lead_rule['filter_value'], ['class'=>'form-control filter_value', 'required'=>true]) !!}
								</div>
								
								<div class="form-group">
            						{!! Form::label('destination_campaign', 'What would you like the destination Campaign of the lead to be after it meets criteria?') !!}
            						{!! Form::select("destination_campaign", [null=>'Select One'] +$campaigns, $lead_rule['destination_campaign'], ["class" => "form-control", 'id'=> 'destination_campaign', 'required'=>true]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('destination_subcampaign', 'What would you like the destination Subcampaign  of the lead to be after it meets criteria?') !!}
            						{!! Form::select("destination_subcampaign",  [null=>'Select One'], $lead_rule['destination_subcampaign'], ["class" => "form-control", 'id'=> 'destination_subcampaign']) !!}
            					</div>

            					{!! Form::hidden('id', $lead_rule['id'], ['id'=>'id']) !!}
								
								{!! Form::submit('Save Changes', ['class'=>'btn btn-primary mb0'] ) !!}
								
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