@extends('layouts.dash')

@section('title', 'KPI Recipients')

@section('content')

<div class="preloader"></div>

@include('shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        <div class="row">

            <div class="col-sm-6">
                <h2>Add Recipients</h2>

                <form action="#" method="post" class="form user_email_form well clear add_recipient display"
                    data-kpi="1">
                    <div class="form-group">
                        <input type="text" class="form-control name" name="name" placeholder="Name" required>
                    </div>

                    <div class="form-group">
                        <input type="email" class="form-control email" name="email" placeholder="Email Address"
                            required>
                    </div>

                    <div class="form-group">
                        <input type="tel" class="form-control phone" name="phone" placeholder="Phone Number">
                    </div>

                    <input type="checkbox" class="addtoall hide" checked name="addtoall" value="1">

                    <input type="hidden" name="redirect_url" value="recipients.php" class="redirect_url">

                    <input type="submit" class="btn btn-primary btn-md mb0" value="Submit">
                </form>
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
@endsection