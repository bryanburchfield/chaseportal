@if(!session()->has('isSso'))
    <div class="btn-group flt_rgt">

        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = '{{ action('AutomatedReportController@automatedReports') }}';">
            <span>{{__('general.auto_reports')}}</span>
        </button>
    </div>
@elseif(session()->has('isSsoSuperadmin'))
    <div class="btn-group flt_rgt col-sm-4 p0">
        <div class="form-group sso">
            <select name="tz" id="tz" class="form-control">
                @foreach ($timezone_array as $key => $name)
                    <option {{ Auth::user()->tz == $key ? 'selected' : '' }} value="{{$key}}">{{$name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="btn-group flt_rgt col-sm-4">
        <div class="form-group sso">
            <select name="group_id" id="group_id" class="form-control">
                @if (Auth::user()->group_id == -1 )
                    <option {{ Auth::user()->group_id == -1 ? 'selected' : '' }} value="-1">Select Group</option>
                @endif
                @foreach ($groups as $group)
                    <option {{ Auth::user()->group_id == $group->GroupId ? 'selected' : '' }} value="{{$group->GroupId}}">{{$group->GroupId}} : {{$group->GroupName}}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif
