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
                    <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> Add Touch</h2>
                        {{-- <a href="{{ action("PlaybookTouchController@index", [$contacts_playbook->id])}}" class="btn btn-secondary flt_rgt mb0 mt20">Go Back</a> --}}
                        {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 add_touch']) !!}
                        <input type="hidden" class="playbook_id" name="playbook_id" value="{{$contacts_playbook->id}}">
                        <div class="card">
                            <div class="form-group">
                                {!! Form::label('rule_name', __('tools.rule_name')) !!}
                                {!! Form::text('rule_name', null, ['class'=>'form-control rule_name', 'required'=>true]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="row">
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
                                <datalist id="subcamps" class="subcampaigns"></datalist>
                            </div>

                        </div>
                    </div>
                </div> --}}

                <div class="row leadfilter_row">
                    <div class="col-sm-3 pr0">
                        <div class="flowchart_element condition mt35"><span>{{__('general.when')}}</span></div>
                        <div class="vertical-line"></div>
                    </div>

                    <div class="col-sm-9 pl0 mbp0">
                        <div class="card condition">

                            <div class="form-group">
                                {!! Form::label('filter_type', __('tools.filter_type')) !!}
                                {!! Form::select("filter_type", array(null=>__('general.select_one'), 'lead_age' => __('tools.lead_age'), 'lead_attempts' => __('tools.lead_attempts'), 'days_called' => __('tools.days_called')), null, ["class" => "form-control lead_rule_filter_type", 'required'=>true]) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('filter_value', __('tools.days_to_filter')) !!}
                                {!! Form::text('filter_value', null, ['class'=>'form-control lead_rule_filter_value', 'required'=>true, 'id'=>'']) !!}
                            </div>

                            <a href="#" class="add_leadrule_filter"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>

                            <div class="alert alert-danger filter_error mt20">{{__('tools.filter_error')}}</div>

                        </div>
                    </div>
                </div>

                <div class="row campaign_row">
                    <div class="col-sm-3 pr0">
                        <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
                        <div class="vertical-line hidetilloaded"></div>
                    </div>

                    <div class="col-sm-9 pl0 mbp0">
                        <div class="card" id="action">
                            <div class="form-group">
                                {!! Form::label('destination_campaign', __('tools.destination_campaign_ques')) !!}
                                {!! Form::select("destination_campaign", [null=>__('general.select_one')] +$campaigns, null, ["class" => "form-control destination_campaign", 'id'=> 'destination_campaign', 'required'=>true]) !!}
                            </div>

                            <div class="form-group">
                                <label for="subcamps">{{__('tools.destination_subcampaign_ques')}}</label>
                                <input autocomplete="off" list="destination_subcampaign" name="destination_subcampaign" class="form-control destination_subcampaign" />
                                <datalist id="destination_subcampaign" class="destination_subcampaign subcampaigns"></datalist>
                            </div>
                            <a href="#" class="add_campaign"><i class="fas fa-plus-circle"></i> {{__('tools.add_campaign')}}</a>
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
                            {!! Form::submit(__('tools.add_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
                            <div class="alert alert-danger add_rule_error mt20"></div>
                        </div>
                    {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection

