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
	                <div class="col-sm-4">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.add_new_rule')}}</h2>
	                        {!! Form::open(['method'=>'POST', 'url'=>'dashboards/tools', 'class'=>'form mt20 add_rule']) !!}

    							@if($errors->any())
                                    <div class="alert alert-danger mt20">
                                        @foreach($errors->all() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </div>
    							@endif

	                            <div class="form-group">
	                            	{!! Form::label('rule_name', __('tools.rule_name')) !!}
	                            	{!! Form::text('rule_name', null, ['class'=>'form-control rule_name', 'required'=>true]) !!}
	                            </div>

	                            <div class="form-group">
            						{!! Form::label('source_campaign', __('tools.campaign')) !!}
            						{!! Form::select("source_campaign", [null=>'Select One'] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
            					</div>

	                            <div class="form-group">
            						{!! Form::label('source_subcampaign', __('tools.subcampaign')) !!}
            						{!! Form::select("source_subcampaign", [null=>'Select One'], null, ["class" => "form-control", 'id'=> 'subcampaign_select']) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('filter_type', __('tools.filter_type')) !!}
            						{!! Form::select("filter_type", array(null=>'Select One', 'lead_age' => 'Lead Age', 'lead_attempts' => '# of Attempts on Lead', 'days_called' => 'Distinct Days Leads are Called'), null, ["class" => "form-control", 'id'=> 'filter_type', 'required'=>true]) !!}
            					</div>

								<div class="form-group">
									{!! Form::label('filter_value', __('tools.days_to_filter')) !!}
									{!! Form::text('filter_value', null, ['class'=>'form-control filter_value', 'required'=>true]) !!}
								</div>

								<div class="form-group">
            						{!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
            						{!! Form::select("destination_campaign", [null=>'Select One'] +$campaigns, null, ["class" => "form-control", 'id'=> 'destination_campaign', 'required'=>true]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('destination_subcampaign', __('tools.destination_subcampaign_ques')) !!}
            						{!! Form::select("destination_subcampaign",  [null=>'Select One'], null, ["class" => "form-control", 'id'=> 'destination_subcampaign']) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('description', __('tools.description')) !!}
            						{!! Form::textarea("description", null, ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
            					</div>

								{!! Form::submit(__('tools.add_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
	                        {!! Form::close() !!}
	                    </div>
	                </div>

	                <div class="col-sm-8">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-cog"></i> {{__('tools.rules')}}</h2>
							<div class="table-responsive">
		                        <table class="table rules_table mt20">

		                        	@if(!count($lead_rules))
										<div class="alert alert-info">{{__('tools.no_rules')}}</div>
									@else
			                            <thead>
			                            	<tr>
			                            	    <th>{{__('tools.name')}}</th>
			                            	    <th>{{__('tools.campaign')}}</th>
			                            	    <th>{{__('tools.subcampaign')}}</th>
			                            	    <th>{{__('tools.filter_type')}}</th>
			                            	    <th>{{__('tools.filter_value')}}</th>
			                            	    <th>{{__('tools.destination_campaign')}}</th>
			                            	    <th>{{__('tools.destination_subcampaign')}}</th>
			                            	    <th>{{__('tools.edit')}}</th>
			                            	    <th>{{__('tools.delete')}}</th>
			                            	</tr>
			                            </thead>
			                        @endif

									<tbody>
			                            @foreach($lead_rules as $lr)
											<tr data-ruleid="{{$lr->id}}">
												<td>{{$lr->rule_name}}</td>
												<td>{{$lr->source_campaign}}</td>
												<td>{{$lr->source_subcampaign}}</td>
												<td>{{$lr->filter_type}}</td>
												<td>{{$lr->filter_value}}</td>
												<td>{{$lr->destination_campaign}}</td>
												<td>{{$lr->destination_subcampaign}}</td>
												<td><a class="edit_rules" href="{{ url('/dashboards/tools/edit_rule/'.$lr->id) }}" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fas fa-edit"></i></a></td>
												<td><a data-toggle="modal" data-target="#deleteRuleModal" class="remove_user" href="#" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fa fa-trash-alt"></i></a></td>
			                            @endforeach
									</tbody>
		                        </table>
							</div>
	                    </div>
	                </div>
	            </div>
			</div>
		</div>
	</div>
</div>

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteRuleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_rule')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="rule_id" name="rule_id" value="">
                <input type="hidden" class="name" name="name" value="">
               <h3>{{__('tools.confirm_delete')}} <span class="rule_name"></span>?</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
	            <button type="button" class="btn btn-danger delete_rule">{{__('tools.delete_rule')}}</button>
	        </div>
	    </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection

