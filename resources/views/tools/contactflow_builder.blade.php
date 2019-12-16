@php
if (Auth::user()->isType('demo')) {
	$demo = true;
} else {
	$demo = false;
}
@endphp
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
			    	<div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="{{url('/tools/contactflow_builder')}}">Contact Flow Builder</a></li>
                            <li><a href="{{url('/tools/dnc_importer')}}">DNC Importer</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active mt30" id="contactflow_builder">
                                <h2 class="bbnone">Contact Flow Builder</h2>

                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#lead_rules" data-toggle="tab">{{__('tools.lead_rules')}}</a></li>
                                    <li><a href="#add_rule" data-toggle="tab">{{__('tools.add_new_rule')}}</a></li>
                                    <li><a href="#move_history" data-toggle="tab">{{__('tools.move_history')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="lead_rules">
                                        <div class="col-sm-12 nopad">
                                            <h2 class="page_heading"><i class="fa fa-cog"></i> {{__('tools.rules')}}</h2>
                                            <div class="table-responsive">
                                                <table class="table rules_table mt20">

                                                    @if(!count($lead_rules))
                                                        <div class="alert alert-info">{{__('tools.no_rules')}}</div>
                                                    @else
                                                        <thead>
                                                            <tr>
                                                                <th>{{__('tools.active')}}</th>
                                                                <th>{{__('tools.name')}}</th>
                                                                <th>{{__('tools.campaign')}}</th>
                                                                <th>{{__('tools.subcampaign')}}</th>
                                                                <th>{{__('tools.filter_type')}}</th>
                                                                <th>{{__('tools.filter_value')}}</th>
                                                                <th>{{__('tools.destination_campaign')}}</th>
                                                                <th>{{__('tools.destination_subcampaign')}}</th>
                                                                @if(!$demo)
                                                                <th>{{__('tools.edit')}}</th>
                                                                <th>{{__('tools.delete')}}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                    @endif

                                                    <tbody>
                                                        @foreach($lead_rules as $lr)
                                                            <tr data-ruleid="{{$lr->id}}">
                                                                <td>
                                                                    <label class="switch leadrule_switch">
                                                                        <input type="checkbox" {{ ($lr->active) ? 'checked' : '' }} name="leadrule_input">
                                                                        <span></span>
                                                                    </label>
                                                                </td>
                                                                <td>{{$lr->rule_name}}</td>
                                                                <td>{{$lr->source_campaign}}</td>
                                                                <td>{{$lr->source_subcampaign}}</td>
                                                                <td>{{$lr->filter_type}}</td>
                                                                <td>{{$lr->filter_value}}</td>
                                                                <td>{{$lr->destination_campaign}}</td>
                                                                <td>{{$lr->destination_subcampaign}}</td>
                                                                @if(!$demo)
                                                                <td><a class="edit_rules" href="{{ url('/tools/contactflow_builder/edit_rule/'.$lr->id) }}" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a data-toggle="modal" data-target="#deleteRuleModal" class="remove_user" href="#" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                                                @endif
                                                                @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="add_rule">

                                        <div class="container">
                                            <div class="row">
                                                <div class="col-sm-9 col-sm-offset-3 pl0">
                                                    <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.add_new_rule')}}</h2>
                                                    {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 add_rule']) !!}

                                                    <div class="card">
                                                        <div class="form-group">
                                                            {!! Form::label('rule_name', __('tools.rule_name')) !!}
                                                            {!! Form::text('rule_name', null, ['class'=>'form-control rule_name', 'required'=>true]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3 pr0">
                                                    <div class="flowchart_element when"><span>{{__('general.when')}}</span></div>
                                                    <div class="vertical-line"></div>
                                                </div>

                                                <div class="col-sm-9 pl0">
                                                    <div class="card" id="when">
                                                        <div class="form-group">
                                                            {!! Form::label('source_campaign', __('tools.campaign')) !!}
                                                            {!! Form::select("source_campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
                                                        </div>

                                                        <div class="form-group">
                                                            {!! Form::label('source_subcampaign', __('tools.subcampaign')) !!}
                                                            {!! Form::text("source_subcampaign", null, ["class" => "form-control source_subcampaign"]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3 pr0">
                                                    <div class="flowchart_element condition mt35"><span>{{__('general.condition')}}</span></div>
                                                    <div class="vertical-line"></div>
                                                </div>

                                                <div class="col-sm-9 pl0">
                                                    <div class="card" id="condition">

                                                        <div class="form-group">
                                                            {!! Form::label('filter_type', __('tools.filter_type')) !!}
                                                            {!! Form::select("filter_type", array(null=>__('general.select_one'), 'lead_age' => __('tools.lead_age'), 'lead_attempts' => __('tools.lead_attempts'), 'days_called' => __('tools.days_called')), null, ["class" => "form-control", 'id'=> 'filter_type', 'required'=>true]) !!}
                                                        </div>

                                                        <div class="form-group">
                                                            {!! Form::label('filter_value', __('tools.days_to_filter')) !!}
                                                            {!! Form::text('filter_value', null, ['class'=>'form-control filter_value', 'required'=>true]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3 pr0">
                                                    <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
                                                </div>

                                                <div class="col-sm-9 pl0">
                                                    <div class="card" id="action">
                                                        <div class="form-group">
                                                            {!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
                                                            {!! Form::select("destination_campaign", [null=>__('general.select_one')] +$campaigns, null, ["class" => "form-control", 'id'=> 'destination_campaign', 'required'=>true]) !!}
                                                        </div>

                                                        <div class="form-group">
                                                            {!! Form::label('destination_subcampaign', __('tools.destination_subcampaign_ques')) !!}
                                                            {!! Form::text("destination_subcampaign", null, ["class" => "form-control destination_subcampaign"]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-9 col-sm-offset-3 pl0">
                                                    <div class="card">
                                                        <div class="form-group">
                                                            {!! Form::label('description', __('tools.description')) !!}
                                                            {!! Form::textarea("description", null, ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
                                                        </div>

                                                        {!! Form::submit(__('tools.add_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
                                                        <div class="alert alert-danger add_rule_error mt20"></div>
                                                    </div>
                                                {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="move_history">
                                        <div class="col-sm-12 nopad">
                                            <div class="card">
                                                <h2 class="page_heading"><i class="fa fa-history"></i> {{__('tools.lead_move_history')}}</h2>

                                                <div class="table-responsive">
                                                    <table class="table rules_table mt20">
                                                        <thead>
                                                            <tr>
                                                                <th>{{__('tools.date')}}</th>
                                                                <th>{{__('tools.rule_name')}}</th>
                                                                <th>{{__('tools.leads_moved')}}</th>
                                                                <th>{{__('tools.view_details')}}</th>
                                                                <th>{{__('tools.undo_move')}}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($history as $key => $value)
                                                                <tr>
                                                                    <td>{{$value['date']}}</td>
                                                                    <td>{{$value['rule_name']}}</td>
                                                                    <td>{{$value['leads_moved']}}</td>
                                                                    <td><a data-toggle="modal" data-target="#leadDetailsModal" class="lead_details" href="#" data-name="{{$value['rule_name']}}" data-leadid="{{$value['lead_rule_id']}}"><i class="fa fa-external-link-alt"></i></a></td>
                                                                    @if($demo)
                                                                    <td><a role="button" href="#" disabled="disabled" class="btn btn-sm btn-default disable"><i class="fa fa-history"></i> {{__('tools.undo_move')}}</a></td>
                                                                    @else
                                                                    <td><a role="button" href="#" {{$value['reversed'] ? 'disabled="disabled"' : ''}} data-leadid="{{$value['lead_move_id'] }}" class="btn btn-sm {{$value['reversed'] ? 'btn-default disable' : 'btn-danger reverse_lead_move'}}"><i class="fa fa-history"></i> {{__('tools.undo_move')}}</a></td>
                                                                    @endif
                                                                </tr>
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
				</div>
			</div>
		</div>
	</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.rule_details')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="rule_id" name="rule_id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.close')}}</button>
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

<!-- Reverse Lead Move Modal -->
<div class="modal fade" id="reverseLeadMoveModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.reverse_lead_move')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="lead_move_id" name="lead_move_id" value="">
            	<h3>{{__('tools.confirm_lead_move')}}</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
	            <button type="button" class="btn btn-danger confirm_reverse_lead_move">{{__('tools.undo_move')}}</button>
	        </div>
	    </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection

