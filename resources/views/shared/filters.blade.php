<div class="row">
    <div class="col-sm-9">
        <div class="filter_time_camp_dets">
            <p>
                <span class="selected_datetime"></span> |
                <span class="selected_campaign"></span>
            </p>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="filters">
            <div class="input-group">
                <div class="input-group-btn">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span>Interaction</span>
                        </button>

                        <ul class="dropdown-menu filter_campaign stop-propagation">
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
                        <ul class="dropdown-menu date_filters">
                            <li {!! ($dateFilter == 'today') ? 'class="active"' : '' !!}><a href="#" data-datefilter="today">Today</a></li>
                            <li {!! ($dateFilter == 'yesterday') ? 'class="active"' : '' !!}><a href="#" data-datefilter="yesterday">Yesterday</a></li>
                            <li {!! ($dateFilter == 'week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="week">This Week</a></li>
                            <li {!! ($dateFilter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">This Month</a></li>
                            <li {!! ($dateFilter == 'custom') ? 'class="active"' : '' !!}><a href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">Custom</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
