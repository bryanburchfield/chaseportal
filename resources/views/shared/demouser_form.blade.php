{!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'fc_style form demo_user '. $mode .'_demo_user' ]) !!}

	<div class="form-group">
	    {!! Form::label('name', 'Name') !!}
	    {!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
	</div>

	<div class="form-group">
	    {!! Form::label('email', 'Email') !!}
	    {!! Form::email('email', null, ['class'=>'form-control email']) !!}
	</div>

	<div class="form-group">
	    {!! Form::label('phone', 'Phone') !!}
	    {!! Form::text('phone', null, ['class'=>'form-control phone', 'required'=>true]) !!}
	</div>

	<div class="form-group demo_user_expiration">
	    <label for="expiration" data-toggle="tooltip" data-placement="right" title="Select the length of the demo client's trial">{{$mode == 'edit' ? 'Extend Demo' : 'Expiration'}} <i class="fas fa-info-circle"></i></label>
	    {!! Form::select("expiration", ['' => 'Select One', '5' => '5 Days', '10' => '10 Days', '15' => '15 Days', '30' => '30 Days'], null, ["class" => "form-control", 'id'=> 'expiration', ($mode == 'add' ? 'required' : '')]) !!}
	</div>

	<div class="alert alert-danger mt20"></div>

    <div class="form-group mb20">
    	{!! Form::submit(($mode == 'edit' ? 'Update' : 'Create'), ['class'=>'btn btn-primary mb0 mb20'] ) !!}
    </div>

{!! Form::close() !!}