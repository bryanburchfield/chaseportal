    <div class="col-sm-6 mb0 card">
        <h2 class="page_heading">{{ ($mode == 'edit') ? 'Edit' : 'New' }} User</h2>

        {!! Form::open(['method'=>'POST', 'url'=>'/dashboards/' . $mode . '_user', 'class'=>'form ' . $mode . '_user']) !!}

            @can('accessSuperAdmin')
            <div class="form-group">
                {!! Form::label('group_id', 'Group ID') !!}
                {!! Form::text('group_id', null, ['class'=>'form-control group_id', 'required'=>true]) !!}
            </div>
            @endcan
            @cannot('accessSuperAdmin')
                {!! Form::hidden('group_id', Auth::User()->group_id, ['group_id'=>Auth::User()->group_id]) !!}
            @endcannot

            <div class="form-group">
                {!! Form::label('name', 'Name') !!}
                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('email', 'Email') !!}
                {!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('user_type', 'User Type') !!}
                {!! Form::select("user_type", $user_types, null, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
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

            {{-- <div class="form-group">
                {!! Form::label('additional_dbs', 'Database 2') !!}
                {!! Form::select("additional_dbs", $dbs, null, ["class" => "form-control", 'id'=> 'additional_dbs']) !!}
            </div> --}}

            @if($mode == 'edit')
                {!! Form::hidden('id', null, ['id'=>'user_id']) !!}
            @endif

            {!! Form::submit(($mode == 'edit' ? 'Update' : 'Create') . ' User', ['class'=>'btn btn-primary mb0'] ) !!}

            <div class="alert alert-danger mt20"></div>

        {!! Form::close() !!}
    </div>