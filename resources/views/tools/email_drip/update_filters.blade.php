@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<?php

	//dd($email_drip_campaign->emailDripCampaignFilters);
	//dd($email_drip_campaign);
?>
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
			    		<h2>Manage Filters - {{$email_drip_campaign->name}}</h2>
					    <form action="{{action('EmailDripController@saveFilters')}}" method="post" class="form">
					    	@csrf

					    	<div class="filter_fields_cnt">
					    	    @if(count($email_drip_campaign->emailDripCampaignFilters))
									@foreach($email_drip_campaign->emailDripCampaignFilters as $filter)
										<div class="row filter_fields_div" data-filterid="{{$filter->id}}">
										    <div class="col-sm-4">
										        <label>Field</label>
										        <div class="form-group">
										            <select class="form-control filter_fields" name="filter_fields[]" data-type="field">
										            	@foreach($filter_fields as $key => $value)
										            		<option {{$filter->field == $key ? 'selected' : ''}} data-type="{{$value}}" value="{{$key}}">{{$key}}</option>
										            	@endforeach
										            </select>
										        </div>
										    </div>

										    <div class="col-sm-3 filter_operators_div">
										        <label>Operator</label>
										        <div class="form-group">
										        	<select class="form-control filter_operators" name="filter_operators[]" data-type="operator">
											        	@foreach ($operators[$filter_fields[$filter->field]] as $key => $value)
															<option {{ $filter->operator == $key ? 'selected' : ''}} value="{{$key}}">{{$value}}</option>
											        	@endforeach
										        	</select>
										        </div>
										    </div>

										    <div class="col-sm-3 filter_values_div">
										        <label>Value</label>
										        <input type="text" class="form-control filter_value" name="filter_values[]" data-type="value" value="{{$filter->value}}">
										    </div>

										    <div class="col-sm-2">
										        <a href="#" class="remove_camp_filter"><i class="fa fa-trash-alt"></i> Remove</a>
										    </div>
										</div>
									@endforeach
								@else
									<div class="row filter_fields_div" data-filterid="{{$filter->id}}">
									    <div class="col-sm-4">
									        <label>Field</label>
									        <div class="form-group">
									            <select class="form-control filter_fields" name="filter_fields[]" data-type="field">
									            	@foreach($filter_fields as $key => $value)
									            		<option data-type="{{$value}}" value="{{$key}}">{{$key}}</option>
									            	@endforeach
									            </select>
									        </div>
									    </div>

									    <div class="col-sm-3 filter_operators_div">
									        <label>Operator</label>
									        <div class="form-group">
									            <select class="form-control filter_operators" name="filter_operators[]" data-type="operator">
									            </select>
									        </div>
									    </div>

									    <div class="col-sm-3 filter_values_div">
									        <label>Value</label>
									        <input type="text" class="form-control filter_value" name="filter_values[]" data-type="value" value="">
									    </div>

									    <div class="col-sm-2">
									        <a href="#" class="remove_camp_filter"><i class="fa fa-trash-alt"></i> Remove</a>
									    </div>
									</div>
					    	    @endif

					    	    <div class="row filters"></div>
					    	</div>
							<div class="alert alert-danger filter_error">Incomplete filter values. Please complete this filter before adding another one.</div>
							<input type="hidden" name="email_drip_campaign_id" id="email_drip_campaign_id" value="{{$email_drip_campaign->id}}">
							<a href="{{url('/tools/email_drip/')}}" class="btn btn-secondary mt0 btn_flt_lft mr10">Cancel</a>
							<a href="#" class="btn btn-info add_email_campaign_filter mt0 btn_flt_lft mr10"><i class="fas fa-plus-circle"></i> Add</a>
					    	<input type="submit" class="btn btn-primary" value="Save Filters">

					    </form>
			    	</div>
			    </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')
@endsection