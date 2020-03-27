@extends('layouts.dash')

@section('title', 'KPI ' . __('kpi.recipients'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">
    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container-full mt20">
                <div class="row">

                    <div class="col-sm-5 col-sm-push-7 create_recips">
                        <h2>{{__('kpi.create_recip')}}</h2>

                        {!! Form::open(['method'=> 'POST', 'action' => 'KpiController@addRecipient', 'class' => 'form user_email_form card clear add_recipient display', 'data-kpi'=> "1"]) !!}

                            <div class="form-group searchCnt">
                                {!! Form::label('name', __('general.full_name')) !!}
                                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=> true, 'autocomplete' => 'new-password']) !!}
                                <div class="search_results"></div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('email', __('general.email')) !!}
                                {!! Form::email('email', null, ['class'=>'form-control email']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('phone', __('general.phone')) !!}
                                {!! Form::tel('phone', null, ['class'=>'form-control phone']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('kpi_list', 'KPIs') !!}
                                {!! Form::select("kpi_list[]", $all_kpis, null, ["class" => "form-control multiselect", 'id'=> 'kpi_select','multiple'=>true]) !!}
                            </div>

                            {!! Form::hidden('redirect_url', 'recipients', ['class'=>'redirect_url']) !!}
                            {!! Form::submit(__('general.submit'), ['class'=>'btn btn-primary btn-md mb0']) !!}

                            @if($errors->any())
                                <div class="alert alert-danger mt20">
                                @foreach($errors->all() as $e)
                                    <li>{{$e}}</li>
                                @endforeach
                                </div>
                            @endif
                        {!! Form::close() !!}

                    </div>

                    <div class="col-sm-7 col-sm-pull-5 expanded_emails display recips">
                        <h2>{{__('kpi.recipients')}}</h2>

                        @forelse($recipients as $recipient)
                            <div class="user clear" id="{{ $recipient->id }}">
                                @if(Auth::user()->isType('demo') && $recipient->user_id != Auth::user()->id)
                                    @php
                                    $recipient->email = preg_replace('/^.\K|.(?=.*@)|@.\K|\..*(*SKIP)(*F)|.(?=.*\.)/im', '*', $recipient->email);
                                    @endphp
                                @endif
                                <p class="name">{{ $recipient->name }}</p>
                                <p class="email">{{ $recipient->email }}</p>
                                <p class="phone">{{ $recipient->phone }}</p>
                                @if(!Auth::user()->isType('demo') || $recipient->user_id == Auth::user()->id)
                                    <a class="edit_recip_glyph" data-toggle="modal" data-target="#editRecipModal" href="#" data-recip="{{ $recipient->id }}" data-userid="{{$recipient->id}}" data-username="{{$recipient->name}}"><i class="fas fa-user-edit"></i></a>
                                    <a class="remove_recip_glyph" data-toggle="modal" data-target="#deleteRecipModal" href="#" data-recip="{{ $recipient->id }}" data-userid="{{$recipient->id}}" data-username="{{$recipient->name}}"><i class="fas fa-trash-alt"></i></a>
                                    @if(Auth::user()->isType('superadmin'))
                                        <a class="kpi_change_details" href="{{action('KpiController@auditRecipient', ['id' => $recipient->id])}}"><i class="fas fa-link"></i></a>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <div class="alert alert-info">{{__('kpi.no_recips')}}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('kpi.confirm_recipient_removal')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="fromall" name="fromall" value="1">
               <h3>{{__('tools.confirm_delete')}} <span class="username"></span>?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
            <button type="button" class="btn btn-danger remove_recip">{{__('kpi.delete_recipient')}}</button>
        </div>
    </div>
    </div>
</div>

@include('shared.editrecipmodal');

@include('shared.reportmodal')

@endsection