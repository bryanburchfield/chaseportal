    <div class="col-sm-6 mb0 card">
        <h2 class="page_heading">{{ ($mode == 'edit') ? 'Edit' : 'New' }} Client</h2>

        {!! Form::open(['method'=>'POST', 'url'=>'/dashboards/' . $mode . '_user', 'class'=>'form ' . $mode . '_user']) !!}

            <div class="form-group">
                {!! Form::label('user_type', 'User Type') !!}
                {!! Form::select("user_type", ['' => 'Select One', 'client' => 'Client', 'demo' => 'Demo'], null, ["class" => "form-control", 'id'=> 'user_type', 'required'=>true]) !!}
            </div>

            <div class="form-group demo_user_expiration">
                <label for="expiration" data-toggle="tooltip" data-placement="right" title="Select the length of the demo user's trial">Expiration <i class="fas fa-info-circle"></i></label>
                {!! Form::select("expiration", ['' => 'Select One', '5' => '5 Days', '10' => '10 Days', '15' => '15 Days', '30' => '30 Days'], null, ["class" => "form-control", 'id'=> 'expiration', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('group_id', 'Group ID') !!}
                {!! Form::text('group_id', null, ['class'=>'form-control group_id', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('name', 'Name') !!}
                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('email', 'Email') !!}
                {!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('phone', 'Phone') !!}
                {!! Form::text('phone', null, ['class'=>'form-control phone', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('tz', 'Timezone') !!}
                {!! Form::select("tz", $timezone_array, null, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('db', 'Database') !!}
                {!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('additional_dbs', 'Database 2') !!}
                {!! Form::select("additional_dbs", $dbs, null, ["class" => "form-control", 'id'=> 'additional_dbs']) !!}
            </div>

            @if($mode == 'edit')
            {!! Form::hidden('id', null, ['id'=>'user_id']) !!}
            @endif

            {!! Form::submit(($mode == 'edit' ? 'Update' : 'Create') . ' Client', ['class'=>'btn btn-primary mb0'] ) !!}

            <br><br>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)
                        {{ $e }}
                    @endforeach
                </div>
            @endif

        {!! Form::close() !!}
    </div>