@if ($message = Session::get('flasherror'))
    @php
        $errors['id'] = $message;
    @endphp
@endif
@if ($message = Session::get('flashsuccess'))
    @php
        $success['id'] = $message;
    @endphp
@endif
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

                    <div class="col-sm-12 mb20">
                        @if ($lead)
                            <h3><i class="fas fa-id-card"></i> {{ $lead->FirstName }} {{ $lead->LastName }}</h3>
                        @endif
                    </div>

                    <div class="col-sm-4 mt20">
                        <form action="{{ action('LeadsController@getLead') }}" class="form search_form" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search" name="id" name="search_value">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary mb10" type="submit"><i class="glyphicon glyphicon-search"></i> Search</button>
                                </div>
                            </div>

                            <label class="radio-inline">
                                <input type="radio" name="search_key" value="phone" checked> Phone
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="search_key" value="lead_id"> Lead ID
                            </label>

                            @if($success ?? '')
                                <div class="alert alert-success">
                                    @foreach ($success as $k => $message)
                                        {{ $message }}
                                    @endforeach
                                </div>
                            @endif
                            @if($errors)
                                <div class="alert alert-danger">
                                    @foreach ($errors as $k => $message)
                                        {{ $message }}    
                                    @endforeach
                                </div>
                            @endif
                        </form>
                    </div>

                    @if ($lead)
                        <div class="col-sm-12 lead_details">

                            <div class="bt bb mt30 mb30 pt10 pb10">
                                <div class="col-sm-3 mb10">
                                    <h4 class="mb10">Lead ID</h4>
                                    <p>{{ $lead->id }}</p>
                                </div>

                                <div class="col-sm-3 mb10">
                                    <h4 class="mb10">Import Date</h4>
                                    <p>{{ $lead->Date }}</p>
                                </div>

                                <div class="col-sm-3 mb10">
                                    <h4 class="mb10">Phone</h4>
                                    <p>{{ $lead->PrimaryPhone }}</p>
                                </div>

                                <div class="col-sm-3 mb10">
                                    <h4 class="mb10">Last Updated</h4>
                                    <p>{{ $lead->LastUpdated }}</p>
                                </div>
                            </div>

                            <div class="panel-group lead_fields_accordion" id="accordion" role="tablist" aria-multiselectable="true">
                                <form action="{{ action('LeadsController@updateLead',['lead' => $lead]) }}" method="POST" name="updateLead" class="form fc_style lead_fields_form">
                                @csrf
                                    <div class="panel panel-default">
                                        <div class="panel-heading" role="tab" id="headingOne">
                                            <h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Lead Fields</a></h4>
                                        </div>

                                        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                            <div class="panel-body">
                                                <div class="lead_fields">

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>First Name</label>
                                                            <input type="text" class="form-control" name="FirstName" value="{{ old('FirstName', $lead->FirstName) }}">
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>First Name: <span>{{$lead->FirstName}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Last Name</label>
                                                            <input type="text" class="form-control" name="LastName" value="{{ old('LastName', $lead->LastName) }}">
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Last Name: <span>{{$lead->LastName}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Address</label>
                                                            <input type="text" class="form-control" name="Address" value="{{ old('Address', $lead->Address) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Address: <span>{{$lead->Address}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>City</label>
                                                            <input type="text" class="form-control" name="City" value="{{ old('City', $lead->City) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>City: <span>{{$lead->City}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>State</label>
                                                            <input type="text" class="form-control" name="State" value="{{ old('State', $lead->State) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>State: <span>{{$lead->State}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Zip Code</label>
                                                            <input type="text" class="form-control" name="ZipCode" value="{{ old('ZipCode', $lead->ZipCode) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Zip Code: <span>{{$lead->ZipCode}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Primary Phone</label>
                                                            <input type="text" class="form-control" name="PrimaryPhone" value="{{ old('PrimaryPhone', $lead->PrimaryPhone) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Primary Phone: <span>{{$lead->PrimaryPhone}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Secondary Phone</label>
                                                            <input type="text" class="form-control" name="SecondaryPhone" value="{{ old('SecondaryPhone', $lead->SecondaryPhone) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Secondary Phone: <span>{{$lead->SecondaryPhone}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Rep</label>
                                                            <input type="text" class="form-control" name="Rep" value="{{ old('Rep', $lead->Rep) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Rep: <span>{{$lead->Rep}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Call Status</label>
                                                            <input type="text" class="form-control" name="CallStatus" value="{{ old('CallStatus', $lead->CallStatus) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Call Status: <span>{{$lead->CallStatus}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Campaign</label>
                                                            <input type="text" class="form-control" name="Campaign" value="{{ old('Campaign', $lead->Campaign) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Campaign: <span>{{$lead->Campaign}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Attempt</label>
                                                            <input type="text" class="form-control" name="Attempt" value="{{ old('Attempt', $lead->Attempt) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Attempt: <span>{{$lead->Attempt}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label>Subcampaign</label>
                                                            <input type="text" class="form-control" name="Subcampaign" value="{{ old('Subcampaign', $lead->Subcampaign) }}" @cannot('accessAdmin') disabled @endcannot>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Subcampaign: <span>{{$lead->Subcampaign}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label>Notes</label>
                                                            <textarea name="Notes" id="Notes" cols="30" rows="10" class="form-control" @cannot('accessAdmin') disabled @endcannot>{{ old('Notes', $lead->Notes) }}</textarea>
                                                        </div>
                                                    </div>
                                                    @endcan

                                                    @cannot('accessAdmin')
                                                    <p>Notes: <span>{{$lead->Notes}}</span></p>
                                                    @endcannot

                                                    @can('accessAdmin')
                                                        <div class="col-sm-12">
                                                            <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                                        </div>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="panel panel-default">
                                        <div class="panel-heading" role="tab" id="headingTwo">
                                            <h4 class="panel-title"><a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">Custom Fields</a></h4>
                                        </div>

                                        <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
                                            <div class="panel-body">
                                                <div class="custom_fields">
                                                    @foreach($lead->customFields() as $field)
                                                        @can('accessAdmin')
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label>{{ $field['description'] }}</label>
                                                                <input type="text" class="form-control" name="{{ $field['key'] }}" value="{{ old($field['key'], $field['value']) }}" @cannot('accessAdmin') disabled @endcannot>
                                                            </div>
                                                        </div>
                                                        @endcan

                                                        @cannot('accessAdmin')
                                                        <p>{{$field['key']}}: <span>{{$field['value']}}</span></p>
                                                        @endcannot
                                                    @endforeach

                                                    @can('accessAdmin')
                                                        <div class="col-sm-12">
                                                            <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                                        </div>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection