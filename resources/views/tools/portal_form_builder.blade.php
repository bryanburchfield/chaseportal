@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
	            <div class="row">
					<div class="col-sm-12">
						<h2>Form Builder</h2>
						<hr>
					</div>	

	            	<div class="col-sm-4 mt30 elements_col">
	            	    <div class="elements">
	            	    	<h3>Fields</h3>

	            	    	<div id="components-container" class="form-horizontal">

	            	    		{{-- TEXT INPUT --}}
	            	    		<div class="col-xs-6">
	            	    			<div class="component">
	            	    				<h4><i class="fas fa-font"></i> Text Input</h4>
	            	    			</div>
	            	    		</div>

	            	    		<div class="component hidetilloaded">
	            	    			<div class="form-group" data-type="text">
	            	    			    <label class="control-label" for="text_input">Text Input</label>
	            	    			    <div class="controls">
	            	    			        <input type="text" name="" disabled id="text_input" placeholder="placeholder" class="form-control">
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
	            	    			    <label class="control-label" for="password_input">Password Input</label>
	            	    			    <div class="controls">
	            	    			        <input type="password" name="" disabled id="password_input" placeholder="placeholder" class="form-control">
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
	            	    			    <label class="control-label" for="phone_input">Phone Input</label>
	            	    			    <div class="controls">
	            	    			        <input type="tel" name="" disabled id="phone_input" placeholder="555-123-1234" class="form-control">
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
	            	    			    <label class="control-label" for="email_input">Email Input</label>
	            	    			    <div class="controls">
	            	    			        <input type="email" name="" disabled id="email_input" placeholder="ex:johndoe@gmail.com" class="form-control">
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
		            	    	        <label class="control-label" for="textarea">Textarea</label>
		            	    	        <div class="controls">
		            	    	            <textarea name="" class="form-control" id="textarea" placeholder="placeholder"></textarea>
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
		            	    	        <label class="control-label" for="select_basic">Select - Basic</label>
		            	    	        <div class="controls">
		            	    	            <select class="form-control" name="" id="select_basic">
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
		            	    	        <label class="control-label" for="select_multiple">Select - Multiple</label>
		            	    	        <div class="controls">
		            	    	            <select name="" class="form-control" id="select_multiple" multiple="multiple" size="3">
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
		            	    	        <label class="control-label">Checkboxes</label>
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

	            	    	    {{-- RADIO --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="far fa-circle"></i> Radio Buttons</h4>
		            	    		</div>
		            	    	</div>
	            	    	    
		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="radio">
		            	    	        <label class="control-label">Radio Buttons</label>
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


	            	    	    {{-- BUTTON --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="fas fa-plus-square"></i> Button</h4>
		            	    		</div>
		            	    	</div>

		            	    	<div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="button">
		            	    	        <input type="submit" class="btn btn-primary" value="Submit">
		            	    	    </div>
	            	    	    </div>
	            	    	    {{-- BUTTON --}}

	            	    	    {{-- STATIC TEXT --}}
	            	    	    <div class="col-xs-6">
		            	    		<div class="component">
		            	    			<h4><i class="fas fa-subscript"></i> Static Text</h4>
		            	    		</div>
		            	    	</div>

	            	    	    <div class="component hidetilloaded">
		            	    	    <div class="form-group" data-type="static_text">
		            	    	        <label class="control-label">Static Text</label>
		            	    	        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
		            	    	    </div>
		            	    	</div>
		            	    	{{-- STATIC TEXT --}}
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

	            	            <div class="tab-pane" id="source-tab">
	            	                <textarea id="source"></textarea>
	            	            </div>
	            	        </div>
	            	    </div>
	            	</div>
	                
	            </div>

	            <div class="row">
	            	<div class="col-sm-12">
	            		<div class="form_preview"><div id="source"></div></div>
	            	</div>
	            </div>
	        </div>

		</div>
	</div>
	@include('shared.notifications_bar')
</div>


{{-- <!-- Options Modal -->
    <div class="modal fade" id="options_modal" tabindex="-1" role="dialog" aria-labelledby="options_modal_label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="options_modal_label">Options</h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="save_options">Save changes</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> --}}

@include('shared.reportmodal')
@endsection