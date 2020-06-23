<form method="POST" action="{{action('ReportController@setGroup')}}">
    @csrf
    <input type="hidden" name="report" value="{{$report}}">

    <label for="group_id">Select Group</label>
    <select name="group_id" id="group_id">
        @foreach ($groups as $group)
            <option value="{{$group->GroupId}}">{{$group->GroupId}} : {{$group->GroupName}}</option>
        @endforeach
    </select>
    
    <button type="submit">Submit</button>
</form>