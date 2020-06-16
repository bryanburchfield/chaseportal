@php
$mode = empty($playbook_touch->id) ? 'add' : 'edit';
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
                    <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.'.$mode.'_touch')}}</h2>
                        {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 '.$mode.'_touch']) !!}
                        <input type="hidden" class="playbook_id" name="contacts_playbook_id" value="{{$playbook_touch->id}}">
                        <div class="card">
                            <div class="form-group">
                                {!! Form::label('name', __('tools.name')) !!}
                                {!! Form::text('name', $playbook_touch->name, ['class'=>'form-control name', 'required'=>true]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                @if($playbook_touch->playbook_touch_filters->isNotEmpty())
                    @foreach($playbook_touch->playbook_touch_filters as $playbook_touch_filter)
                        <div class="row leadfilter_row">

                            <div class="col-sm-3 pr0">
                                <div class="flowchart_element condition mb30"><span>{{$loop->index ?__('general.and') : __('general.when')}}</span></div>
                                <div class="vertical-line"></div>
                            </div>

                            <div class="col-sm-9 pl0 mbp0">
                                <div class="card condition">

                                    <div class="form-group">
                                        {!! Form::label('filter_type', __('tools.filter')) !!}
                                        <select name="filter_type" class="form-control filter_type">
                                            <option value="">{{__('tools.select_one')}}</option>
                                            @foreach($playbook_filters as $playbook_filter)
                                                <option {{$playbook_filter->id == $playbook_touch_filter->playbook_filter_id ? 'selected' : ''}} value="{{$playbook_filter->id}}">{{$playbook_filter->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    @if($playbook_touch->playbook_touch_filters->count() < $playbook_filters->count())
                                        <a href="#" class="add_filter"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>
                                    @endif
                                    <a href="#" class="remove_filter"><i class="fas fa-trash-alt"></i> {{__('tools.remove_filter')}}</a>
                                    <div class="alert alert-danger filter_error mt20 hidetilloaded">{{__('tools.filter_error')}}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row leadfilter_row">
                        <div class="col-sm-3 pr0">
                            <div class="flowchart_element condition mb30"><span>{{__('general.when')}}</span></div>
                            <div class="vertical-line"></div>
                        </div>

                        <div class="col-sm-9 pl0 mbp0">
                            <div class="card condition">
                                <div class="form-group">
                                    {!! Form::label('filter_type', __('tools.filter')) !!}
                                    <select name="filter_type" class="form-control filter_type">
                                        <option value="">{{__('tools.select_one')}}</option>
                                        @foreach($playbook_filters as $playbook_filter)
                                            <option value="{{$playbook_filter->id}}">{{$playbook_filter->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($playbook_filters->count() > 1)
                                    <a href="#" class="add_filter"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>
                                @endif
                                <div class="alert alert-danger filter_error mt20 hidetilloaded">{{__('tools.filter_error')}}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($playbook_touch->playbook_touch_actions->isNotEmpty())
                    @foreach($playbook_touch->playbook_touch_actions as $playbook_touch_action)
                        <div class="row action_row">
                            <div class="col-sm-3 pr0">
                                <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
                                <div class="vertical-line {{$playbook_touch->playbook_touch_actions->count() == $loop->index + 1 ? 'hidetilloaded' : ''}}"></div>
                            </div>

                            <div class="col-sm-9 pl0 mbp0">
                                <div class="card" id="action">
                                    <div class="form-group">
                                        {!! Form::label('actions', __('tools.action')) !!}
                                        <select name="action_type" class="form-control action_type">
                                            <option value="">{{__('tools.select_one')}}</option>
                                            @foreach($playbook_actions as $playbook_action)
                                                <option {{$playbook_action->id == $playbook_touch_action->playbook_action_id ? 'selected' : ''}} value="{{$playbook_action->id}}">{{$playbook_action->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($playbook_touch->playbook_touch_actions->count() < $playbook_actions->count())
                                        <a href="#" class="add_action"><i class="fas fa-plus-circle"></i> {{__('tools.add_action')}}</a>
                                    @endif
                                    <a href="#" class="remove_action"><i class="fas fa-trash-alt"></i> {{__('tools.remove_action')}}</a>
                                    <div class="alert alert-danger action_error mt20 hidetilloaded">{{__('tools.action_error')}}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row action_row">
                        <div class="col-sm-3 pr0">
                            <div class="flowchart_element action"><span>{{__('general.actiontaken')}}</span></div>
                            <div class="vertical-line hidetilloaded"></div>
                        </div>

                        <div class="col-sm-9 pl0 mbp0">
                            <div class="card" id="action">
                                <div class="form-group">
                                    {!! Form::label('actions', __('tools.action')) !!}
                                    <select name="action_type" class="form-control action_type">
                                        <option value="">{{__('tools.select_one')}}</option>
                                        @foreach($playbook_actions as $playbook_action)
                                            <option value="{{$playbook_action->id}}">{{$playbook_action->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($playbook_actions->count() > 1)
                                    <a href="#" class="add_action"><i class="fas fa-plus-circle"></i> {{__('tools.add_action')}}</a>
                                @endif
                                <div class="alert alert-danger action_error mt20 hidetilloaded">{{__('tools.action_error')}}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                        <a href="{{ URL::previous() }}"  class="btn btn-default btn-reset">{{__('general.cancel')}}</a>
                        {!! Form::submit(__('tools.'.$mode.'_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
                        <div class="alert alert-danger {{$mode}}_rule_error mt20"></div>
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

