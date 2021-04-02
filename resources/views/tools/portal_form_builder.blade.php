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
					<div class="col-sm-8">
						<div class="dropzone" id="build">
							<form id="target" class="form-horizontal">

						    	<fieldset>
						    		<div id="legend" class="component" rel="popover" title="Form Title" trigger="manual"
							        data-content="
								        <form class='form'>
							        		<div class='form-group col-md-12'>
							            		<label class='control-label'>Title</label> <input class='form-control' type='text' name='title' id='text'>
							            		<hr/>
							            	<button class='btn btn-info'>Save</button><button class='btn btn-danger'>Cancel</button>
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

					<div class="col-sm-4">
						<ul class="nav nav-tabs" role="tablist">
						    <li role="presentation" class="active"><a href="#elements" aria-controls="elements" role="tab" data-toggle="tab">Elements</a></li>
						    <li role="presentation"><a href="#properties" aria-controls="properties" role="tab" data-toggle="tab">Properties</a></li>
						    <li role="presentation"><a href="#rendered" aria-controls="rendered" role="tab" data-toggle="tab">Rendered</a></li>
						</ul>

				    	<div class="elements draggable_elements">
				    		<form class="form-horizontal" id="components">
								<fieldset>
									<div class="tab-content elements">

										<div role="tabpanel" class="tab-pane active" id="elements">

											<div class="form-group component" data-type="text" rel="popover" title="Text Input" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control'>
												<button class='btn btn-info'>Save</button><button class='btn btn-danger'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Text input-->
												<label class="col-md-4 control-label valtype" for="input01" data-valtype='label'>Text input</label>
												<div class="col-md-8">
													<input type="text" placeholder="placeholder" class="form-control input-md valtype" data-valtype="placeholder" >
												</div>
											</div>

											<div class="form-group component" data-type="search" rel="popover" title="Search Input" trigger="manual"
											data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control'>
												<button class='btn btn-info'>Save</button><button class='btn btn-danger'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Search input-->
												<label class="col-md-4 control-label valtype" data-valtype="label">Search input</label>
												<div class="col-md-8">
													<input type="text" placeholder="placeholder" class="form-control input-md search-query valtype" data-valtype="placeholder">
													{{-- <p class="help-block valtype" data-valtype="help">Supporting help text</p> --}}
												</div>
											</div>

											<div class="form-group component" rel="popover" title="Search Input" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Options: </label>
												<textarea class='form-control' style='min-height: 200px' id='option'> </textarea>
												<hr/>
												<button class='btn btn-info'>Save</button><button class='btn btn-danger'>Cancel</button>
												</div>
												</form>" data-html="true"
												>

												<!-- Select Basic -->
												<label class="col-md-4 control-label valtype" data-valtype="label">Select - Basic</label>
												<div class="col-md-8">
													<select class="form-control input-md valtype" data-valtype="option">
														<option>Enter</option>
														<option>Your</option>
														<option>Options</option>
														<option>Here!</option>
													</select>
												</div>
											</div>

											<div class="form-group component" data-type="prep-text" rel="popover" title="Prepended Text Input" trigger="manual"
												data-content="
												<form class='form'>
												<div class='form-group col-md-12'>
												<label class='control-label'>Label Text</label> <input class='form-control' type='text' name='label' id='label'>
												<label class='control-label'>Prepend</label> <input type='text' name='prepend' id='prepend' class='form-control'>
												<label class='control-label'>Placeholder</label> <input type='text' name='placeholder' id='placeholder' class='form-control'>
												<label class='control-label'>Help Text</label> <input type='text' name='help' id='help' class='form-control'>
												<hr/>
												<button class='btn btn-info'>Save</button><button class='btn btn-danger'>Cancel</button>
												</div>
												</form>" data-html="true" 
												>

												<!-- Prepended text-->
												<label class="col-md-4 control-label valtype" data-valtype="label">Prepended text</label>
												<div class="col-md-8">
													<div class="input-group">
														<span class="input-group-addon valtype" data-valtype="prepend">^_^</span>
														<input class="form-control valtype" placeholder="placeholder" id="prependedInput" type="text" data-valtype="placeholder">
													</div>
												</div>
											</div>
										</div>

										<div role="tabpanel" class="tab-pane" id="properties">
											<h2>Properties</h2>
										</div>

										<div role="tabpanel" class="tab-pane" id="rendered">
											<textarea id="source" class="col-md-12"></textarea>
										</div>
									</div>
									
								</fieldset>
							</form>
				    	</div>
				    </div>						   
				</div>

				<div class="row">
					<div class="col-sm-12">
						<div class="form_preview">
							
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