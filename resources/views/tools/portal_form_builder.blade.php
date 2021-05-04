@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50">
	            <div class="row">
					<div class="col-sm-12">
						<h2>Form Builder</h2>
						<hr class="dk_theme">
					</div>	

	            	<div class="col-sm-4 mt30 elements_col">
	            	    <div class="elements">
	            	    	<div class="cb mb20">
	            	    		<h3 class="flt_lft">Fields</h3>
	            	    		<a href="#" class="btn btn-sm btn-info flt_rgt mt10">Save</a>
	            	    	</div>

	            	    	<div id="components-container" class="form-horizontal cb mt20">

	            	    		{{-- TEXT INPUT --}}
	            	    		<div class="col-xs-6">
	            	    			<div class="component">
	            	    				<h4><i class="fas fa-font"></i> Text Input</h4>
	            	    			</div>
	            	    		</div>

	            	    		<div class="component hidetilloaded">
	            	    			<div class="form-group" data-type="text">
	            	    			    <label for="text_input">Label</label>
	            	    			    <div class="controls">
	            	    			        <input type="text" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
	            	    			    </div>
	            	    			</div>
	            	    		</div>
	            	    		{{-- TEXT INPUT --}}

	            	    		{{-- PASSWORD INPUT --}}
	            	    		<div class="col-xs-6">
	            	    			<div class="component">
	            	    				<h4><i class="fas fa-key"></i> Password Input</h4>
	            	    			</div>
	            	    		</div>

	            	    		<div class="component hidetilloaded">
	            	    			<div class="form-group" data-type="password_input">
	            	    			    <label for="password_input">Password Label</label>
	            	    			    <div class="controls">
	            	    			        <input type="password" name="" control="input" field-name="" disabled id="password_input" placeholder="" class="form-control control-input">
	            	    			    </div>
	            	    			</div>
	            	    		</div>
	            	    		{{-- PASSWORD INPUT --}}

	            	    		{{-- PHONE INPUT --}}
	            	    		<div class="col-xs-6">
	            	    			<div class="component">
	            	    				<h4><i class="fas fa-phone"></i> Phone Input</h4>
	            	    			</div>
	            	    		</div>

	            	    		<div class="component hidetilloaded">
	            	    			<div class="form-group" data-type="phone_input">
	            	    			    <label for="phone_input">Phone Label</label>
	            	    			    <div class="controls">
	            	    			        <input type="tel" name="" control="input" field-name="" disabled id="phone_input" placeholder="555-123-1234" class="form-control control-input">
	            	    			    </div>
	            	    			</div>
	            	    		</div>
	            	    		{{-- PHONE INPUT --}}

	            	    		{{-- EMAIL INPUT --}}
	            	    		<div class="col-xs-6">
	            	    			<div class="component">
	            	    				<h4><i class="fas fa-envelope"></i> Email Input</h4>
	            	    			</div>
	            	    		</div>

	            	    		<div class="component hidetilloaded">
	            	    			<div class="form-group" data-type="email_input">
	            	    			    <label for="email_input">Email Label</label>
	            	    			    <div class="controls">
	            	    			        <input type="email" name="" control="input" field-name="" disabled id="email_input" placeholder="ex:johndoe@gmail.com" class="form-control control-input">
	            	    			    </div>
	            	    			</div>
	            	    		</div>
	            	    		{{-- EMAIL INPUT --}}

	            	    	    {{-- TEXTAREA --}}
	            	    	    <div class="col-xs-6">
	            	    	    	<div class="component">
	            	    	    		<h4><i class="fas fa-paragraph"></i> Textarea</h4>
	            	    	    	</div>
	            	    	    </div>

	            	    	    <div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="textarea">
		            	    	        <label for="textarea">Label</label>
		            	    	        <div class="controls">
		            	    	            <textarea name="" class="form-control control-input" id="textarea" control="textarea" placeholder=""></textarea>
		            	    	        </div>
		            	    	    </div>
		            	    	</div>
		            	    	{{-- TEXTAREA --}}

		            	    	{{-- SELECT BASIC --}}
		            	    	<div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="fas fa-caret-square-down"></i> Select - Basic</h4>
		            	    		</div>
		            	    	</div>

		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="select_basic">
		            	    	        <label for="select_basic">Label</label>
		            	    	        <div class="controls">
		            	    	            <select class="form-control control-select" name="" id="select_basic">
		            	    	                <option value="1">Option 1</option>
		            	    	                <option value="2">Option 2</option>
		            	    	                <option value="3">Option 3</option>
		            	    	            </select>
		            	    	        </div>
		            	    	    </div>
		            	    	</div>
		            	    	{{-- SELECT BASIC --}}

		            	    	{{-- SELECT MULTIPLE --}}
		            	    	<div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-caret-square-down"></i> Select - Multiple</h4>
		            	    		</div>
		            	    	</div>

		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="select_multiple">
		            	    	        <label for="select_multiple">Label</label>
		            	    	        <div class="controls">
		            	    	            <select name="" class="form-control control-select" id="select_multiple" multiple="multiple" size="3">
		            	    	                <option value="1">Option 1</option>
		            	    	                <option value="2">Option 2</option>
		            	    	                <option value="3">Option 3</option>
		            	    	            </select>
		            	    	        </div>
		            	    	    </div>
		            	    	</div>
	            	    	    {{-- SELECT MULTIPLE --}}

	            	    	    {{-- CHECKBOXES --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-square"></i> Checkboxes</h4>
		            	    		</div>
		            	    	</div>

		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="checkbox">
		            	    	        <label>Label</label>
		            	    	        <div class="controls">
		            	    	            <div class="checkbox"><label class="" for="checkbox_1">
		            	    	                <input type="checkbox" name="checkbox" id="checkbox_1">
		            	    	                Option 1
		            	    	            </label></div>
		            	    	            <div class="checkbox"><label class="" for="checkbox_2">
		            	    	                <input type="checkbox" name="checkbox" id="checkbox_2">
		            	    	                Option 2
		            	    	            </label></div>
		            	    	            <div class="checkbox"><label class="" for="checkbox_3">
		            	    	                <input type="checkbox" name="checkbox" id="checkbox_3">
		            	    	                Option 3
		            	    	            </label></div>
		            	    	        </div>
		            	    	    </div>
		            	    	</div>
	            	    	    {{-- CHECKBOXES --}}

	            	    	     {{-- INLINE CHECKBOXES --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-square"></i> Inline-Checkboxes</h4>
		            	    		</div>
		            	    	</div>

		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="inline_checkbox">
		            	    	        <label>Label</label>
		            	    	        <div class="controls">
		            	    	            <label class="checkbox-inline"><input type="checkbox" value="">Option 1</label>
		            	    	            <label class="checkbox-inline"><input type="checkbox" value="">Option 2</label>
		            	    	            <label class="checkbox-inline"><input type="checkbox" value="">Option 3</label>
		            	    	        </div>
		            	    	    </div>
		            	    	</div>
	            	    	    {{-- INLINE CHECKBOXES --}}

	            	    	    {{-- RADIO --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-circle"></i> Radio Buttons</h4>
		            	    		</div>
		            	    	</div>
	            	    	    
		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="radio">
		            	    	        <label>Label</label>
		            	    	        <div class="controls">
		            	    	            <div class="radio"><label class="" for="radio_1">
		            	    	                <input type="radio" name="radio" id="radio_1">
		            	    	                Option 1
		            	    	            </label></div>
		            	    	            <div class="radio"><label class="" for="radio_2">
		            	    	                <input type="radio" name="radio" id="radio_2">
		            	    	                Option 2
		            	    	            </label></div>
		            	    	            <div class="radio"><label class="" for="radio_3">
		            	    	                <input type="radio" name="radio" id="radio_3">
		            	    	                Option 3
		            	    	            </label></div>
		            	    	        </div>
		            	    	    </div>
	            	    	    </div>
	            	    	    {{-- RADIO --}}

	            	    	    {{-- INLINE RADIO --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-dot-circle"></i> Inline Radio</h4>
		            	    		</div>
		            	    	</div>
	            	    	    
		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="inline_radio">
		            	    	        <label>Label</label>
		            	    	        <div class="controls">
		            	    	            <label class="radio-inline">
		            	    	                <input type="radio" name="optradio" checked>Option 1
		            	    	            </label>
		            	    	            <label class="radio-inline">
		            	    	                <input type="radio" name="optradio">Option 2
		            	    	            </label>
		            	    	            <label class="radio-inline">
		            	    	                <input type="radio" name="optradio">Option 3
		            	    	            </label>
		            	    	        </div>
		            	    	    </div>
	            	    	    </div>
	            	    	    {{-- INLINE RADIO --}}

	            	    	    {{-- STATIC TEXT --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="fas fa-subscript"></i> Static Text</h4>
		            	    		</div>
		            	    	</div>

	            	    	    <div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="static_text">
		            	    	        <label>Static Text</label>
		            	    	        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
		            	    	    </div>
		            	    	</div>
		            	    	{{-- STATIC TEXT --}}

	            	    	    <div class="col-xs-12 submit_btns cb p0 mt20">
	            	    	    	<h4 class="ml10"><b>Submit Buttons</b></h4>

		            	    	    {{-- SUBMIT BUTTON --}}
		            	    	    <div class="col-xs-6">
			            	    		<div class="component">
			            	    			<h4><i class="fas fa-plus-square"></i> Submit</h4>
			            	    		</div>
			            	    	</div>

			            	    	<div class="component hidetilloaded">
			            	    	    <div class="form-group" data-type="button">
			            	    	        <input type="submit" class="control-submit btn btn-primary" control="submit" action="submit" disabled value="Submit">
			            	    	    </div>
		            	    	    </div>
		            	    	    {{-- SUBMIT BUTTON --}}

		            	    	    {{-- SUBMIT & NAV EMAIL BUTTON --}}
		            	    	    <div class="col-xs-6">
			            	    		<div class="component">
			            	    			<h4><i class="fas fa-plus-square"></i> Submit &amp; Email</h4>
			            	    		</div>
			            	    	</div>

			            	    	<div class="component hidetilloaded">
			            	    	    <div class="form-group" data-type="button">
			            	    	        <input type="submit" control="submit" action="submit_and_navigate_and_email" navigate-to="SCRIPT NAME" email-to="EMAIL ADDRESS OR FORM FIELD" email-template="SCRIPT NAME" value="Submit and Email" class="control-navigate-submit-email btn btn-primary">
			            	    	    </div>
		            	    	    </div>
		            	    	    {{-- SUBMIT & NAV EMAIL BUTTON --}}

		            	    	    {{-- SUBMIT & NAVIGATE BUTTON --}}
		            	    	    <div class="col-xs-6">
			            	    		<div class="component">
			            	    			<h4><i class="fas fa-plus-square"></i> Submit &amp; Navigate</h4>
			            	    		</div>
			            	    	</div>

			            	    	<div class="component hidetilloaded">
			            	    	    <div class="form-group" data-type="button">
			            	    	        <input type="submit" control="submit" action="submit_and_navigate" navigate-to="SCRIPT NAME" value="Submit and Navigate" class="control-navigate-submit btn btn-primary">
			            	    	    </div>
		            	    	    </div>
		            	    	    {{-- SUBMIT & NAVIGATE BUTTON --}}

		            	    	    {{-- SUBMIT EMAIL BUTTON --}}
		            	    	    <div class="col-xs-6">
			            	    		<div class="component">
			            	    			<h4><i class="fas fa-plus-square"></i> Submit Email</h4>
			            	    		</div>
			            	    	</div>

			            	    	<div class="component hidetilloaded">
			            	    	    <div class="form-group" data-type="button">
			            	    	        <input type="submit" control="submit" action="submit_and_email" email-to="EMAIL ADDRESS OR FORM FIELD" value="Submit and Email" class="control-submit-email btn btn-primary">
			            	    	    </div>
		            	    	    </div>
		            	    	    {{-- SUBMIT EMAIL BUTTON --}}

		            	    	    {{-- SUBMIT & EMAIL TEMPLATE BUTTON --}}
		            	    	    <div class="col-xs-6">
			            	    		<div class="component">
			            	    			<h4><i class="fas fa-plus-square"></i> Submit &amp; Email Template</h4>
			            	    		</div>
			            	    	</div>

			            	    	<div class="component hidetilloaded">
			            	    	    <div class="form-group" data-type="button">
			            	    	        <input type="submit" control="submit" action="submit_and_email_template" email-to="EMAIL ADDRESS OR FORM FIELD" email-template="SCRIPT NAME" value="Submit and Email" class="control-submit-email-template btn btn-primary">
			            	    	    </div>
		            	    	    </div>
		            	    	    {{-- SUBMIT & EMAIL TEMPLATE BUTTON --}}

		            	    	    
	            	    	    </div>

	            	    	    <div class="col-xs-12 bt mt20 p0">
	            	    	    	<h3>Prebuilts</h3>

	            	    	    	{{-- FULL ADDRESS TEMPLATE --}}
    		            	    	<div class="template_contents">
	    	            	    	    <div class="col-xs-6">
	    		            	    		<div class="component template">
	    		            	    			<h4><i class="fas fa-home"></i> Address</h4>
	    		            	    		</div>
	    		            	    	</div>

	    		            	    	@include('tools.formbuilder_options.shared.full_address')

    		            	    	</div>
    		            	    	{{-- FULL ADDRESS TEMPLATE --}}

    	            	    	    {{-- BASIC INFO TEMPLATE --}}
    	            	    	    <div class="template_contents">
	    	            	    	    <div class="col-xs-6">
	    		            	    		<div class="component template">
	    		            	    			<h4><i class="fas fa-home"></i> Basic Info</h4>
	    		            	    		</div>
	    		            	    	</div>

	    		            	    	{{--  CLIENT ID  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">Client ID</label>
			            	    			    <div class="controls">
			            	    			        <input type="text" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  CLIENT ID  --}}

			            	    		{{--  FIRST NAME  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">First Name</label>
			            	    			    <div class="controls">
			            	    			        <input type="text" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  FIRST NAME  --}}

			            	    		{{--  LAST NAME  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">Last Name</label>
			            	    			    <div class="controls">
			            	    			        <input type="text" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  LAST NAME  --}}

			            	    		{{--  PRIMARY PHONE  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">Primary Phone</label>
			            	    			    <div class="controls">
			            	    			        <input type="tel" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  PRIMARY PHONE  --}}

			            	    		@include('tools.formbuilder_options.shared.full_address')

			            	    		{{--  CAMPAIGN  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">Campaign</label>
			            	    			    <div class="controls">
			            	    			        <input type="tel" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  CAMPAIGN  --}}

			            	    		{{--  SUBCAMPAIGN  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">SubCampaign</label>
			            	    			    <div class="controls">
			            	    			        <input type="tel" name="" control="input" field-name="" disabled id="text_input" placeholder="" class="form-control control-input">
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  SUBCAMPAIGN  --}}

			            	    		{{--  NOTES  --}}
	    		            	    	<div class="component hidetilloaded template_component">
			            	    			<div class="form-group" data-type="text">
			            	    			    <label for="text_input">Notes</label>
			            	    			    <div class="controls">
			            	    			        <textarea name="" class="form-control control-input" id="textarea" control="textarea" placeholder=""></textarea>
			            	    			    </div>
			            	    			</div>
			            	    		</div>
			            	    		{{--  NOTES  --}}

	    		            	    </div>
    		            	    	{{-- BASIC INFO TEMPLATE --}}

	            	    	    </div>
	            	    	</div>

	            	    </div>

	            	    <div class="options hidetilloaded">
	            	    	<h3>Options</h3>
	            	    	<a href="#" class="back"><i class="fas fa-chevron-circle-left"></i></a>

	            	    	<div class="option_vals"></div>
	            	    </div>
	            	</div>

	            	<div class="col-sm-8">	            	   
	            	    <div class="tabbable">
	            	        <ul class="nav nav-tabs">
	            	            <li class="active"><a href="#editor-tab" data-toggle="tab">Editor</a></li>
	            	            <li><a href="#preview-tab" data-toggle="tab">Preview</a></li>
	            	            <li><a href="#source-tab" data-toggle="tab">Source</a></li>
	            	        </ul>

	            	        <div class="tab-content dropzone">
	            	            <div class="tab-pane active" id="editor-tab">
	            	                <form id="builder_content" class="form-horizontal">
	            	                    <fieldset id="content_form_name" class="mt10 mb20">
	            	                        <legend>Form Name</legend>
	            	                    </fieldset>
	            	                </form>
	            	            </div>

	            	            <div class="tab-pane" id="preview-tab">
	            	                <div class="form_preview"><div class="source"></div></div>
	            	            </div>

	            	            <div class="tab-pane" id="source-tab">
	            	                <textarea id="source"></textarea>
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