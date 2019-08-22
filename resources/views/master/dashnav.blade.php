<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span>Interaction</span>
    </button>
    <ul class="dropdown-menu filter_campaign stop-propagation">

        <div class="form-group"><input type="text" class="form-control campaign_search" placeholder="Search..."></div>
<<<<<<< HEAD
        <button type="submit" class="btn btn-primary btn-block select_campaign"><i class="glyphicon glyphicon-ok"></i> Submit</button>
        <?php 
        $t = array_search('Total', $campaign_list);
        unset($campaign_list[$t]);
        array_unshift($campaign_list, 'Total');
        ?>
        @foreach($campaign_list as $campaign)

            $checked = $campaign['selected'] ? $checked = "checked" : $checked= " ";
            <div class="checkbox">
            <label class="campaign_label"><input class="campaign_group" required type="checkbox"  {{$checked}} value="{{$campaign['value']}}" name="campaigns"><span>{{$campaign['name']}}</span></label>
            </div>
        @endforeach                                           
                                    
=======

        @foreach($campaign_list as $camprec)
            <li class="{{ ($camprec['name'] == $campaign) ? 'active' : '' }}" ><a href="#">{{ $camprec['name'] }}</a></li>
        @endforeach
>>>>>>> 729c0d930918904fa82547378e47fe2928c43240

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
        <li {!! ($datefilter == 'last_week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_week">Last Week</a></li>
        <li {!! ($datefilter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">This Month</a></li>
        <li {!! ($datefilter == 'last_month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_month">Last Month</a></li>
        <li {!! ($datefilter == 'custom') ? 'class="active"' : '' !!}><a href="#" data-datefilter="custom" data-toggle="modal" data-target="#datefilter_modal">Custom</a></li>
    </ul>
</div>
