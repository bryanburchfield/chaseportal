
@if($page['type'] =='dash')
    @if($has_multiple_dbs)

        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span>Database</span>
            </button>
            <ul class="dropdown-menu db_select stop-propagation">
                
                @foreach ($db_list as $db)

                    @php $checked = $db['selected'] ? $checked = "checked" : $checked= " "; @endphp
                    <div class="checkbox">
                    <label class="databases_label stop-propagation"><input class="database_group" required type="checkbox" {{$checked}} value="{{$db['database']}}" name="databases"><span>{{$db['name']}}</span></label>
                    </div>
                @endforeach                                                                         

                <input type="hidden" class="page_type" value="{{$page['type']}}">
                <button type="submit" class="btn btn-primary btn-block select_database"><i class="glyphicon glyphicon-ok"></i> Submit</button>                                        
            </ul>
        </div>

    @endif
@endif

@if($page['type']=='report')
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = 'reportsettings.php';">
            <span>Report Settings</span>
        </button>
    </div>
@endif

<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span>Interaction</span>
    </button>

    <ul class="dropdown-menu filter_campaign stop-propagation">
        <div class="form-group"><input type="text" class="form-control campaign_search" placeholder="Search..."></div>
        <button type="submit" class="btn btn-primary btn-block select_campaign"><i class="glyphicon glyphicon-ok"></i> Submit</button>
        
        @foreach($campaign_list as $campaign)
            <div class="checkbox">
                <label class="campaign_label">
                    <input class="campaign_group" required type="checkbox"  {{ $campaign['selected'] == 1 ? "checked" : '' }} value="{{$campaign['value']}}" name="campaigns">
                    <span>
                        {{$campaign['name']}}
                    </span>
                </label>
            </div>
        @endforeach
    </ul>
</div>

<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span>Date</span>
    </button>

    <?php
        $selected_date_filter = $datefilter;
        if (!in_array($selected_date_filter, ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'])) {
            $selected_date_filter = 'custom';
        }
    ?>
    <ul class="dropdown-menu date_filters">
        <li {!! ($selected_date_filter == 'today') ? 'class="active"' : '' !!}><a href="#" data-datefilter="today">Today</a></li>
        <li {!! ($selected_date_filter == 'yesterday') ? 'class="active"' : '' !!}><a href="#" data-datefilter="yesterday">Yesterday</a></li>
        <li {!! ($selected_date_filter == 'week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="week">This Week</a></li>
        <li {!! ($selected_date_filter == 'last_week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_week">Last Week</a></li>
        <li {!! ($selected_date_filter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">This Month</a></li>
        <li {!! ($selected_date_filter == 'last_month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_month">Last Month</a></li>
        <li {!! ($selected_date_filter == 'custom') ? 'class="active"' : '' !!}><a href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">Custom</a></li>
        <li><?php echo $has_multiple_dbs; ?></li>
    </ul>
</div>
