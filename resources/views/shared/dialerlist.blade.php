@php

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
        <div class="accordion" id="{{$mode}}_accordion" role="tablist" aria-multiselectable="false">

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

            <div class="card mb-0 p-0">
                <div class="card-header p-2" id="{{$mode}}_heading{{$db}}">
                    <h5 class="card-title mb-0">
                        <a class="collapsed" data-toggle="collapse" data-target="#{{$mode}}_dialer{{$db}}" aria-expanded="false" aria-controls="{{$mode}}_dialer{{$db}}">
                        {{__('users.dialer')}} {{$db}}

                        @if($users->count())
                            (
                            @can('accessSuperAdmin')
                                {{__('users.clients')}}: {{ $client_count }},
                            @endcan
                            {{__('users.users')}}: {{ $users->count() }}
                            )
                        @endif
                        <span class="float-right"><i class="fas fa-chevron-up"></i></span>
                        </a>
                    </h5>
                </div>
                {{-- {{Auth::User()->isType('superadmin') ? 'collapse' : ''}} --}}
                <div id="{{$mode}}_dialer{{$db}}" class="collapse" role="tablist" aria-labelledby="{{$mode}}_heading{{$db}}" data-parent="#{{$mode}}_accordion">

                    @can('accessSuperAdmin')
                        @php
                            $groups = $users->unique('group_id')->pluck('group_id')->sort()->values();
                        @endphp

                        <div class="accordion nested p-3" id="{{$mode}}_group_accordion{{$db}}" role="tablist" aria-multiselectable="false">
                            @foreach($groups as $id)
                            <div class="card mb-0 p-0">
                                <div class="card-header p-2" id="group_heading{{$id}}">
                                    <h5 class="card-title mb-0">
                                        <a class="collapsed" data-toggle="collapse" data-target="#{{$mode}}_group_{{$db}}_{{$id}}" aria-expanded="false" aria-controls="{{$mode}}_group_{{$db}}_{{$id}}">{{$id}} <span class="float-right"><i class="fas fa-chevron-up"></i></span></a>
                                    </h5>
                                </div>

                                <div id="{{$mode}}_group_{{$db}}_{{$id}}" class="collapse" role="tablist" aria-labelledby="group_heading{{$id}}" data-parent="#{{$mode}}_dialer{{$db}}">
                                    <div class="card-body">
                                        <table class="table table-responsive table-striped nobdr">
                                            <thead>
                                                <tr>
                                                    <th>{{__('users.name')}}</th>
                                                    <th>{{__('users.user_type')}}</th>
                                                    <th>{{__('users.links')}}</th>
                                                    <th>{{__('users.edit')}}</th>
                                                    <th>{{__('users.delete')}}</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                            @foreach($users->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $user)
                                                @if($id == $user->group_id)
                                                    <tr id="user{{$user->id}}" data-id="{{$user->id}}">
                                                    <td>{{$user->name}}</td>
                                                    <td>{{$user->user_type}}</td>
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
                    @endcan

                    @cannot('accessSuperAdmin')
                        <div class="card-body">
                            <table class="table table-responsive table-striped nobdr">
                                <thead>
                                    <tr>
                                        <th>{{__('users.name')}}</th>
                                        <th>{{__('users.user_type')}}</th>
                                        <th>{{__('users.links')}}</th>
                                        <th>{{__('users.edit')}}</th>
                                        <th>{{__('users.delete')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($users->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $user)
                                    <tr id="user{{$user->id}}" data-id="{{$user->id}}">
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->user_type}}</td>
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