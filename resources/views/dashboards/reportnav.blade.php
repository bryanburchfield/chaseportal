@if(!session()->has('isSso'))
    <div class="btn-group flt_rgt">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = '{{ action('AutomatedReportController@automatedReports') }}';">
            <span>{{__('general.auto_reports')}}</span>
        </button>
    </div>
@elseif(session()->has('isSsoSuperadmin'))
    <div class="btn-group flt_rgt">
        <div class="form-group">
            <select name="tz" id="tz" class="form-control">
                @foreach ($timezone_array as $key => $name)
                    <option {{ Auth::user()->tz == $key ? 'selected' : '' }} value="{{$key}}">{{$name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="btn-group flt_rgt">
        <div class="form-group">
            <select name="group_id" id="group_id" class="form-control">
                @foreach ($groups as $group)
                    <option {{ Auth::user()->group_id == $group->GroupId ? 'selected' : '' }} value="{{$group->GroupId}}">{{$group->GroupId}} : {{$group->GroupName}}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif
