    <div class="col-sm-6 mb0 card">
        <h2 class="page_heading">{{ ($mode == 'edit') ? __('users.edit') : __('users.new') }} {{__('users.user')}}</h2>

        {!! Form::open(['method'=>'POST', 'url'=>'/admin/' . $mode . '_user', 'class'=>'fc_style form ' . $mode . '_user']) !!}
            @can('accessSuperAdmin')
            <div class="form-group">
                {!! Form::label('group_id', __('users.group_id')) !!}
                {!! Form::text('group_id', null, ['class'=>'form-control group_id', 'required'=>true]) !!}
            </div>
            @endcan
            @cannot('accessSuperAdmin')
                {!! Form::hidden('group_id', Auth::User()->group_id, ['group_id'=>Auth::User()->group_id]) !!}
            @endcannot

            <div class="form-group">
                {!! Form::label('name', __('users.name')) !!}
                {!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('email', __('users.email')) !!}
                {!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('user_type', __('users.user_type')) !!}
                {!! Form::select("user_type", $user_types, null, ["class" => "form-control", 'id'=> 'user_type', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('phone', __('users.phone')) !!}
                {!! Form::text('phone', null, ['class'=>'form-control phone', 'required'=>true]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('tz', __('users.timezone')) !!}
                {!! Form::select("tz", $timezone_array, null, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
            </div>

            @can('accessSuperAdmin')
            <div class="form-group">
                {!! Form::label('db', __('users.database')) !!}
                {!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
            </div>
            @endcan
            @cannot('accessSuperAdmin')
                {!! Form::hidden('db', Auth::User()->db, ['db'=>Auth::User()->db]) !!}
            @endcannot

            {{-- <div class="form-group">
                {!! Form::label('additional_dbs', 'Database 2') !!}
                {!! Form::select("additional_dbs", $dbs, null, ["class" => "form-control", 'id'=> 'additional_dbs']) !!}
            </div> --}}

            @if($mode == 'edit')
                {!! Form::hidden('id', null, ['id'=>'user_id']) !!}
            @endif

            {!! Form::submit(($mode == 'edit' ? __('users.update') : __('users.create')) . ' User', ['class'=>'btn btn-primary mb0'] ) !!}
            
            <div class="alert alert-success mt20 hidetilloaded"></div>
            <div class="alert alert-danger mt20 hidetilloaded"></div>

        {!! Form::close() !!}
    </div>