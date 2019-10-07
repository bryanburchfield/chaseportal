@extends('layouts.dash')

@section('title', 'KPI Recipients')

@section('content')

<div class="preloader"></div>

<div class="wrapper">
    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container-full mt20">
                <div class="row">

                    <div class="col-sm-7 expanded_emails display">
                        <h2>Recipients</h2>

                        @foreach($recipients as $recipient)
                            <div class="user clear" id="{{ $recipient->id }}">
                                <p class="name">{{ $recipient->name }}</p>
                                <p class="email">{{ $recipient->email }}</p>
                                <p class="phone">{{ $recipient->phone }}</p>
                                <a class="edit_recip_glyph" data-toggle="modal" data-target="#editRecipModal" href="#" data-recip="{{ $recipient->id }}" data-userid="{{$recipient->id}}" data-username="{{$recipient->name}}"><i class="fas fa-user-edit"></i></a>
                                <a class="remove_recip_glyph" data-toggle="modal" data-target="#deleteRecipModal" href="#" data-recip="{{ $recipient->id }}" data-userid="{{$recipient->id}}" data-username="{{$recipient->name}}"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-sm-5 create_recips">
                        <h2>Create Recipient</h2>

                        {!! Form::open(['method'=> 'POST', 'action' => 'KpiController@addRecipient', 'class' => 'form user_email_form card clear add_recipient display', 'data-kpi'=> "1"]) !!}

                            <div class="form-group">
                                {!! Form::label('name', 'Full Name') !!}
                                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=> true, 'autocomplete' => 'new-password']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('email', 'Email Address') !!}
                                {!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('phone', 'Phone') !!}
                                {!! Form::tel('phone', null, ['class'=>'form-control phone']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('kpis', 'KPIs') !!}
                                {!! Form::select("kpis[]", $all_kpis, null, ["class" => "form-control multiselect", 'id'=> 'kpi_select','multiple'=>true]) !!}
                            </div>

                            {!! Form::hidden('redirect_url', 'recipients', ['class'=>'redirect_url']) !!}
                            {!! Form::submit('Submit', ['class'=>'btn btn-primary btn-md mb0']) !!}

                        {!! Form::close() !!}

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
                <h4 class="modal-title" id="myModalLabel">Confirm Recipient Removal</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="fromall" name="fromall" value="1">
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger remove_recip">Delete User</button>
        </div>
    </div>
    </div>
</div>

<!-- Edit Recipient Modal -->
<div class="modal fade" id="editRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Recipient</h4>
            </div>

            <div class="modal-body">
                {!! Form::open(['method'=> 'POST', 'action' => 'KpiController@updateRecipient', 'class' => 'form clear display']) !!}

                <div class="form-group">
                    {!! Form::label('name', 'Full Name') !!}
                    {!! Form::text('edit_name', null, ['class'=>'form-control edit_name', 'required'=> true, 'autocomplete' => 'new-password']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('email', 'Email Address') !!}
                    {!! Form::email('edit_email', null, ['class'=>'form-control edit_email', 'required'=>true]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('phone', 'Phone') !!}
                    {!! Form::tel('edit_phone', null, ['class'=>'form-control edit_phone']) !!}
                </div>

                <div class="kpi_list"></div>

                {!! Form::hidden('recipient_id', '', ['class'=>'recipient_id']) !!}

                @if (old('recipient_id'))
                    {!! Form::hidden('edit_form_submitted', '1', ['class'=>'edit_form_submitted']) !!}
                @endif

                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Submit', ['class'=>'btn btn-warning btn-md mb0']) !!}
                {!! Form::close() !!}

                @if($errors->any())
                <div class="alert alert-danger mt20">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection