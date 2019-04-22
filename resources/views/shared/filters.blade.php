<div class="row">
    <div class="col-sm-9">
        <div class="filter_time_camp_dets">
            <p></p>
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

                        <ul class="dropdown-menu filter_campaign">
                            @foreach ($campaign_list as $k => $camp)
                            <li {!! ($camp == $campaign) ? 'class="active"' : '' !!}><a href="#">{{ $camp }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span>Date</span>
                        </button>
                        <ul class="dropdown-menu date_filters">
                            <li {!! ($datefilter == 'today') ? 'class="active"' : '' !!}><a href="#" data-datefilter="today">Today</a></li>
                            <li {!! ($datefilter == 'yesterday') ? 'class="active"' : '' !!}><a href="#" data-datefilter="yesterday">Yesterday</a></li>
                            <li {!! ($datefilter == 'week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="week">This Week</a></li>
                            <li {!! ($datefilter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">This Month</a></li>
                            <li {!! ($datefilter == 'custom') ? 'class="active"' : '' !!}><a href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">Custom</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
