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
                            
                            {!! Form::hidden('redirect_url', 'recipients.php', ['class'=>'redirect_url']) !!}
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
                                <a class="remove_recip_fromall" href="#" data-recip="{{ $recipient->id }}"><i class="glyphicon glyphicon-remove-sign"></i></a>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection