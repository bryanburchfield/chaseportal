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

                    <div class="col-sm-8">
                        <h3><i class="fas fa-id-card"></i> {{ $lead->FirstName }} {{ $lead->LastName }}</h3>
                    </div>

                    <div class="col-sm-4">
                        <form action="{{ action('LeadsController@getLead') }}" class="form" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search" name="id">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> Search</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if ($lead)
                        <div class="col-sm-12 lead_details">

                            <div class="bt bb mt30 mb30 pt10 pb10">
                                <div class="col-sm-3">
                                    <h4 class="mb10">Lead ID</h4>
                                    <p>{{ $lead->id }}</p>
                                </div>

                                <div class="col-sm-3">
                                    <h4 class="mb10">Client Id</h4>
                                    <p>{{ $lead->ClientId }}</p>
                                </div>

                                <div class="col-sm-3">
                                    <h4 class="mb10">Phone</h4>
                                    <p>{{ $lead->PrimaryPhone }}</p>
                                </div>

                                <div class="col-sm-3">
                                    <h4 class="mb10">Last Updated</h4>
                                    <p>{{ $lead->LastUpdated }}</p>
                                </div>
                            </div>

                            <ul class="nav nav-tabs tabs lead_form_field_tabs" role="tablist">
                                <li role="presentation" class="active"><a data-toggle="tab" href="#lead_fields" >Lead Fields</a></li>
                                <li role="presentation"><a data-toggle="tab" href="#custom_fields">Custom Fields</a></li>
                            </ul>

                            <form action="#" method="POST" name="updateLead">
                                <div class="tab-content">
                                
                                    <div role="tabpanel" id="lead_fields" class="tab-pane fade in active">                
                                        <div class="col-sm-8 mt50 p0">
                                            <div class="lead_fields">
                                                
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input type="text" class="form-control" name="FirstName" value="{{ old('FirstName', $lead->FirstName) }}">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input type="text" class="form-control" name="LastName" value="{{ old('LastName', $lead->LastName) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" class="form-control" name="Address" value="{{ old('Address', $lead->Address) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>City</label>
                                                        <input type="text" class="form-control" name="City" value="{{ old('City', $lead->City) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>State</label>
                                                        <input type="text" class="form-control" name="State" value="{{ old('State', $lead->State) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Zip Code</label>
                                                        <input type="text" class="form-control" name="ZipCode" value="{{ old('ZipCode', $lead->ZipCode) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Primary Phone</label>
                                                        <input type="text" class="form-control" name="PrimaryPhone" value="{{ old('PrimaryPhone', $lead->PrimaryPhone) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Secondary Phone</label>
                                                        <input type="text" class="form-control" name="SecondaryPhone" value="{{ old('SecondaryPhone', $lead->SecondaryPhone) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Rep</label>
                                                        <input type="text" class="form-control" name="Rep" value="{{ old('Rep', $lead->Rep) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Call Status</label>
                                                        <input type="text" class="form-control" name="CallStatus" value="{{ old('CallStatus', $lead->CallStatus) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Date</label>
                                                        <input type="text" class="form-control" name="Date" value="{{ old('Date', $lead->Date) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Campaign</label>
                                                        <input type="text" class="form-control" name="Campaign" value="{{ old('Campaign', $lead->Campaign) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Attempt</label>
                                                        <input type="text" class="form-control" name="Attempt" value="{{ old('Attempt', $lead->Attempt) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Group Id</label>
                                                        <input type="text" class="form-control" name="GroupId" value="{{ old('GroupId', $lead->GroupId) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Was Dialed</label>
                                                        <input type="text" class="form-control" name="WasDialed" value="{{ old('WasDialed', $lead->WasDialed) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Last Updated</label>
                                                        <input type="text" class="form-control" name="LastUpdated" value="{{ old('LastUpdated', $lead->LastUpdated) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Id Guid</label>
                                                        <input type="text" class="form-control" name="IdGuid" value="{{ old('IdGuid', $lead->IdGuid) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Reload Date</label>
                                                        <input type="text" class="form-control" name="ReloadDate" value="{{ old('ReloadDate', $lead->ReloadDate) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Reload Attempt</label>
                                                        <input type="text" class="form-control" name="ReloadAttempt" value="{{ old('ReloadAttempt', $lead->ReloadAttempt) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Subcampaign</label>
                                                        <input type="text" class="form-control" name="Subcampaign" value="{{ old('Subcampaign', $lead->Subcampaign) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Call Type</label>
                                                        <input type="text" class="form-control" name="CallType" value="{{ old('CallType', $lead->CallType) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Disposition Id</label>
                                                        <input type="text" class="form-control" name="DispositionId" value="{{ old('DispositionId', $lead->DispositionId) }}">
                                                    </div>
                                                </div>

                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label>Notes</label>
                                                        <textarea name="Notes" id="Notes" cols="30" rows="10" class="form-control">
                                                            {{ old('Notes', $lead->Notes) }}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-sm-12">
                                                    <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" id="custom_fields" class="tab-pane fade">
                                        <div class="col-sm-8 mt50 p0">
                                            @foreach($lead->customFields() as $field)
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>{{ $field['key'] }}</label>
                                                        <input type="text" class="form-control" name="DispositionId" value="{{ old('DispositionId', $field['description']) }}">
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="col-sm-12">
                                                <input type="submit" class="btn btn-primary cb" value="Save Changes">
                                            </div>
                                        </div>
                                    </div>
                               
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Put lead# form field here --}}
                    {{-- GET http://chasedata.test/tools/lead_detail/143468 --}}

				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection