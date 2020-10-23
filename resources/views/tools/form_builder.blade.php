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
			    					<option value="rounded_borders">Rounded Borders</option>
			    					<option value="another_one">Something Else</option>
			    				</select>
			    			</div>
			    			
<div class="form_element_options">
<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" class="add_element mt10 input"><i class="fas fa-plus-circle"></i> Add</a>
</div>
			    					
<div class="col-sm-10">
<div class="form-group">
	<label>Label</label>
	<input type="text" class="form-control default" placeholder="" name="" value="">
</div>
</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" class="add_element mt10 textarea"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">

<div class="form-group">
	<label>Label</label>
	<textarea class="form-control default" rows="3"></textarea>
</div>

</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" class="add_element mt10 checkbox"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">

<div class="form-group">
	<label><input type="checkbox" value=""> Option one </label>
</div>

</div>
</div>

<div class="form_option">
<div class="col-sm-2 p0">
	<a href="#" class="add_element mt10 radio"><i class="fas fa-plus-circle"></i> Add</a>
</div>

<div class="col-sm-10">
<div class="form-group">
	<label class="radio-inline"><input type="radio" name="optradio" value="option1"> Option one</label>
	{{-- <label class="radio-inline"><input type="radio" name="optradio" value="option1"> Option one</label>
	<label class="radio-inline"><input type="radio" name="optradio" value="option1"> Option one</label> --}}
</div>
</div>
</div>
</div>
</div>						
</div>

					<div class="col-sm-8">
						<div class="card form_preview oa hidetilloaded">
							
						</div>

						<div class="card hidetilloaded">
							<div class="form_code">
								<pre class="p10 sh_html codegoeshere">
									{{-- <code class="html codegoeshere p10">
									</code> --}}
								</pre>
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

            	<input type="hidden" class="id" value="">
            </div>
           
            <div class="modal-footer">
                <button type="button" class="btn btn-default mr10 flt_lft" data-dismiss="modal">{{__('general.close')}}</button>
                <a href="#" class="edit_field btn-primary btn flt_lft">{{__('general.save')}}</a>
            </div>
            {!! Form::close() !!}

        </div>
    </div>
</div>

@endsection