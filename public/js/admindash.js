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
    databases:'',
    inorout_toggled:false,
    time: new Date().getTime(),

    init:function(){
        this.get_call_volume(this.inorout, this.datefilter, this.chartColors);
        this.agent_call_count(this.datefilter, this.chartColors);
        this.completed_calls(this.datefilter);
        this.average_hold_time(this.datefilter);
        this.service_level(this.datefilter);
        this.abandon_rate(this.datefilter);
        this.rep_avg_handletime(this.datefilter, this.chartColors);
        this.call_volume_type();

        Dashboard.eventHandlers();
        Master.check_reload();
    },

    eventHandlers:function(){
        $('.date_filters li a').on('click', this.filter_date);
        $('.filter_campaign').on('click', 'li', this.filter_campaign);
        $('.submit_date_filter').on('click', this.custom_date_filter);
        $('.card-6 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
        $('.callvolume_inorout .btn').on('click', this.call_volume_type);
        $('.service_level_time a').on('click', this.set_service_level_time);
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

        Dashboard.completed_calls(datefilter);
        Dashboard.average_hold_time(datefilter);
        Dashboard.abandon_rate(datefilter);
        Dashboard.agent_call_count(datefilter, Dashboard.chartColors);
        Dashboard.service_level(datefilter);
        Dashboard.get_call_volume(inorout, datefilter, Dashboard.chartColors);
        Dashboard.rep_avg_handletime(datefilter, Dashboard.chartColors);
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
            url: '/admindashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                inorout:inorout,
                datefilter:datefilter
            },
            success:function(response){
                console.log(response);
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


                // if(!Dashboard.inorout_toggled){
                
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
                    if(show_decimal){show_decimal=Master.ylabel_format(response.call_volume.outbound_duration);}
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
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Minutes'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        if(show_decimal){
                                            return Math.round((parseInt(value) /60) * 10) / 10;
                                        }else{
                                            return Math.round(parseInt(value) / 60);
                                        }
                                    }
                                }
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
                // }
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);
            } 
        });
    },

    // agent call count & agent call time pie graphs
    agent_call_count:function(datefilter, chartColors){

        var campaign = $('.filter_campaign li ').text();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/agent_call_count',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                Master.flip_card(response.reps.length, '#agent_call_count');
                Master.flip_card(response.reps.length, '#agent_calltime');

                $('#agent_call_count, #agent_calltime, #agent_call_count_graph, #agent_calltime_graph').parent().find('.no_data').remove();


                if(response.reps.length){
                    /// agent call count table
                    var call_count_trs;
                    for (var i = 0; i < response.table_count.length; i++) {
                        if(response.table_count[i].Rep != ''){
                            call_count_trs+='<tr><td>'+response.table_count[i].Rep+'</td><td>'+response.table_count[i].Campaign+'</td><td>'+response.table_count[i].Count+'</td></tr>';
                        }
                    }
                    $('#agent_call_count tbody').append(call_count_trs);

                    /// agent call time table
                    var calltime_trs;
                    for (var i = 0; i < response.table_duration.length; i++) {
                        if(response.table_duration[i].Rep != ''){
                            calltime_trs+='<tr><td>'+response.table_duration[i].Rep+'</td><td>'+response.table_duration[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.table_duration[i].Duration)+'</td></tr>';
                        }
                    }
                    $('#agent_calltime tbody').append(calltime_trs);

                }else{
                    $('#agent_call_count tbody, #agent_calltime tbody').empty();                    
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_calltime, #agent_call_count_graph, #agent_calltime_graph');
                }

                // $('#agent_calltime').parent().find('.no_data').remove();

                ////////////////////////////////////////////////////////////
                ////    AGENT CALL COUNT GRAPH
                ///////////////////////////////////////////////////////////

                if(window.agent_call_count_chart != undefined){
                    window.agent_call_count_chart.destroy();
                }

                var response_length = response.counts.length;
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
                        enabled:true,
                    }
                }

                var ctx = document.getElementById('agent_call_count_graph').getContext('2d');

                window.agent_call_count_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: agent_call_count_data,
                    options: agent_call_count_options
                });

                ////////////////////////////////////////////////////////////
                ////    AGENT CALL TIME GRAPH
                ///////////////////////////////////////////////////////////
                if(window.agent_calltime_chart != undefined){
                    window.agent_calltime_chart.destroy();
                }

                var response_length = response.durations_secs.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

                var agent_calltime_data = {
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

                var agent_calltime_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItem, data) { 
                                return ' '+ data['labels'][tooltipItem['index']] + ' ' + Master.convertSecsToHrsMinsSecs(data['datasets'][0]['data'][tooltipItem['index']]);
                            }
                        }
                    }
                }

                var ctx = document.getElementById('agent_calltime_graph').getContext('2d');

                window.agent_calltime_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: agent_calltime_data,
                    options: agent_calltime_options
                });
               
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#agent_call_count #agent_calltime');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });        
    },

    completed_calls:function(datefilter){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/completed_calls',
            type: 'POST',
            dataType: 'json',
            data:{datefilter:datefilter},
            success:function(response){
                console.log(response);
                Master.trend_percentage( $('#completed_calls'), response.completed_calls.pct_change, response.completed_calls.pct_sign, response.completed_calls.ntc );

                $('#completed_calls .total').html(Master.formatNumber(response.completed_calls.total));
                $('#completed_calls p.inbound').html(Master.formatNumber(response.completed_calls.inbound));
                $('#completed_calls p.outbound').html(Master.formatNumber(response.completed_calls.outbound));
                $('.filter_time_camp_dets p').html('<span class="selected_datetime">'+response.completed_calls.details[1] + '</span> | <span class="selected_campaign"> ' +  response.completed_calls.details[0] +'</span>');
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#completed_calls .divider');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    average_hold_time:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/avg_hold_time',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter :datefilter},
            success:function(response){

                Master.trend_percentage( $('.avg_hold_time_card'), response.average_hold_time.pct_change, response.average_hold_time.pct_sign, response.average_hold_time.ntc );
                $('#avg_hold_time').html(response.average_hold_time.avg_hold_time);
                $('#total_hold_time').html(response.average_hold_time.total_hold_time);
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#avg_hold_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    set_service_level_time:function(e){
        e.preventDefault();
        var answer_secs = $(this).attr('href');

         $('.answer_secs').html(answer_secs);
        var datefilter = $('#datefilter').val();
        Dashboard.service_level(datefilter, answer_secs);
    },

    service_level:function(datefilter, answer_secs=20){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/service_level',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter :datefilter, answer_secs:answer_secs},
            success:function(response){

                var service_level_data = {
                    datasets: [{
                        data: [response.service_level.service_level,response.service_level.remainder],
                        backgroundColor: [
                            Dashboard.chartColors.green,
                            Dashboard.chartColors.grey,
                        ]
                    }]
                }

                var service_level_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:false,
                    },
                    elements: {
                            center: {
                            text: response.service_level.service_level+'%',
                            color: '#203047', 
                            fontStyle: 'Arial', 
                            sidePadding: 15 
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    
                    circumference: Math.PI,
                    rotation : Math.PI,
                    cutoutPercentage : 70, // precent
                }
                
                var ctx = document.getElementById('service_level').getContext('2d');

                if(window.service_level_chart != undefined){
                    window.service_level_chart.destroy();
                }

                window.service_level_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: service_level_data,
                    options: service_level_options
                });
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#service_level');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    abandon_rate:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/abandon_rate',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter :datefilter},
            success:function(response){

                Master.trend_percentage( $('.abandon_calls_card'), response.abandon_rate.pct_change, response.abandon_rate.pct_sign, response.abandon_rate.ntc );

                if(response.abandon_rate.abandon_rate=="NAN%"){response.abandon_rate.abandon_rate='0'}
                if(response.abandon_rate.abandon_calls==null){response.abandon_rate.abandon_calls='0'}

                $('#abandon_calls').html(Master.formatNumber(response.abandon_rate.abandon_calls));
                $('#abandon_rate').html(response.abandon_rate.abandon_rate );
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#abandon_calls');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    rep_avg_handletime:function(datefilter, chartColors){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            'async': false,
            url: '/admindashboard/rep_avg_handletime',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter :datefilter},
            success:function(response){
                console.log(response);
                Master.flip_card(response.avg_handletime.length, '#rep_avg_handletime');

                $('#rep_avg_handletime, #rep_avg_handletime_graph').parent().find('.no_data').remove();

                if(response.avg_handletime.length){
                    
                    var trs;
                    for (var i = 0; i < response.reps.length; i++) {
                        if(response.table[i].Rep != ''){
                            trs+='<tr><td>'+response.table[i].Rep+'</td><td>'+response.table[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.table[i].AverageHandleTime)+'</td></tr>';
                        }
                    }
                    $('#rep_avg_handletime tbody').append(trs);
                }else{
                    $('#rep_avg_handletime tbody').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#rep_avg_handletime, #rep_avg_handletime_graph');
                }

                ////////////////////////////////////////////////////////////
                ////    REP AVG HANDLE TIME GRAPH
                ///////////////////////////////////////////////////////////

                if(window.rep_avg_handletime_chart != undefined){
                    window.rep_avg_handletime_chart.destroy();
                }

                var response_length = response.avg_handletime.length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

                var rep_avg_handletime_data = {
                    datasets: [{
                        data: response.avg_handletimesecs,
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

                var rep_avg_handletime_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItem, data) { 
                                return ' '+ data['labels'][tooltipItem['index']] + ' ' + Master.convertSecsToHrsMinsSecs(data['datasets'][0]['data'][tooltipItem['index']]);
                            }
                        }
                    }
                }

                var ctx = document.getElementById('rep_avg_handletime_graph').getContext('2d');

                window.rep_avg_handletime_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: rep_avg_handletime_data,
                    options: rep_avg_handletime_options
                });
                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#rep_avg_handletime');
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
            url: '/admindashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {dateFilter : datefilter},
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

        $('#inorout').val(inorout);  
        Dashboard.inorout_toggled=false; 
        Dashboard.datefilter = datefilter;
        console.log(datefilter);
        if(datefilter !='custom'){
            $('.preloader').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: '/admindashboard/update_filters',
                type: 'POST',
                dataType: 'json',
                data: {dateFilter :datefilter, inorout:inorout},
                success:function(response){
                    console.log(response);
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
            url: '/admindashboard/update_filters',
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
            url: '/admindashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {dateFilter :datefilter,campaign: campaign, inorout:inorout},
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
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        datefilter = start_date + ' ' + end_date;
        var inorout = $('#inorout').val();
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
        if(this.inorout != undefined){
            inorout = Dashboard.inorout;
        }else{
            Dashboard.inorout = $(this).data('type');
            $('#inorout').val(Dashboard.inorout);
        }

        $('.callvolume_inorout .btn').removeClass('btn-primary');
        $('.callvolume_inorout .btn').each(function(){
            if($(this).data('type') === Dashboard.inorout){
                $(this).addClass('btn-primary');
            }
        });
        
        datefilter = $('#datefilter').val();
        
        Dashboard.inorout_toggled=true;  
        
        $('.callvolume_inorout').siblings('.inandout').hide();

        $('.callvolume_inorout').siblings('.inandout.'+Dashboard.inorout).show();

        var inorout = Dashboard.inorout;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/admindashboard/update_filters',
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
    resizeCardTableDivs();

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



