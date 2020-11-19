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

                    <div class="col-sm-8 mb20">
                        @if ($lead)
                            <h3><i class="fas fa-id-card"></i> {{ $lead->FirstName }} {{ $lead->LastName }}</h3>
                        @endif
                    </div>

                    <div class="col-sm-4">
                        <div class="card">
                            <form action="{{ action('LeadsController@getLead') }}" class="form" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search" name="id" name="search_value">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> Search</button>
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

                            <ul class="nav nav-tabs tabs lead_form_field_tabs" role="tablist">
                                <li role="presentation" class="active"><a data-toggle="tab" href="#lead_fields" >Lead Fields</a></li>
                                <li role="presentation"><a data-toggle="tab" href="#custom_fields">Custom Fields</a></li>
                            </ul>

                            <form action="{{ action('LeadsController@updateLead',['lead' => $lead]) }}" method="POST" name="updateLead">
                                @csrf
                                <div class="tab-content">
                                
                                    <div role="tabpanel" id="lead_fields" class="tab-pane fade in active">                
                                        <div class="col-sm-12 mt50 p0">
                                            <div class="lead_fields">
                                                
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input type="text" class="form-control" name="FirstName" value="{{ old('FirstName', $lead->FirstName) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input type="text" class="form-control" name="LastName" value="{{ old('LastName', $lead->LastName) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" class="form-control" name="Address" value="{{ old('Address', $lead->Address) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>City</label>
                                                        <input type="text" class="form-control" name="City" value="{{ old('City', $lead->City) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>State</label>
                                                        <input type="text" class="form-control" name="State" value="{{ old('State', $lead->State) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Zip Code</label>
                                                        <input type="text" class="form-control" name="ZipCode" value="{{ old('ZipCode', $lead->ZipCode) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Primary Phone</label>
                                                        <input type="text" class="form-control" name="PrimaryPhone" value="{{ old('PrimaryPhone', $lead->PrimaryPhone) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Secondary Phone</label>
                                                        <input type="text" class="form-control" name="SecondaryPhone" value="{{ old('SecondaryPhone', $lead->SecondaryPhone) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Rep</label>
                                                        <input type="text" class="form-control" name="Rep" value="{{ old('Rep', $lead->Rep) }}" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Call Status</label>
                                                        <input type="text" class="form-control" name="CallStatus" value="{{ old('CallStatus', $lead->CallStatus) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Campaign</label>
                                                        <input type="text" class="form-control" name="Campaign" value="{{ old('Campaign', $lead->Campaign) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Attempt</label>
                                                        <input type="text" class="form-control" name="Attempt" value="{{ old('Attempt', $lead->Attempt) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Subcampaign</label>
                                                        <input type="text" class="form-control" name="Subcampaign" value="{{ old('Subcampaign', $lead->Subcampaign) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label>Notes</label>
                                                        <textarea name="Notes" id="Notes" cols="30" rows="10" class="form-control" @cannot('accessAdmin') disabled @endcannot>{{ old('Notes', $lead->Notes) }}</textarea>
                                                    </div>
                                                </div>

                                                @can('accessAdmin')
                                                    <div class="col-sm-12">
                                                        <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" id="custom_fields" class="tab-pane fade">
                                        <div class="col-sm-12 mt50 p0">
                                            @foreach($lead->customFields() as $field)
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>{{ $field['description'] }}</label>
                                                        <input type="text" class="form-control" name="{{ $field['key'] }}" value="{{ old($field['key'], $field['value']) }}" @cannot('accessAdmin') disabled @endcannot>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @can('accessAdmin')
                                                <div class="col-sm-12">
                                                    <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </form>
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