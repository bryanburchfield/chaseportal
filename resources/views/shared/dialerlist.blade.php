<div class="col-sm-6 pr0 mbmt50 mbp0">
<h2 class="page_heading mb0">All Clients ({{ App\Models\User::count() }} total)</h2>
    <div class="users">
        <div class="panel-group" id="{{$mode}}_accordion" role="tablist" aria-multiselectable="true">
        @foreach (App\Models\Dialer::orderBy('dialer_numb')->get() as $dialer)
            @php
            $db = sprintf("%02d", $dialer->dialer_numb);
            $users = $dialer->users(true);
            $clients = $users->count();
            @endphp

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="{{$mode}}_heading{{$db}}">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#{{$mode}}_accordion" href="#{{$mode}}_dialer{{$db}}" aria-expanded="false" aria-controls="{{$mode}}_dialer{{$db}}">
                        Dialer {{$db}}
                        @if($clients)
                            ({{ $clients }} client{{ $clients > 1 ? 's' : '' }})
                        @endif
                        </a>
                    </h4>
                </div>
                <div id="{{$mode}}_dialer{{$db}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="{{$mode}}_heading{{$db}}">
                    <div class="panel-body">
                        <table class="table table-responsive table-striped">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Links</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr id="user{{$user->id}}" data-id="{{$user->id}}">
                                <td>{{$user->group_id}} - {{$user->name}}</td>
                                <td><a data-toggle="modal" data-target="#userLinksModal" class="user_links" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-link"></i></a></td>
                                <td><a data-dialer="{{$db}}" href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
                                <td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fa fa-trash-alt"></i></a></td>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>