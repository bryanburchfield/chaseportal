@if(!session()->has('isSso'))
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = '{{ url('dashboards/automatedreports') }}';">
            <span>{{__('general.auto_reports')}}</span>
        </button>
    </div>
@endif