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
	                <div class="col-sm-4">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-plus-circle"></i> Add New Rule</h2>
	                        {!! Form::open(['method'=>'POST', 'url'=>'dashboards/tools', 'class'=>'form mt20 add_rule']) !!}

	                            <div class="form-group">
	                            	{!! Form::label('rule_name', 'Rule Name') !!}
	                            	{!! Form::text('rule_name', null, ['class'=>'form-control rule_name', 'required'=>true]) !!}
	                            </div>
								
	                            <div class="form-group">
            						{!! Form::label('source_campaign', 'Campaigns') !!}
            						{!! Form::select("source_campaign", [null=>'Select One'] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
            					</div>

	                            <div class="form-group">
            						{!! Form::label('source_subcampaign', 'Sub Campaigns') !!}
            						{!! Form::select("source_subcampaign", [null=>'Select One'], null, ["class" => "form-control", 'id'=> 'subcampaign_select']) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('filter_type', 'Filter Type') !!}
            						{!! Form::select("filter_type", array(null=>'Select One', 'lead_age' => 'Lead Age', 'lead_attempts' => '# of Attempts on Lead', 'days_called' => 'Distinct Days Leads are Called'), null, ["class" => "form-control", 'id'=> 'filter_type', 'required'=>true]) !!}
            					</div>
								
								<div class="form-group">
									{!! Form::label('filter_value', 'Days to Filter By') !!}
									{!! Form::text('filter_value', null, ['class'=>'form-control filter_value', 'required'=>true]) !!}
								</div>
								
								<div class="form-group">
            						{!! Form::label('destination_campaign', 'What would you like the destination Campaign of the lead to be after it meets criteria?') !!}
            						{!! Form::select("destination_campaign", [null=>'Select One'] +$campaigns, null, ["class" => "form-control", 'id'=> 'destination_campaign', 'required'=>true]) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('destination_subcampaign', 'What would you like the destination Subcampaign  of the lead to be after it meets criteria?') !!}
            						{!! Form::select("destination_subcampaign",  [null=>'Select One'], null, ["class" => "form-control", 'id'=> 'destination_subcampaign']) !!}
            					</div>

            					<div class="form-group">
            						{!! Form::label('description', 'Description') !!}
            						{!! Form::textarea("description", null, ["class" => "form-control", 'id'=> 'description', 'rows' => 4]) !!}
            					</div>
								
								{!! Form::submit('Add Rule', ['class'=>'btn btn-primary mb0'] ) !!}
								
    							@if($errors->any())
                                    <div class="alert alert-danger mt20">
                                        @foreach($errors->all() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </div>
    							@endif
	                        {!! Form::close() !!}
	                    </div>
	                </div>

	                <div class="col-sm-8">
	                    <div class="card">
	                        <h2 class="page_heading"><i class="fa fa-cog"></i> Rules</h2>

	                        <table class="table table-responsive rules_table mt20">

	                        	@if(!count($lead_rules))
									<div class="alert alert-info">No Rules have been added yet</div>
								@else
		                            <thead>
		                            	<tr>
		                            	    <th>Name</th>
		                            	    <th>Campaigns</th>
		                            	    <th>SubCampaigns</th>
		                            	    <th>Filter Type</th>
		                            	    <th>Filter Value</th>
		                            	    <th>Destination Campaign</th>
		                            	    <th>Destination SubCampaign</th>
		                            	    <th>Edit</th>
		                            	    <th>Delete</th>
		                            	</tr>
		                            </thead>
		                        @endif

								<tbody>
																
		                            @foreach($lead_rules as $lr)
										<tr data-ruleid="{{$lr->id}}">
											<td>{{$lr->rule_name}}</td>
											<td>{{$lr->source_campaign}}</td>
											<td>{{$lr->source_subcampaign}}</td>
											<td>{{$lr->filter_type}}</td>
											<td>{{$lr->filter_value}}</td>
											<td>{{$lr->destination_campaign}}</td>
											<td>{{$lr->destination_subcampaign}}</td>
											<td><a class="edit_rules" href="{{ url('/dashboards/tools/edit_rule/'.$lr->id) }}" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fas fa-edit"></i></a></td>
											<td><a data-toggle="modal" data-target="#deleteRuleModal" class="remove_user" href="#" data-name="{{$lr->rule_name}}" data-user="{{$lr->id}}"><i class="fa fa-trash-alt"></i></a></td>
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

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteRuleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Delete Rule</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="rule_id" name="rule_id" value="">
                <input type="hidden" class="name" name="name" value="">
               <h3>Are you sure you want to delete <span class="rule_name"></span>?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger delete_rule">Delete Rule</button>
        </div>
    </div>
    </div>
</div>

@endsection