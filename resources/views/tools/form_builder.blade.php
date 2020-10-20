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
			    						<a href="#" class="add_element mt10"><i class="fas fa-plus-circle"></i> Add</a>
			    					</div>
			    					
			    					<div class="col-sm-10">
			    						<div class="form-group">
			    							<label>Label</label>
			    							<input type="text" class="form-control default" placeholder="">
			    						</div>
			    					</div>
			    				</div>

			    				<div class="form_option">
			    					<div class="col-sm-2 p0">
			    						<a href="#" class="add_element mt10"><i class="fas fa-plus-circle"></i> Add</a>
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
			    						<a href="#" class="add_element mt10"><i class="fas fa-plus-circle"></i> Add</a>
			    					</div>

			    					<div class="col-sm-10">
			    						<div class="form-group">
			    							<label>
			    							    <input type="checkbox" value="">
			    							    Option one 
			    							  </label>
			    						</div>
			    					</div>
			    				</div>
			    				
			    				<div class="form_option">
			    					<div class="col-sm-2 p0">
			    						<a href="#" class="add_element mt10"><i class="fas fa-plus-circle"></i> Add</a>
			    					</div>

				    				<div class="col-sm-10">
				    					<div class="form-group">
				    						<label>
				    						    <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1">
				    						    Option one
				    						</label>
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
								<pre class="p10">
									<code class="html codegoeshere p10">
									</code>
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

@endsection