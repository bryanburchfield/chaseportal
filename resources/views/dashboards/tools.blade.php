@extends('layouts.master')
@section('title', 'Tools')

@section('content')

<div class="preloader"></div>

<div class="wrapper">
	
	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
	                <div class="col-sm-5">
	                    <div class="card">
	                        <!-- <p class="text-center">Chase Data Themed Date Picker in input below</p><br> -->
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> Add New Rule</h2>
	                        <form action="#" method="post" class="form mt20">

	                            <div class="form-group">
	                                <label>Rule Name</label>
	                                <input type="text" class="form-control rule_name" name="rule_name">
	                            </div>

	                            <div class="form-group">
	                                <label for="campaign_select">Campaigns</label>
	                                <select name="campaigns[]" id="campaign_select" multiple class="form-control multiselect" value="">
	                                    
	                                </select>
	                            </div>

	                            <div class="form-group">
	                                <label for="subcampaign_select">Sub Campaigns</label>
	                                <select name="subcampaigns[]" id="subcampaign_select" multiple class="form-control multiselect" value="">
	                                    
	                                </select>
	                            </div>

	                            <div class="form-group">
	                                <label for="filter_type">Filter Type</label>
	                                <select name="filter_type" id="filter_type" class="form-control">
	                                    <option value="">Select One</option>
	                                    <option value="lead_age">Lead Age</option>
	                                    <option value="lead_attempts"># of Attempts on Lead</option>
	                                    <option value="days_called">Distinct Days Leads are Called</option>
	                                </select>
	                            </div>

	                            <div class="form-group">
	                                <label for="filter_days">Days to Filter By</label>
	                                <input type="number" class="form-control filter_days" name="filter_days">
	                            </div>

	                            <div class="form-group">
	                                <label for="campaign_select_destination">What would you like the destination Campaign of the lead to be after it meets criteria?</label>
	                                <select name="campaign_destination[]" id="campaign_select_destination" multiple class="form-control multiselect" value="">
	                                    
	                                </select>
	                            </div>

	                            <div class="form-group">
	                                <label for="subcampaign_select_destination">What would you like the destination Subcampaign  of the lead to be after it meets criteria?</label>
	                                <select name="subcampaign_destination[]" id="subcampaign_select_destination" multiple class="form-control multiselect" value="">
	                                    
	                                </select>
	                            </div>

	                            <input type="submit" class="btn btn-primary" value="Add Rule">

	                            <div class="alert alert-danger mt20">
	                                Demo error message
	                            </div>
	                        </form>
	                    </div>
	                </div>

	                <div class="col-sm-7">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-cog"></i> Rules</h2>

	                        <table class="table mt20">
	                            <tr>
	                                <th>Rule Name</th>
	                                <th>Campaigns</th>
	                                <th>SubCampaigns</th>
	                                <th>Filter Type</th>
	                                <th>Filter Value</th>
	                                <th>Destination Campaign</th>
	                                <th>Destination SubCampaign</th>
	                            </tr>

	                            <tr>
	                                <td>asdfk</td>
	                                <td>kjhkjh</td>
	                                <td>jkh</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                            </tr>

	                            <tr>
	                                <td>asdfk</td>
	                                <td>kjhkjh</td>
	                                <td>jkh</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                            </tr>

	                            <tr>
	                                <td>asdfk</td>
	                                <td>kjhkjh</td>
	                                <td>jkh</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                            </tr>

	                            <tr>
	                                <td>asdfk</td>
	                                <td>kjhkjh</td>
	                                <td>jkh</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                            </tr>

	                            <tr>
	                                <td>asdfk</td>
	                                <td>kjhkjh</td>
	                                <td>jkh</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                                <td>kjhkj</td>
	                                <td>h</td>
	                            </tr>
	                        </table>
	                    </div>
	                </div>
	            </div> 
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection