<div class="row">
    <div class="col-sm-6">
        <div class="filter_time_camp_dets">
            <p>
                <span class="selected_datetime"></span> |
                <span class="selected_campaign"></span>
            </p>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="filters">
            <div class="input-group">
                <div class="input-group-btn">
                    <div class="btn-group flt_rgt">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span>{{__('general.interaction')}}</span>
                        </button>

                        <ul class="dropdown-menu filter_campaign stop-propagation">
                            <div class="form-group mb0">
                                <input type="text" class="form-control campaign_search" placeholder="{{__('general.search')}}">
                                <input type="hidden" class="campaign_search_url" value="/dashboards/campaign_search">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block select_campaign"><i class="glyphicon glyphicon-ok"></i> {{__('general.submit')}}</button>

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

                    <div class="btn-group flt_rgt">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span>{{__('general.date')}}</span>
                        </button>
                        <ul class="dropdown-menu date_filters">
                            <li {!! ($dateFilter == 'today') ? 'class="active"' : '' !!}><a href="#" data-datefilter="today">{{__('general.today')}}</a></li>
                            <li {!! ($dateFilter == 'yesterday') ? 'class="active"' : '' !!}><a href="#" data-datefilter="yesterday">{{__('general.yesterday')}}</a></li>
                            <li {!! ($dateFilter == 'week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="week">{{__('general.this_week')}}</a></li>
                            <li {!! ($dateFilter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">{{__('general.this_month')}}</a></li>
                            <li {!! ($dateFilter == 'custom') ? 'class="active"' : '' !!}><a href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">{{__('general.custom')}}</a></li>
                        </ul>
                    </div>

                    <li class="btn-group flt_rgt">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span><i class="fas fa-globe-americas"></i> Language</span>
                        </button>

                        <ul class="dropdown-menu lang_select stop-propagation">
                            <li><a class="dropdown-item" href="{{url('lang/en')}}">English</a></li>
                            <li><a class="dropdown-item" href="{{url('lang/es')}}"> Español</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </div>
</div>
