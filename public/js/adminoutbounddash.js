Chart.pluginService.register({
  beforeDraw: function (chart) {
    if (chart.config.options.elements.center) {
      //Get ctx from string
      var ctx = chart.chart.ctx;

      //Get options from the center object in options
      var centerConfig = chart.config.options.elements.center;
      var fontStyle = centerConfig.fontStyle || 'Arial';
      var txt = centerConfig.text;
      var color =  '#203047';
      var sidePadding = centerConfig.sidePadding || 20;
      var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
      //Start with a base font of 30px
      ctx.font = "40px " + fontStyle;

      //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
      var stringWidth = ctx.measureText(txt).width;
      var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

      // Find out how much the font can grow in width.
      var widthRatio = elementWidth / stringWidth;
      var newFontSize = Math.floor(20 * widthRatio);
      var elementHeight = (chart.innerRadius * 2);

      // Pick a new font size so it will not be larger than the height of label.
      var fontSizeToUse = Math.min(newFontSize, elementHeight);

      //Set font settings to draw it correctly.
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
      var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 1.7);
      ctx.font = fontSizeToUse+"px " + fontStyle;
      ctx.fillStyle = color;

      //Draw text in center
      ctx.fillText(txt, centerX, centerY);
    }
  }
});

var Dashboard = {

    chartColors : {
        red: 'rgb(255,67,77)',
        blue: 'rgb(1,1,87)',
        orange: 'rgb(228,154,49)',
        green: 'rgb(51,160,155)',
        grey: 'rgb(98,98,98)',
        yellow: 'rgb(255, 205, 86)',
        lightblue: 'rgb(66, 134, 244)'
    },

    datefilter : document.getElementById("datefilter").value,
    inorout : document.getElementById("inorout").value,
    inorout_toggled:false,
    time: new Date().getTime(),

    init:function(){
        this.get_call_volume(this.inorout, this.datefilter, this.chartColors);
        this.agent_talk_time(this.datefilter, this.chartColors);
        this.sales_per_hour_per_rep(this.datefilter, this.chartColors);
        this.calls_by_campaign(this.datefilter, this.chartColors);
        this.total_calls(this.datefilter);
        Dashboard.eventHandlers();
        Master.check_reload();
        $('#avg_wait_time').closest('.flipping_card').flip(true);
    },

    eventHandlers:function(){
        $('.date_filters li a').on('click', this.filter_date);
        $('.filter_campaign').on('click', 'li', this.filter_campaign);
        $('.submit_date_filter').on('click', this.custom_date_filter);
        $('.card-6 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
        $('.callvolume_inorout .btn').on('click', this.call_volume_type);
    },

    display_error:function(div, textStatus, errorThrown){
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reload the page.</p>');
    },

    return_chart_colors:function(response_length, chartColors){
        const chart_colors = Object.keys(Dashboard.chartColors)
        var chart_colors_array=[];

        var j=0;
        for (var i=0; i < response_length; i++) {
            if(j==chart_colors.length){
                j=0;
            }
            chart_colors_array.push(eval('chartColors.'+chart_colors[j]));
            j++;
        }

        return chart_colors_array;
    },

    refresh:function(datefilter, campaign, inorout){

        Dashboard.get_call_volume(inorout, datefilter, Dashboard.chartColors);
        Dashboard.agent_talk_time(datefilter, Dashboard.chartColors);
        Dashboard.sales_per_hour_per_rep(datefilter, Dashboard.chartColors);
        Dashboard.calls_by_campaign(datefilter, Dashboard.chartColors);
        Dashboard.total_calls(datefilter);
        Dashboard.update_datefilter(datefilter);
        Master.check_reload();
        $('.preloader').fadeOut('slow');
    },

    // call volume, call duration line graphs & total minutes
    get_call_volume:function(inorout, datefilter, chartColors){

        var activeBtn = $('.callvolume_inorout').find("[data-type='" + this.inorout + "']");
        $(activeBtn).siblings().addClass('btn-default');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/adminoutbounddash/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                inorout:inorout,
                datefilter:datefilter
            },
            success:function(response){

                Master.trend_percentage( $('#total_minutes'), response.call_volume.pct_change, response.call_volume.pct_sign, response.call_volume.ntc );
                $('#total_minutes').find('.total').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total));
                $('#total_minutes').find('p.inbound').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total_inbound_duration));
                $('#total_minutes').find('p.outbound').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total_outbound_duration));

                var call_volume_inbound = {

                    labels: response.call_volume.inbound_time_labels,
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response.call_volume.total_inbound_calls,
                        yAxisID: 'y-axis-1',
                    },{
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response.call_volume.inbound_handled,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Voicemails',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response.call_volume.inbound_voicemails,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Abandoned',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response.call_volume.inbound_abandoned,
                        yAxisID: 'y-axis-1'
                    }]
                };

                var call_volume_outbound = {
                    labels: response.call_volume.outbound_time_labels,
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response.call_volume.total_outbound_calls,
                        yAxisID: 'y-axis-1',
                    }, {
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response.call_volume.outbound_handled,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Dropped',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response.call_volume.outbound_dropped,
                        yAxisID: 'y-axis-1'
                    }]
                };

                var call_volume_options={
                    responsive: true,
                    hoverMode: 'index',
                    stacked: false,
                    scales: {
                        yAxes: [{
                            type: 'linear',
                            display: true,
                            position: 'left',
                            id: 'y-axis-1',
                        }, {
                            type: 'linear',
                            display: false,
                            id: 'y-axis-2',

                            gridLines: {
                                drawOnChartArea: false, 
                            },
                        }],
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }

                // call volume inbound line graph
                var ctx = document.getElementById('call_volume_inbound').getContext('2d');
                if(window.call_volume_inbound_chart != undefined){
                    window.call_volume_inbound_chart.destroy();
                }
                window.call_volume_inbound_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_inbound,
                    options: call_volume_options
                });
                
                // call volume outbound line graph
                var ctx = document.getElementById('call_volume_outbound').getContext('2d');
                if(window.call_volume_outbound_chart != undefined){
                    window.call_volume_outbound_chart.destroy();
                }
                window.call_volume_outbound_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_outbound,
                    options: call_volume_options
                });


                if(!Dashboard.inorout_toggled){
                    var call_duration = {   
                        labels: response.call_volume.duration_time,
                        datasets: [{
                            label: 'Inbound',
                            borderColor: chartColors.orange,
                            backgroundColor: chartColors.orange,
                            fill: false,
                            data: response.call_volume.inbound_duration,
                            yAxisID: 'y-axis-1',
                        },{
                            label: 'Outbound',
                            borderColor: chartColors.green,
                            backgroundColor: chartColors.green,
                            fill: false,
                            data: response.call_volume.outbound_duration,
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    var show_decimal= Master.ylabel_format(response.call_volume.inbound_duration);
                    if(show_decimal){Master.ylabel_format(response.call_volume.outbound_duration);}
                    var call_duration_options={
                        responsive: true,
                        hoverMode: 'index',
                        stacked: false,
                        scales: {
                            yAxes: [{
                                type: 'linear',
                                display: true,
                                position: 'left',
                                id: 'y-axis-1',
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        if(show_decimal){
                                            return Math.round((parseInt(value) /60) * 10) / 10;
                                        }else{
                                            return Math.round(parseInt(value) / 60);
                                        }
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Minutes'
                                },
                            }, {
                                type: 'linear',
                                display: false,
                                id: 'y-axis-2',

                                gridLines: {
                                    drawOnChartArea: false,
                                },
                            }],
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12
                            }
                        },

                        tooltips: {
                            enabled: true,
                            mode: 'single',
                            callbacks: {
                                label: function(tooltipItems, data) { 
                                    return Master.convertSecsToHrsMinsSecs(tooltipItems.yLabel);
                                }
                            }
                        }
                    }

                    // call duration line graph
                    var ctx = document.getElementById('call_duration').getContext('2d');

                    if(window.call_duration_chart != undefined){
                        window.call_duration_chart.destroy();
                    }
                    window.call_duration_chart = new Chart(ctx, {
                        type: 'line',
                        data: call_duration,
                        options: call_duration_options
                    });
                }
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);
            } 
        });
    },

    sales_per_hour_per_rep:function(datefilter, chartColors){
        var campaign = $('.filter_campaign li ').text();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/adminoutbounddash/avg_wait_time',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                $('#avg_wait_time tbody').empty();
                if(response.avg_wait_time.length){
                
                    var trs;
                    for (var i = 0; i < response.avg_wait_time.length; i++) {
                        if(response.avg_wait_time[i].Rep != ''){
                            trs+='<tr><td>'+response.avg_wait_time[i].Rep+'</td><td>'+response.avg_wait_time[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.avg_wait_time[i].AvgWaitTime)+'</td></tr>';
                        }
                    }
                    $('#avg_wait_time tbody').append(trs);
                }
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/adminoutbounddash/sales_per_hour_per_rep',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                Master.flip_card(response.table.length, '#sales_per_hour_per_rep');
                Master.trend_percentage( $('.sales_per_hour'), response.perhour_pct_change, response.perhour_pct_sign, response.perhour_ntc );
                Master.trend_percentage( $('.total_sales_card '), response.sales_pct_change, response.sales_pct_sign, response.sales_ntc );
                $('#sales_per_hour_per_rep, #sales_per_hour_per_rep_graph').parent().find('.no_data').remove();

                var tot_mins = $('#total_minutes .outbound .data.outbound').text();
                tot_mins = parseInt(tot_mins);
                var tot_sales = response.total_sales;

                if(tot_sales){
                    $('#sales_per_hour').text(response.total_sales_per_hour);
                }else{
                    $('#sales_per_hour').text('0');
                }

                if(response.total_sales == 0 || response.total_sales_per_hour.toString().length < 5){
                    $('#sales_per_hour').addClass('bg_rounded');
                }else{
                    $('#sales_per_hour').removeClass('bg_rounded');
                }

                if(response.total_sales.toString().length < 4){
                    $('#total_sales').addClass('bg_rounded');
                }else{
                    $('#total_sales').removeClass('bg_rounded');
                }
                $('#total_sales').html(Master.formatNumber(response.total_sales));
                $('#sales_per_hour_per_rep tbody').empty();

                if(response.reps.length){
                
                    var trs;
                    for (var i = 0; i < response.table.length; i++) {
                        if(response.table[i].Rep != ''){
                            trs+='<tr><td>'+response.table[i].Rep+'</td><td>'+response.table[i].Campaign+'</td><td>'+response.table[i].Sales+'</td></tr>';
                        }
                    }
                    $('#sales_per_hour_per_rep tbody').append(trs);
                }else{
                    $('#sales_per_hour_per_rep tbody').empty(); 
                    $('<p class="no_data">No data yet</p>').insertBefore('#sales_per_hour_per_rep, #sales_per_hour_per_rep_graph');
                }

                var response_length = response.sales.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

                var sales_per_hour_per_rep_data = {
                    datasets: [{
                        data: response.sales,
                        backgroundColor: chart_colors_array,
                        label: 'Dataset 1'
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.reps
                };
                 
                var sales_per_hour_per_rep_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:true,
                    }
                }

                var ctx = document.getElementById('sales_per_hour_per_rep_graph').getContext('2d');

                window.sales_per_hour_per_rep_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: sales_per_hour_per_rep_data,
                    options: sales_per_hour_per_rep_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#sales_per_hour_per_rep');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    calls_by_campaign:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/adminoutbounddash/calls_by_campaign',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                Master.flip_card(response.Table.length, '#calls_by_campaign');
                $('#calls_by_campaign, #calls_by_campaign_graph').parent().find('.no_data').remove();

                if(response.Table.length){
                
                    let trs;
                    for (var i = 0; i < response.Table.length; i++) {
                        if(response.Table[i].Campaign != ''){
                            trs+='<tr><td>'+response.Table[i].Campaign+'</td><td>'+Master.formatNumber(response.Table[i].CallCount)+'</td></tr>';
                        }
                    }
                    $('#calls_by_campaign tbody').append(trs);
                }else{
                    $('#calls_by_campaign tbody').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#calls_by_campaign, #calls_by_campaign_graph');
                }

                if(window.calls_by_campaign_chart != undefined){
                    window.calls_by_campaign_chart.destroy();
                }

                var response_length = response.Counts.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

               var calls_by_campaign_data = {
                    datasets: [{
                        data: response.Counts,
                        backgroundColor: chart_colors_array,
                        label: 'Dataset 1'
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.Campaigns
                };
                
                var calls_by_campaign_options={
                    responsive: true,
                    legend: {
                    display: false
                    },
                    tooltips: {
                        enabled:true,
                    }
                }

                var ctx = document.getElementById('calls_by_campaign_graph').getContext('2d');

                window.calls_by_campaign_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: calls_by_campaign_data,
                    options: calls_by_campaign_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#avg_handle_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            } 
        });
    }, 

    // agent call count pie graph & agent call time table
    agent_talk_time:function(datefilter, chartColors){

        var campaign = $('.filter_campaign li ').text();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/adminoutbounddash/agent_talk_time',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                Master.flip_card(response.reps.length, '#agent_call_count');
                Master.flip_card(response.reps.length, '#agent_talk_time');

                $('#agent_call_count, #agent_talk_time, #agent_call_count_graph, #agent_talk_time_graph').parent().find('.no_data').remove();
                
                $('#agent_call_count tbody').empty();
                $('#agent_talk_time tbody').empty();

                if(response.table_count.length){
                    
                    let trs;
                    for (var i = 0; i < response.table_count.length; i++) {
                        if(response.table_count[i].Rep != ''){
                            trs+='<tr><td>'+response.table_count[i].Rep+'</td><td>'+response.table_count[i].Campaign+'</td><td>'+Master.formatNumber(response.table_count[i].Count)+'</td></tr>';
                        }
                    }
                    $('#agent_call_count tbody').append(trs);
                }else{
                    $('#agent_call_count tbody').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_call_count_graph');
                }

                if(response.table_duration.length){
                    $('#agent_talk_time').show();
                    let trs;
                    for (var i = 0; i < response.table_duration.length; i++) {
                        if(response.table_duration[i].Rep != ''){
                            trs+='<tr><td>'+response.table_duration[i].Rep+'</td><td>'+response.table_duration[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.table_duration[i].Duration)+'</td></tr>';
                        }
                    }
                    $('#agent_talk_time tbody').append(trs);
                }else{
                    $('#agent_talk_time tbody').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_talk_time, #agent_call_count_graph, #agent_talk_time_graph');
                }
                

                ////////////////////////////////////////////////////////////
                ////    AGENT CALL COUNT GRAPH
                ///////////////////////////////////////////////////////////

                if(window.agent_call_count_chart != undefined){
                    window.agent_call_count_chart.destroy();
                }

                var response_length = response.reps.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

                var agent_call_count_data = {
                    datasets: [{
                        data: response.counts,
                        backgroundColor: chart_colors_array,
                        label: 'Dataset 1'
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.reps
                };

                var agent_call_count_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: true,
                       
                    }
                }

                var ctx = document.getElementById('agent_call_count_graph').getContext('2d');

                window.agent_call_count_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: agent_call_count_data,
                    options: agent_call_count_options
                });

                ////////////////////////////////////////////////////////////
                ////    AGENT TALK TIME GRAPH
                ///////////////////////////////////////////////////////////

                if(window.agent_talk_time_chart != undefined){
                    window.agent_talk_time_chart.destroy();
                }

                var response_length = response.reps.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

                var agent_talk_time_data = {
                    datasets: [{
                        data: response.durations_secs,
                        backgroundColor: chart_colors_array,
                        label: 'Dataset 1'
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.reps
                };
                
                var agent_talk_time_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItem, data) { 
                                return ' '+ data['labels'][tooltipItem['index']] + ' ' + Master.convertSecsToHrsMinsSecs(data['datasets'][0]['data'][tooltipItem['index']]);
                            }
                        }
                    }
                }

                var ctx = document.getElementById('agent_talk_time_graph').getContext('2d');

                window.agent_talk_time_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: agent_talk_time_data,
                    options: agent_talk_time_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#agent_talk_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });        
    },

    total_calls:function(datefilter){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/adminoutbounddash/total_calls',
            type: 'POST',
            dataType: 'json',
            data:{datefilter:datefilter},
            success:function(response){

                Master.trend_percentage( $('#total_calls'), response.total_calls.pct_change, response.total_calls.pct_sign, response.total_calls.ntc );
                $('#total_calls .total').html(Master.formatNumber(response.total_calls.total));
                $('#total_calls p.inbound').html(Master.formatNumber(response.total_calls.inbound));
                $('#total_calls p.outbound').html(Master.formatNumber(response.total_calls.outbound));
                $('.filter_time_camp_dets p').html(response.total_calls.details);
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#total_calls .divider');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    update_datefilter:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/adminoutbounddash/update_datefilter',
            type: 'POST',
            dataType: 'json',
            data: {datefilter: datefilter},
            success:function(response){
            }
        });
    },

    filter_date:function(){
        
        $(this).parent().siblings().removeClass('active');
        $(this).parent().addClass('active');
        datefilter = $(this).data('datefilter');

        $('#datefilter').val(datefilter);
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        var inorout = $('#inorout').val();
        $('#inorout').val();  
        Dashboard.inorout_toggled=false; 
        Dashboard.datefilter = datefilter;

        if(datefilter !='custom'){
            $('.preloader').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: '/adminoutbounddash/set_campaign',
                type: 'POST',
                dataType: 'json',
                data: {datefilter:datefilter,campaign: campaign, inorout:inorout},
                success:function(response){
                    Dashboard.refresh(datefilter, campaign, inorout);
                }
            });          
        }
    },

    set_databases:function(databases){
        Dashboard.databases=databases;
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        var datefilter = $('#datefilter').val();
        var inorout = $('#inorout').val();
        $('.preloader').show();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/admindashboard/set_campaign',
            type: 'POST',
            dataType: 'json',
            data: {databases:databases},
            success:function(response){
                Dashboard.refresh(datefilter, campaign, inorout);
            }
        });  
    },

    filter_campaign:function(){

        $('.preloader').show();

        $(this).siblings().removeClass('active')
        $(this).addClass('active');
        var active_date = $('.date_filters li.active');
        datefilter = $('#datefilter').val();
        var inorout =$('#inorout').val();
        var campaign = $(this).text();
        Master.active_camp_search = campaign;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/adminoutbounddash/set_campaign',
            type: 'POST',
            dataType: 'json',
            data: {datefilter:datefilter,campaign: campaign, inorout:inorout},
            success:function(response){
                Dashboard.refresh(datefilter, campaign, inorout);
            }
        });
    },

    custom_date_filter:function(){
        $('.preloader').show();
        $('#datefilter_modal').hide();
        $('.modal-backdrop').hide();
        
        var start_date = $('.startdate').val(),
            end_date = $('.enddate').val()
        ;
        datefilter = start_date + ' ' + end_date;
        var inorout = $('#inorout').val();
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        $('#inorout').val();

        $('.startdate').val('');
        $('.enddate').val('');
        $('#datefilter_modal').modal('toggle');
        $('#datefilter').val(start_date + ' ' + end_date);
        Dashboard.datefilter = datefilter;
        Dashboard.refresh(datefilter, campaign, inorout);
    },

    toggle_inorout_btn_class:function(){
        $(this).siblings().removeClass('btn-primary');
        $(this).siblings().addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass('btn-primary');
    },

    call_volume_type: function(){
        Dashboard.inorout = $(this).data('type');
        datefilter = $('#datefilter').val();
        $('#inorout').val(Dashboard.inorout);
        Dashboard.inorout_toggled=true;        
        $(this).parent().parent().find('.inandout').hide(0, function(){
            $(this).parent().parent().find('.'+Dashboard.inorout).show();
        });

        var inorout = Dashboard.inorout;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        
        $.ajax({
            url: '/adminoutbounddash/set_campaign',
            type: 'POST',
            dataType: 'json',
            data: { inorout:inorout},
            success:function(response){
            }
        }); 
    },

    title_options :{
        fontColor:'#144da1',
        fontSize:16,
    }
}

$(document).ready(function(){

    $(".flipping_card").flip({trigger: 'manual',reverse:true});
    $(".flip_card_btn").on('click', function(){
        $(this).closest('.flipping_card').flip('toggle');
    });

    Dashboard.init();
    resizeTopFlippingCard();
    resizeCardTableDivs();

    function resizeTopFlippingCard(){
        var height_dt2 = $('.get_hgt2').outerHeight();
        $('.set_hgt2').css({'min-height':height_dt2});
        $('.set_hgt2').css({'max-height':height_dt2});        
    }

    $('.count').each(function () {
        $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        }, {
            duration: 1500,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
    });

    if ($(window).width() > 1010) {
        $(window).on('resize', function(){
            resizeCardTableDivs();
        });
    }

    function resizeCardTableDivs(){
        var height_dt = $('.get_hgt').outerHeight();
        $('.set_hgt').css({'min-height':height_dt});
        $('.set_hgt').css({'max-height':height_dt}); 
    }

    $('.enddate').datepicker({maxDate: '0'});
    $('.startdate').datepicker({maxDate: '0'});
    
    
});


