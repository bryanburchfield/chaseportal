
@if($page['type'] =='dash')
    @if($has_multiple_dbs)

        <div class="dropdown float-right d-flex"">
            <button type="button" class="btn btn-default dropdown-toggle bl_none" data-toggle="dropdown">
                <span>{{__('general.database')}}</span>
            </button>

            <ul class="dropdown-menu db_select float-right stop-propagation">
                @foreach ($db_list as $db)
                    @php $checked = $db['selected'] ? $checked = "checked" : $checked= " "; @endphp
                    <div class="checkbox list-item">
                    <label class="databases_label stop-propagation"><input class="database_group" required type="checkbox" {{$checked}} value="{{$db['database']}}" name="databases"><span>{{$db['name']}}</span></label>
                    </div>
                @endforeach

                <input type="hidden" class="page_type" value="{{$page['type']}}">
                <button type="submit" class="btn btn-primary btn-block select_database"><i class="fas fa-check"></i> {{__('general.submit')}}</button>
            </ul>
        </div>
    @endif
@endif

@if($page['type']=='report')
    <div class="dropdown d-flex float-right">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = 'reportsettings.php';">
            <span>Report Settings</span>
        </button>
    </div>
@endif

<div class="dropdown float-right">
    <button type="button" class="btn btn-default dropdown-toggle bl_none" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span>{{__('general.interaction')}}</span>
    </button>

    <div class="dropdown-menu py-0 filter_campaign stop-propagation">
        <div class="form-group mb0">
            <input type="text" class="form-control campaign_search" placeholder="{{__('general.search')}}">
            <input type="hidden" class="campaign_search_url" value="/dashboards/campaign_search">
        </div>
        <button type="submit" class="btn btn-primary btn-block select_campaign"><i class="fas fa-check"></i> {{__('general.submit')}}</button>

        @foreach($campaign_list as $campaign)
            <div class="checkbox p-2 dropdown-item">
                <label class="campaign_label">
                    <input class="campaign_group" required type="checkbox"  {{ $campaign['selected'] == 1 ? "checked" : '' }} value="{{$campaign['value']}}" name="campaigns">
                    <span>
                        {{$campaign['name']}}
                    </span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="dropdown float-right">
    <button type="button" class="btn btn-default dropdown-toggle bl_none" data-toggle="dropdown">
        <span>{{__('general.date')}}</span>
    </button>

    <?php
        $selected_date_filter = $dateFilter;
        if (!in_array($selected_date_filter, ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'])) {
            $selected_date_filter = 'custom';
        }
    ?>
    <div class="dropdown-menu py-0 date_filters" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'today') ? 'active' : '' !!}"  href="#" data-datefilter="today">{{__('general.today')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'yesterday') ? 'active' : '' !!}" href="#" data-datefilter="yesterday">{{__('general.yesterday')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'week') ? 'active' : '' !!}" href="#" data-datefilter="week">{{__('general.this_week')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'last_week') ? 'active' : '' !!}" href="#" data-datefilter="last_week">{{__('general.last_week')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'month') ? 'active' : '' !!}" href="#" data-datefilter="month">{{__('general.this_month')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'last_month') ? 'active' : '' !!}" href="#" data-datefilter="last_month">{{__('general.last_month')}}</a>
        <a class="dropdown-item py-2 {!! ($selected_date_filter == 'custom') ? 'active' : '' !!}" href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">{{__('general.custom')}}</a>
    </div>
</div>
