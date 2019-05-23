@extends('layouts.dash')

@section('title', 'KPI Recipients')

@section('content')

<div class="preloader"></div>

<div class="wrapper">
    @include('shared.sidenav')

    <div id="content">
        @include('shared.reportnav')

        <div class="container-fluid bg dashboard p20">
            <div class="container mt20">
                <div class="row">

                    <div class="col-sm-6">
                        <h2>Add Recipients</h2>

                        {!! Form::open(['method'=> 'POST', 'action' => 'KpiController@addRecipient', 'class' => 'form user_email_form, well clear add_recipient display', 'data-kpi'=> "1"]) !!}

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
                                {!! Form::tel('phone', null, ['class'=>'form-control phone', 'required'=>true]) !!}                                
                            </div>
                            
                            {!! Form::hidden('redirect_url', 'recipients', ['class'=>'redirect_url']) !!}
                            {!! Form::submit('Submit', ['class'=>'btn btn-primary btn-md mb0']) !!}

                        {!! Form::close() !!}
                    </div>

                    <div class="col-sm-6 expanded_emails display">
                        <h2>Remove Recipients</h2>
                        @foreach($recipients as $recipient)
                            <div class="user clear" id="{{ $recipient->id }}">
                                <p class="name">{{ $recipient->name }}</p>
                                <p class="email">{{ $recipient->email }}</p>
                                <p class="phone">{{ $recipient->phone }}</p>
                                <a class="remove_recip_fromall" data-toggle="modal" data-target="#deleteRecipModal" href="#" data-recip="{{ $recipient->id }}" data-userid="{{$recipient->id}}" data-username="{{$recipient->name}}"><i class="glyphicon glyphicon-remove-sign"></i></a>
                            </div>
                        @endforeach
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
                
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::open(['method'=> 'POST', 'url'=> 'kpi/optout/']) !!}
                    {!! Form::hidden('recipient_id', null, ['id'=>'userid']) !!}
                    {!! Form::hidden('username', null, ['id'=>'username']) !!}
                    {!! Form::submit('Delete User', ['class'=>'btn btn-danger']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@endsection