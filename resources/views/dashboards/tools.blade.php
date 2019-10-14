@extends('layouts.master')
@section('title', 'Tools')

@section('content')

<div class="preloader"></div>

@php
// dd($campaigns);
@endphp
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
	                <div class="col-sm-4">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> Add New Rule</h2>
	                        {!! Form::open(['method'=>'POST', 'url'=>'/dashboards/add_user', 'class'=>'form mt20 add_rule']) !!}

	                            <div class="form-group">
	                            	{!! Form::label('rule_name', 'Rule Name') !!}
	                            	{!! Form::text('rule_name', null, ['class'=>'form-control rule_name', 'required'=>true]) !!}
	                            </div>

	                            <div class="form-group">
<<<<<<< HEAD
            						{!! Form::label('campaigns', 'Campaigns') !!}
            						{!! Form::select("campaigns[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
            					</div>

	                            <div class="form-group">
            						{!! Form::label('subcampaigns', 'Sub Campaigns') !!}
            						{!! Form::select("subcampaigns[]", $subcampaigns, null, ["class" => "form-control multiselect", 'id'=> 'subcampaign_select','multiple'=>true]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('filter_type', 'Filter Type') !!}
            						{!! Form::select("filter_type", array('lead_age' => 'Lead Age', 'lead_attempts' => '# of Attempts on Lead', 'days_called' => 'Distinct Days Leads are Called'), null, ["class" => "form-control", 'id'=> 'filter_type', 'required'=>true]) !!}
            					</div>
								
								<div class="form-group">
									{!! Form::label('filter_days', 'Days to Filter By') !!}
									{!! Form::text('filter_days', null, ['class'=>'form-control filter_days', 'required'=>true]) !!}
								</div>
								
								<div class="form-group">
            						{!! Form::label('campaign_select_destination', 'What would you like the destination Campaign of the lead to be after it meets criteria?') !!}
            						{!! Form::select("campaign_select_destination[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select_destination','multiple'=>true]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('subcampaign_select_destination', 'What would you like the destination Subcampaign  of the lead to be after it meets criteria?') !!}
            						{!! Form::select("subcampaign_select_destination[]", $subcampaigns, null, ["class" => "form-control multiselect", 'id'=> 'subcampaign_select_destination','multiple'=>true]) !!}
            					</div>
								
								{!! Form::submit('Add Rule', ['class'=>'btn btn-primary mb0'] ) !!}
								
	                            <div class="alert alert-danger mt20"></div>
	                        {!! Form::close() !!}
=======
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
>>>>>>> a56bac392fbfcb9455ca1499098907fc2179a500
	                    </div>
	                </div>

	                <div class="col-sm-8">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-cog"></i> Rules</h2>

	                        <table class="table table-responsive rules_table mt20">
	                            <thead>
	                            	<tr>
	                            	    <th>Name</th>
	                            	    <th>Campaigns</th>
	                            	    <th>SubCampaigns</th>
	                            	    <th>Filter Type</th>
	                            	    <th>Filter Value</th>
	                            	    <th>Destination Campaign</th>
	                            	    <th>Destination SubCampaign</th>
	                            	    <th>Description</th>
	                            	    <th>Edit</th>
	                            	</tr>
	                            </thead>

								<tbody>

	                            @foreach($lead_rules as $lr)
									<tr data-ruleid="{{$lr->id}}">
										<td>{{$lr->name}}</td>
										<td>{{$lr->source_campaign}}</td>
										<td>{{$lr->source_subcampaign}}</td>
										<td>{{$lr->filter_type}}</td>
										<td>{{$lr->filter_value}}</td>
										<td>{{$lr->destination_campaign}}</td>
										<td>{{$lr->destination_subcampaign}}</td>
										<td>{{$lr->description}}</td>
										<td><a data-toggle="modal" data-target="#editRulesModal" class="edit_rules" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-edit"></i></a></td>
	                            @endforeach

								</tbody>

	                        </table>
	                    </div>
	                </div>
	            </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

<!-- Edit Rules Modal -->
<div class="modal fade" id="editRulesModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editModalLabel">Edit Rule</h4>
            </div>
            <div class="modal-body">

               <form action="#" method="post" class="form mt20 edit_rule">

                    <div class="form-group">
                        <label>Rule Name</label>
                        <input type="text" class="form-control rule_name" name="rule_name" />
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
                        <input type="number" class="form-control filter_days" name="filter_days" />
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

                    <div class="alert alert-danger mt20"></div>

					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <input type="submit" class="btn btn-success edit_rule" value="Edit Rule">
                    <button type="button" class="btn btn-danger delete_rule btn_flt_rgt">Delete Rule</button>
                </form>
            </div>
	    </div>
    </div>
</div>

@endsection