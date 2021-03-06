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
                    @include('tools.shared.topnav', ['toolpage' => 'rules'])
                        <div class="tab-content">
                            <div class="tab-pane active mt30" id="contactflow_builder">
                                <h2 class="bbnone">{{__('tools.contact_flowbuilder')}}</h2>

                                <ul class="nav nav-tabs tabs tools_subnav">
                                    <li class="active"><a href="#lead_rules" data-toggle="tab">{{__('tools.lead_rules')}}</a></li>
                                    <li><a href="#add_rule" data-toggle="tab">{{__('tools.add_new_rule')}}</a></li>
                                    <li><a href="#move_history" data-toggle="tab">{{__('tools.move_history')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="lead_rules">
                                        <div class="col-sm-12 p0">
                                            <h2 class="page_heading"><i class="fa fa-cog"></i> {{__('tools.rules')}}</h2>
                                            <div class="table-responsive nobdr">
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
                                                                        @if ($demo)
                                                                        <input disabled type="checkbox" {{ ($lr->active) ? 'checked' : '' }} name="leadrule_input">
                                                                        @else
                                                                        <input type="checkbox" {{ ($lr->active) ? 'checked' : '' }} name="leadrule_input">
                                                                        @endif
                                                                        <span></span>
                                                                    </label>
                                                                </td>
                                                                <td>{{$lr->rule_name}}</td>
                                                                <td>{{$lr->source_campaign}}</td>
                                                                <td>{{$lr->source_subcampaign}}</td>
                                                                <td>
                                                                @foreach ($lr->leadRuleFilters as $lrf)
                                                                    {{$lrf->type}}@if (!$loop->last) <br> @endif
                                                                @endforeach
                                                                </td>
                                                                <td>
                                                                @foreach ($lr->leadRuleFilters as $lrf)
                                                                    {{$lrf->value}}@if (!$loop->last) <br> @endif
                                                                @endforeach
                                                                </td>
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
                                                <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                                                    <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.add_new_rule')}}</h2>
                                                    {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 fc_style add_rule']) !!}

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
                                                    <div class="flowchart_element when"><span>{{__('general.where')}}</span></div>
                                                    <div class="vertical-line"></div>
                                                </div>

                                                <div class="col-sm-9 pl0 mbp0">
                                                    <div class="card" id="when">
                                                        <div class="form-group">
                                                            {!! Form::label('source_campaign', __('tools.campaign')) !!}
                                                            {!! Form::select("source_campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="subcamps">{{__('tools.subcampaign')}}</label>
                                                            <input autocomplete="off" list="subcamps" name="subcamps" class="form-control source_subcampaign" />
                                                            <datalist id="subcamps"></datalist>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row leadfilter_row">
                                                <div class="col-sm-3 pr0">
                                                    <div class="flowchart_element condition mt35"><span>{{__('general.when')}}</span></div>
                                                    <div class="vertical-line"></div>
                                                </div>

                                                <div class="col-sm-9 pl0 mbp0">
                                                    <div class="card" id="condition">

                                                        <div class="form-group">
                                                            {!! Form::label('filter_type', __('tools.filter_type')) !!}
                                                            <select name="filter_type" id="filter_type" class="form-control lead_rule_filter_type" required>
                                                                <option value="">{{__('general.select_one')}}</option>
                                                                <option data-filtertype="lead_age" value="lead_age">{{__('tools.lead_age')}}</option>
                                                                <option data-filtertype="lead_attempts" value="lead_attempts">{{__('tools.lead_attempts')}}</option>
                                                                <option data-filtertype="days_called" value="days_called">{{__('tools.days_called')}}</option>
                                                                <option data-filtertype="ring_group" value="ring_group">{{__('tools.ring_group')}}</option>
                                                                <option data-filtertype="call_status" value="call_status">{{__('tools.call_status')}}</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group subfilter_group" data-subfilter="lead_age">
                                                            {!! Form::label('filter_value', __('tools.days_to_filter')) !!}
                                                            {!! Form::text('filter_value', null, ['class'=>'form-control lead_rule_filter_value', 'id'=>'']) !!}
                                                        </div>

                                                        <div class="form-group subfilter_group hidetilloaded" data-subfilter="days_called">
                                                            {!! Form::label('filter_value', __('tools.days_to_filter')) !!}
                                                            {!! Form::text('filter_value', null, ['class'=>'form-control lead_rule_filter_value', 'id'=>'']) !!}
                                                        </div>

                                                        <div class="form-group subfilter_group hidetilloaded" data-subfilter="lead_attempts">
                                                            {!! Form::label('filter_value', __('tools.numb_filter_attempts')) !!}
                                                            {!! Form::text('filter_value', null, ['class'=>'form-control lead_rule_filter_value', 'id'=>'']) !!}
                                                        </div>

                                                        <div class="form-group subfilter_group hidetilloaded" data-subfilter="ring_group">
                                                            {!! Form::label('filter_type', __('tools.inbound_sources')) !!}
                                                            {!! Form::select("inbound_sources", $inbound_sources, null, ["class" => "form-control inbound_sources lead_rule_filter_value"]) !!}
                                                        </div>

                                                        <div class="form-group subfilter_group hidetilloaded" data-subfilter="call_status">
                                                            {!! Form::label('filter_type', __('tools.call_statuses')) !!}
                                                            {!! Form::select("call_statuses ", $call_statuses , null, ["class" => "form-control call_statuses lead_rule_filter_value"]) !!}
                                                        </div>

                                                        <a href="#" class="add_leadrule_filter"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>

                                                        <div class="alert alert-danger filter_error hidetilloaded mt20">{{__('tools.filter_error')}}</div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3 pr0">
                                                    <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
                                                </div>

                                                <div class="col-sm-9 pl0 mbp0">
                                                    <div class="card" id="action">
                                                        <div class="form-group">
                                                            {!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
                                                            {!! Form::select("destination_campaign", [null=>__('general.select_one')] +$campaigns, null, ["class" => "form-control", 'id'=> 'destination_campaign', 'required'=>true]) !!}
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="subcamps">{{__('tools.destination_subcampaign_ques')}}</label>
                                                            <input autocomplete="off" list="destination_subcampaign" name="destination_subcampaign" class="form-control destination_subcampaign" />
                                                            <datalist id="destination_subcampaign"></datalist>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                                                    <div class="card">
                                                        <div class="form-group">
                                                            {!! Form::label('description', __('tools.description')) !!}
                                                            {!! Form::textarea("description", null, ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
                                                        </div>

                                                        <a href="#" onclick="location.href='/tools/contactflow_builder';" class="btn btn-default btn-reset">{{__('general.cancel')}}</a>
                                                        @if ($demo)
                                                        <span class="disabled btn btn-primary mb0">{{__('tools.add_rule')}}</span>
                                                        @else
                                                        {!! Form::submit(__('tools.add_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
                                                        @endif
                                                        <div class="alert alert-danger add_rule_error hidetilloaded mt20"></div>
                                                    </div>
                                                {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="move_history">
                                        <div class="col-sm-12 p0">
                                            <div class="card">
                                                <h2 class="page_heading"><i class="fa fa-history"></i> {{__('tools.lead_move_history')}}</h2>

                                                <div class="table-responsive nobdr">
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

@include('shared.notifications_bar')

<!-- Rule Details Modal -->
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

<!-- Delete Rule Modal -->
<div class="modal fade" id="deleteRuleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_rule')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="lead_rule_id" name="lead_rule_id" value="">
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

