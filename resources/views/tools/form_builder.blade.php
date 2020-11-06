@extends('layouts.master')
@section('title', __('tools.form_builder'))

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
			    		<div class="card oa p0">
			    			<div class="form-group theme_selector pb20">
			    				<label>Select a Theme</label>
			    				<select name="theme_selector" class="form-control">
			    					<option value="">Select One</option>
			    					<option value="light">Light</option>
			    					<option value="clean">Clean</option>
			    					<option value="rounded">Rounded</option>
			    					<option value="focus">Focus</option>
			    				</select>
			    			</div>

    						<div class="form-group">
    							<label>Group ID</label>
    							<input type="text" class="form-control" name="group_id" id="group_id">
    						</div>

    						<div class="form-group">
    			                {!! Form::label('db', 'Database') !!}
    			                {!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
    			            </div>

    						<div class="form-group">
    							<label>Table</label>
    							<select name="client_table" id="client_table" class="form-control"></select>
    						</div>

    						<div class="alert alert-warning hidetilloaded">No Tables Found</div>

<div class="form_element_options">
<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" data-type="input" class="add_element mt10 input"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">
<div class="form-group">
	<label>Label</label>
	<input type="text" class="form-control default" placeholder="" name="" field-name="" value="">
</div>
</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" data-type="textarea" class="add_element mt10 textarea"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">

<div class="form-group">
	<label>Label</label>
	<textarea class="form-control default" rows="3" placeholder=""></textarea>
</div>

</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" data-type="checkbox" class="add_element mt10 checkbox"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">

<div class="form-group">
	<label><input type="checkbox" value=""> Option one </label>
</div>

</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" data-type="radio" class="add_element mt10 radio"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">
<div class="form-group">
	<label class="radio-inline"><input type="radio" name="optradio" value="option1"> Option one</label>
</div>
</div>
</div>

</div>
</div>
</div>

					<div class="col-sm-8">
						<div class="card form_preview oa">

<div class="user_created_form_element" data-id="1"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Client ID</label>
	<input type="text" class="form-control" name="ClientId" field-name="ClientId" id="ClientId">
</div></div></div>

<div class="user_created_form_element" data-id="2"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>First Name</label>
	<input type="text" class="form-control" name="FirstName" field-name="FirstName" id="FirstName">
</div></div></div>

<div class="user_created_form_element" data-id="3"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Last Name</label>
	<input type="text" class="form-control" name="LastName" field-name="LastName" id="LastName">
</div></div></div>

<div class="user_created_form_element" data-id="4"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Primary Phone</label>
	<input type="text" class="form-control" name="PrimaryPhone" field-name="PrimaryPhone" id="PrimaryPhone">
</div></div></div>

<div class="user_created_form_element" data-id="5"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Address</label>
	<input type="text" class="form-control" name="Address" field-name="Address" id="Address">
</div></div></div>

<div class="user_created_form_element" data-id="6"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>City</label>
	<input type="text" class="form-control" name="City" field-name="City" id="City">
</div></div></div>

<div class="user_created_form_element" data-id="7"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>State</label>
	<input type="text" class="form-control" name="State" field-name="State" id="State">
</div></div></div>

<div class="user_created_form_element" data-id="8"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Zip Code</label>
	<input type="text" class="form-control" name="ZipCode" field-name="ZipCode" id="ZipCode">
</div></div></div>

<div class="user_created_form_element" data-id="9"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Notes</label>
	<input type="text" class="form-control" name="Notes" field-name="Notes" id="Notes">
</div></div></div>

<div class="user_created_form_element" data-id="10"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Campaign</label>
	<input type="text" class="form-control" name="Campaign" field-name="Campaign" id="Campaign">
</div></div></div>

<div class="user_created_form_element" data-id="11"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">
	<label>Subcampaign</label>
	<input type="text" class="form-control" name="Subcampaign" field-name="Subcampaign" id="Subcampaign">
</div></div></div>

<div class="user_created_form_element btn_div"><div class="col-sm-1"></div><div class="col-sm-11 user_created_element"><input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary"></div></div>
</div>

						<div class="copy_code hidetilloaded"><pre><code></code></pre></div>

						<div class="card hidetilloaded form_code_preview">
							<div class="form_code" data-toggle="tooltip"  title="Link Copied!">
								{{-- <pre class="p10 appended_code sh_html btn_code xml"><input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary"></pre> --}}
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

<!-- Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="reportModalLabel">{{__('general.edit_field')}}</h4>
            </div>

            <div class="modal-body fc_style">

            	<div class="form-group">
            		<label>Field Label</label>
            		<input type="text" class="field_label form-control" name="field_label">
            	</div>

            	<div class="form-group">
            		<label>Field Name</label>
            		<input type="text" class="field_name form-control" name="field_name">
            	</div>

            	<div class="form-group hidetilloaded numb_fields">
            		<label># of Fields</label>
            		<select name="numb_fields" id="numb_fields" class="form-control">
            			<option value="">Select One</option>
            			<option value="1">1</option>
            			<option value="2">2</option>
            			<option value="3">3</option>
            			<option value="4">4</option>
            			<option value="5">5</option>
            			<option value="6">6</option>
            			<option value="7">7</option>
            			<option value="8">8</option>
            		</select>
            	</div>

            	<div class="form-group hidetilloaded mt20 stacked">
            		<h5 class="mb5"><b>Display Type</b></h5>
            		<div class="radio">
						<label><input type="radio" name="display_type" class="display_type" value="inline" checked>Inline</label>
	            	</div>

	            	<div class="radio">
						<label><input type="radio" name="display_type" class="display_type stacked_radio" value="stacked" checked>Stacked</label>
	            	</div>

	            	<div class="alert alert-info mt20">Checkboxes and radio inputs will appear as shown from selection</div>
            	</div>

            	<div class="form-group hidetilloaded mt20 inline">
            		<h5 class="mb5"><b>Display Type</b></h5>
            		<label class="radio-inline"><input type="radio" class="display_type inline_radio" name="display_type" value="inline" checked> Inline</label>
            		<label class="radio-inline"><input type="radio" class="display_type" name="display_type" value="stacked"> Stacked</label>
            		<div class="alert alert-info mt20">Checkboxes and radio inputs will appear as shown from selection</div>
            	</div>

            	<input type="hidden" class="id" value="">
            </div>
           
            <div class="modal-footer">
                <button type="button" class="btn btn-default mr10 flt_lft" data-dismiss="modal" tabindex="-1">{{__('general.close')}}</button>
                <a href="#" class="edit_field btn-primary btn flt_lft">{{__('general.save')}}</a>
                <a href="#" class="add_field btn-primary btn flt_lft hidetilloaded">{{__('general.add')}}</a>
            </div>
            {!! Form::close() !!}

        </div>
    </div>
</div>

@endsection