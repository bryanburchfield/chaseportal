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
					<div class="col-sm-5">
						<ul class="nav nav-tabs portal_form_builder" role="tablist">
						    <li role="presentation" class="active"><a href="#inputs" aria-controls="inputs" role="tab" data-toggle="tab">Inputs</a></li>
						    <li role="presentation"><a href="#radio_check" aria-controls="radio_check" role="tab" data-toggle="tab">Radio/Checkboxes</a></li>
						    <li role="presentation"><a href="#selects" aria-controls="selects" role="tab" data-toggle="tab">Select</a></li>
						    <li role="presentation"><a href="#buttons" aria-controls="buttons" role="tab" data-toggle="tab">Buttons</a></li>
						    <li role="presentation"><a href="#properties" aria-controls="properties" role="tab" data-toggle="tab">Properties</a></li>
						    {{-- <li role="presentation"><a href="#rendered" aria-controls="rendered" role="tab" data-toggle="tab">Rendered</a></li> --}}
						</ul>

				    	<div class="elements draggable_elements">
				    		<form class="form-horizontal" id="components">
								<fieldset>
									<div class="tab-content elements">

										<div role="tabpanel" class="tab-pane active" id="inputs">

											<div class="form-group component" data-type="text" title="Text Input"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control mb-4' type='text' name='label' id='label'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control mb-4'>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Text input-->
												<div class="hidetilloaded">
													<div class="col-sm-6">
														<label class=" control-label valtype" for="input01" data-valtype='label'>Text input</label>
														<div class="">
															<input type="text" placeholder="placeholder" class="form-control input-md valtype" data-valtype="placeholder" >
														</div>
													</div>
												</div>
												

												<!-- Text input-->
												<label class="col-md-4 col-lg-3 control-label valtype" for="input01" data-valtype='label'>Text input</label>
												<div class="col-md-8 col-lg-9">
													<input type="text" placeholder="placeholder" class="form-control input-md valtype" data-valtype="placeholder" >
												</div>
											</div>

											<div class="form-group component" data-type="text" title="Password Input"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control mb-4' type='text' name='label' id='label'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control mb-4'>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Text input-->
												<label class="col-md-4 col-lg-3 control-label valtype" for="input01" data-valtype='label'>Password input</label>
												<div class="col-md-8 col-lg-9">
													<input type="password" placeholder="placeholder" class="form-control input-md valtype" data-valtype="placeholder" >
												</div>
											</div>

											<div class="form-group component" title="Textarea"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Textarea -->
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Textarea</label>
												<div class="col-md-8 col-lg-9">
													<div class="textarea">
														<textarea class="form-control valtype" data-valtype="checkbox" /> </textarea>
													</div>
												</div>
											</div>

											<div class="form-group component" data-type="prep-text" title="Prepended Text Input"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control mb-4' type='text' name='label' id='label'>
												<label class='control-label'>Prepend</label> <input type='text' name='prepend' id='prepend' class='form-control mb-4'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control mb-4'>
												<label class='control-label'>Help Text</label> <input type='text' name='help' id='help' class='form-control mb-4'>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Prepended text-->
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Prepended text</label>
												<div class="col-md-8 col-lg-9">
													<div class="input-group">
														<span class="input-group-addon valtype" data-valtype="prepend">^_^</span>
														<input class="form-control valtype" placeholder="placeholder" id="prependedInput" type="text" data-valtype="placeholder">
													</div>
												</div>
											</div>
										</div>

										<div role="tabpanel" class="tab-pane" id="radio_check">

											<div class="form-group component" title="Inline radioes"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Group Name Attribute</label> <input class='form-control' type='text' name='name' id='name'>
												<textarea class='form-control' style='min-height: 200px' id='inline-radios'></textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Inline radios</label>
												<div class="col-md-8 col-lg-9 valtype" data-valtype="inline-radios">

													<!-- Inline Radios -->
													<label class="radio-inline"><input type="radio" name="optradio" checked>1</label>
													<label class="radio-inline"><input type="radio" name="optradio">2</label>
													<label class="radio-inline"><input type="radio" name="optradio">3</label>
												</div>
											</div>

											<div class="form-group component" rel="popover" title="Multiple Radios" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Group Name Attribute</label> <input class='form-control' type='text' name='name' id='name'>
												<label class='control-label'>Options: </label>
												<textarea class='form-control' style='min-height: 200px' id='radios'></textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Radio buttons</label>
												<div class="col-md-8 col-lg-9 valtype" data-valtype="radios">

													<!-- Multiple Radios -->
													<label class="radio">
														<input type="radio" value="Option one" name="group" checked="checked">
														Option one
													</label>
													<label class="radio">
														<input type="radio" value="Option two" name="group">
														Option two
													</label>
												</div>

											</div>

											<div class="form-group component" title="Inline Checkboxes"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<textarea class='form-control' style='min-height: 200px' id='inline-checkboxes'></textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Inline Checkboxes</label>

												<div class="col-md-8 col-lg-9 valtype" data-valtype="inline-checkboxes">
													<label class="checkbox-inline"><input type="checkbox" value="">1</label>
													<label class="checkbox-inline"><input type="checkbox" value="">2</label>
													<label class="checkbox-inline"><input type="checkbox" value="">3</label>
												</div>
											</div>

											<div class="form-group component" title="Multiple Checkboxes" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Options: </label>
												<textarea class='form-control' style='min-height: 200px' id='checkboxes'> </textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Checkboxes</label>
												<div class="col-md-8 col-lg-9 valtype" data-valtype="checkboxes">

													<!-- Multiple Checkboxes -->
													<label class="checkbox">
														<input type="checkbox" value="Option one">
														Option one
													</label>
													<label class="checkbox">
														<input type="checkbox" value="Option two">
														Option two
													</label>
												</div>

											</div>
										</div>

										<div role="tabpanel" class="tab-pane" id="selects">

											<div class="form-group component" title="Select"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control mb-4' type='text' name='label' id='label'>
												<label class='control-label'>Options: </label>
												<textarea class='form-control mb-4' style='min-height: 200px' id='option'> </textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>

												<!-- Select Basic -->
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Select - Basic</label>
												<div class="col-md-8 col-lg-9">
													<select class="form-control input-md valtype" data-valtype="option">
														<option>Enter</option>
														<option>Your</option>
														<option>Options</option>
														<option>Here!</option>
													</select>
												</div>
											</div>

											<div class="form-group component" rel="popover" title="Search Input" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Options: </label>
												<textarea class='form-control' style='min-height: 200px' id='option'></textarea>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>

												<!-- Select Multiple -->
												<label class="col-md-4 control-label valtype" data-valtype="label">Select - Multiple</label>
												<div class="col-md-4">
													<select class="form-control input-md valtype" multiple="multiple" data-valtype="option">
														<option>Enter</option>
														<option>Your</option>
														<option>Options</option>
														<option>Here!</option>
													</select>
												</div>
											</div>

										</div>

										<div role="tabpanel" class="tab-pane" id="buttons">

											<div class="form-group component" title="Buttons"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Button Text</label> <input class='form-control' type='text' name='label' id='button'>
												<label class='control-label' id=''>Type: </label>
												<select class='form-control input-md' id='color'>
												<option id='btn-default'>White</option>
												<option id='btn-primary'>Orange</option>
												<option id='btn-info'>Blue</option>
												<option id='btn-success'>Green</option>
												<option id='btn-warning'>Orange</option>
												<option id='btn-danger'>Red</option>
												</select>
												<hr/>
												<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger save_edit'>Cancel</button>
												</div>
												</form>" data-html="true"
												>
												<label class="col-md-4 col-lg-3 control-label valtype" data-valtype="label">Button</label>

												<!-- Button -->
												<div class="col-md-8 col-lg-9 valtype"  data-valtype="button">
													<button class='btn btn-success'>Button</button>
												</div>
											</div>

										</div>
										<div role="tabpanel" class="tab-pane" id="properties">
											<h2>Properties</h2>
										</div>
									</div>
									
								</fieldset>
							</form>
				    	</div>
				    </div>		

				    <div class="col-sm-7">
				    	<div class="dropzone" id="build">
				    		<form id="target" class="form-horizontal">

				    	    	<fieldset>
				    	    		<div id="legend" class="component" title="Form Title"
				    		        data-content="
				    			        <form class='form'>
				    		        		<div class='form-group col-md-12'>
				    		            		<label class='control-label'>Title</label> <input class='form-control mb-4' type='text' name='title' id='text'>
				    		            		<hr/>
				    		            	<button class='btn btn-info save_edit'>Save</button><button class='btn btn-danger cancel_edit'>Cancel</button>
				    		          		</div>
				    		        	</form>" data-html="true"
				    		        	>
				    		        	<legend class="valtype" data-valtype="text">Form Name</legend>

				    		        	<p>Drop form elements here</p>
				    	      		</div>
				    	    	</fieldset>
				    		</form>
				    	</div>
				    </div>				   
				</div>

				<div class="row">
					<div class="col-sm-12">
						<div class="form_preview">
							<textarea id="source" class="col-md-12" readonly></textarea>
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