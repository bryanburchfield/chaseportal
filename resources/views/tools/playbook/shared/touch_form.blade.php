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
                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> {{__('tools.add_touch')}}</h2>
                        {!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form mt20 add_touch']) !!}
                        <input type="hidden" class="playbook_id" name="playbook_id" value="{{$contacts_playbook->id}}">
                        <div class="card">
                            <div class="form-group">
                                {!! Form::label('name', __('tools.name')) !!}
                                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
                            </div>
                        </div>
                    </div>
                </div>

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
                                    @foreach($playbook_filters as $filter)
                                        <option value="{{$filter->id}}">{{$filter->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(count($playbook_filters) > 1)
                                <a href="#" class="add_filter"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>
                            @endif

                            <div class="alert alert-danger filter_error mt20 hidetilloaded">{{__('tools.filter_error')}}</div>

                        </div>
                    </div>
                </div>

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
                                    @foreach($playbook_actions as $pb)
                                        <option value="{{$pb->id}}">{{$pb->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(count($playbook_actions) > 1)
                                <a href="#" class="add_action"><i class="fas fa-plus-circle"></i> {{__('tools.add_action')}}</a>
                            @endif

                            <div class="alert alert-danger action_error mt20 hidetilloaded">{{__('tools.action_error')}}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 col-sm-offset-3 pl0 mbp0">
                        <a href="{{ URL::previous() }}"  class="btn btn-default btn-reset">{{__('general.cancel')}}</a>
                        {!! Form::submit(__('tools.add_rule'), ['class'=>'btn btn-primary mb0'] ) !!}
                        <div class="alert alert-danger add_rule_error mt20"></div>
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

