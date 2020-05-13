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
                        @include('tools.shared.topnav', ['toolpage' => 'playbook'])
                        
			    		<div class="tab-content">
                            <div class="tab-pane active mt30">
                                <h2 class="bbnone">{{__('tools.contacts_playbook')}}</h2>
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'sms_numbers'])

								<div class="tab-pane mt30" id="sms_numbers">
                                    <div class="col-sm-12 nopad">
                                        <div class="table-responsive nobdr sms_numbers mt20">
                                            <h1 class="display-3">Update a Number</h1>
                                            @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <br /> 
                                            @endif
                                            <form method="post" action="{{ route('sms_numbers.update', $playbook_sms_number->id) }}">
                                                @method('PATCH') 
                                                @csrf
                                                <div class="form-group">

                                                    <label for="group_id">Group:</label>
                                                    <input type="text" class="form-control" name="group_id" value={{ $playbook_sms_number->group_id }} />
                                                </div>

                                                <div class="form-group">
                                                    <label for="from_number">SMS From Number:</label>
                                                    <input type="text" class="form-control" name="from_number" value={{ $playbook_sms_number->from_number }} />
                                                </div>

                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </form>
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
</div>

@include('shared.reportmodal')

@endsection