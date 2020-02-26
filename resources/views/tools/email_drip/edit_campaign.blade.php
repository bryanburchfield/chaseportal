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
                        @include('tools.shared.topnav', ['toolpage' => 'email_drip'])
                    </div>

                    <div class="col-sm-6">
                        <div class="card mt30">
                            <form action="/tools/email_drip/update_campaign" method="post" class="form edit_campaign_form">
                                 @include('tools.email_drip.shared.campaign_form_fields')

                                <div class="alert alert-success hidetilloaded"></div>
                                <div class="alert alert-danger hidetilloaded"></div>
                                <button type="submit" class="btn btn-primary update_campaign add_btn_loader mt10 mb0">{{__('tools.update_campaign')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection