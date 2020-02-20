@php
    // Really shouldn't put logic inside blades...
    if(Auth::User()->isType('superadmin')) {
        $tot_user_count = App\Models\User::whereNotIn('user_type', ['demo','expired'])->count();
        $tot_client_count = App\Models\User::whereNotIn('user_type', ['demo','expired'])->distinct('group_id')->count();
    } else {
        $tot_user_count = App\Models\User::whereNotIn('user_type', ['demo','expired'])
        ->where('group_id', Auth::User()->group_id)
        ->count();
    }
@endphp
<div class="col-sm-6 pr0 mbmt50 mbp0">
<h2 class="page_heading mb0">
    @can('accessSuperAdmin')
        {{__('users.all_clients')}} ({{ $tot_client_count }})
    @endcan
    {{__('users.all_users')}} ({{ $tot_user_count }})
</h2>
    <div class="users">
        <div class="panel-group" id="{{$mode}}_accordion" role="tablist" aria-multiselectable="true">
    <?php
    //dd(App\Models\Dialer::orderBy('dialer_numb')->get());
    ?>
        @foreach (App\Models\Dialer::orderBy('dialer_numb')->get() as $dialer)
            @php
                // Bail if not superadmin and not this user's dialer
                if(!Auth::User()->isType('superadmin') && Auth::User()->db != $dialer->reporting_db) {
                    continue;
                }
                $db = sprintf("%02d", $dialer->dialer_numb);
                $users = $dialer->users(true);
                $client_count = $dialer->group_count(true);
            @endphp

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="{{$mode}}_heading{{$db}}">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#{{$mode}}_accordion" href="#{{$mode}}_dialer{{$db}}" aria-expanded="false" aria-controls="{{$mode}}_dialer{{$db}}">
                        {{__('users.dialer')}} {{$db}}

                        @if($users->count())
                            (
                            @can('accessSuperAdmin')
                                {{__('users.clients')}}: {{ $client_count }},
                            @endcan
                            {{__('users.users')}}: {{ $users->count() }}
                            )
                        @endif
                        </a>
                    </h4>
                </div>

                <div id="{{$mode}}_dialer{{$db}}" class="panel-collapse {{Auth::User()->isType('superadmin') ? 'collapse' : ''}}" role="tabpanel" aria-labelledby="{{$mode}}_heading{{$db}}">

                    @can('accessSuperAdmin')
                        @php
                            $groups = $users->unique('group_id')->pluck('group_id')->sort()->values();
                        @endphp

                        <div class="panel-body nested">
                            <div class="panel-group" id="{{$mode}}_group_accordion{{$db}}">
                                @foreach($groups as $id)
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="group_heading{{$id}}">
                                        <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse" href="#{{$mode}}_group_{{$db}}_{{$id}}" data-toggle="collapse" data-parent="#{{$mode}}_group_accordion{{$db}}" aria-expanded="false" aria-controls="{{$mode}}_group_{{$db}}_{{$id}}">{{$id}}</a>
                                        </h4>
                                    </div>

                                    <div id="{{$mode}}_group_{{$db}}_{{$id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="group_heading{{$id}}">
                                        <div class="panel-body">
                                            <table class="table table-responsive table-striped nobdr">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('users.client')}}</th>
                                                        <th>{{__('users.links')}}</th>
                                                        <th>{{__('users.edit')}}</th>
                                                        <th>{{__('users.delete')}}</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                @foreach($users->sortBy('group_id') as $user)
                                                    @if($id == $user->group_id)
                                                        <tr id="user{{$user->id}}" data-id="{{$user->id}}">
                                                        <td>{{$user->group_id}} - {{$user->name}}</td>
                                                        <td><a data-toggle="modal" data-target="#userLinksModal" class="user_links" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-link"></i></a></td>
                                                        <td><a data-dialer="{{$db}}" href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
                                                        <td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                    @endcan

                    @cannot('accessSuperAdmin')
                        <div class="panel-body">
                            <table class="table table-responsive table-striped nobdr">
                                <thead>
                                    <tr>
                                        <th>{{__('users.client')}}</th>
                                        <th>{{__('users.links')}}</th>
                                        <th>{{__('users.edit')}}</th>
                                        <th>{{__('users.delete')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($users->sortBy('group_id') as $user)
                                    <tr id="user{{$user->id}}" data-id="{{$user->id}}">
                                    <td>{{$user->group_id}} - {{$user->name}}</td>
                                    <td><a data-toggle="modal" data-target="#userLinksModal" class="user_links" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-link"></i></a></td>
                                    <td><a data-dialer="{{$db}}" href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
                                    <td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endcan
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>