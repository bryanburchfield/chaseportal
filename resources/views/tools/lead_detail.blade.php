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
                        <h3><i class="fas fa-id-card"></i> FullName</h3>
                    </div>

                    <div class="col-sm-4">
                        <form action="{{ action('LeadsController@getLead') }}" class="form" method="POST">
                            @csrf
                            <div class="input-groupx">
                                <input type="text" class="form-control" placeholder="Search" name="id">
                                <div class="input-groupx-btn">
                                    <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if ($lead)
                        <div class="col-sm-12 lead_details">
                            <div class="bt bb mt50 mb30 pt10 pb10">
                                <div class="col-sm-3">
                                    <h4 class="mb10">Lead ID</h4>
                                    <p>{{ $lead->id }}</p>
                                </div>

                                <div class="col-sm-3">
                                    <h4 class="mb10">ClientId</h4>
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
                        </div>

                        <div class="col-sm-8">
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
                                        <input type="text" class="form-control" name="LastName" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="Address" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="City" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" class="form-control" name="State" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Zip Code</label>
                                        <input type="text" class="form-control" name="ZipCode" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Primary Phone</label>
                                        <input type="text" class="form-control" name="PrimaryPhone" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Secondary Phone</label>
                                        <input type="text" class="form-control" name="SecondaryPhone" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Rep</label>
                                        <input type="text" class="form-control" name="Rep" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Call Status</label>
                                        <input type="text" class="form-control" name="CallStatus" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" class="form-control" name="Date" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Campaign</label>
                                        <input type="text" class="form-control" name="Campaign" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Attempt</label>
                                        <input type="text" class="form-control" name="Attempt" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Group Id</label>
                                        <input type="text" class="form-control" name="GroupId" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Was Dialed</label>
                                        <input type="text" class="form-control" name="WasDialed" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Last Updated</label>
                                        <input type="text" class="form-control" name="LastUpdated" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Id Guid</label>
                                        <input type="text" class="form-control" name="IdGuid" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Reload Date</label>
                                        <input type="text" class="form-control" name="ReloadDate" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Reload Attempt</label>
                                        <input type="text" class="form-control" name="ReloadAttempt" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Subcampaign</label>
                                        <input type="text" class="form-control" name="Subcampaign" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Call Type</label>
                                        <input type="text" class="form-control" name="CallType" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Disposition Id</label>
                                        <input type="text" class="form-control" name="DispositionId" value="">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="Notes" id="Notes" cols="30" rows="10" class="form-control">
                                            {{ old('Notes', $lead->Notes) }}
                                        </textarea>
                                    </div>
                                </div>

                                {{-- @foreach($lead->customFields() as $field)
                                    key = <b>{{ $field['key'] }}</b> description = <b>{{ $field['description'] }}</b> value = <b>{{ $field['value'] }}</b> <br>
                                @endforeach --}}

                            </div>
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