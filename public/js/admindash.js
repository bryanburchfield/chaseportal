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
    databases:'',
    time: new Date().getTime(),

    init:function(){
        $.when(this.rep_avg_handletime(this.datefilter, this.chartColors), this.get_call_volume(this.datefilter, this.chartColors), this.agent_call_count(this.datefilter, this.chartColors), this.average_hold_time(this.datefilter), this.abandon_rate(this.datefilter), this.total_sales(this.datefilter), this.service_level(this.datefilter), this.agent_call_status(this.datefilter)).done(function(){
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
                
        Dashboard.eventHandlers();
    },

    eventHandlers:function(){
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

    refresh:function(datefilter){

        $.when(this.rep_avg_handletime(this.datefilter, this.chartColors), this.get_call_volume(this.datefilter, this.chartColors), this.agent_call_count(this.datefilter, this.chartColors), this.average_hold_time(this.datefilter), this.abandon_rate(this.datefilter), this.total_sales(this.datefilter), this.service_level(this.datefilter)).done(function(){
            
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
    },


    // call volume, call duration line graphs & total minutes
    get_call_volume:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/admindashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                dateFilter:datefilter
            },
            success:function(response){
                
                ///////////////// CALLS ANSWERED CARD
                Master.trend_percentage( $('#calls_answered'), response.call_volume.calls_answered.pct_change, response.call_volume.calls_answered.pct_sign, response.call_volume.calls_answered.ntc );
                Master.add_bg_rounded_class($('#calls_answered .total'), response.call_volume.calls_answered.count, 4);
                $('#calls_answered .total').html(Master.formatNumber(response.call_volume.calls_answered.count));
                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);

                ///////////////// AVG HANDLE TIME CARD
                $('#avg_handle_time').html(Master.convertSecsToHrsMinsSecs(response.call_volume.calls_answered.average));                
                if(response.call_volume.calls_answered.min){
                    $('.avg_handle_time_card .inbound .lowest').html(Master.convertSecsToHrsMinsSecs(response.call_volume.calls_answered.min));
                }else{
                    $('.avg_handle_time_card .inbound .lowest').html('00:00:00');
                }

                if(response.call_volume.calls_answered.max){
                    $('.avg_handle_time_card .outbound .highest').html(Master.convertSecsToHrsMinsSecs(response.call_volume.calls_answered.max));
                }else{
                    $('.avg_handle_time_card .outbound .highest').html('00:00:00');
                }

                Master.trend_percentage( $('.avg_handle_time_card'), response.call_volume.calls_answered.pct_change, response.call_volume.calls_answered.pct_sign, response.call_volume.calls_answered.ntc );

                ///////////////// TOTAL CALL PRESENTED
                Master.add_bg_rounded_class($('#calls_offered .total'), response.call_volume.calls_offered.count, 4);
                Master.trend_percentage( $('#calls_offered'), response.call_volume.calls_offered.pct_change, response.call_volume.calls_offered.pct_sign, response.call_volume.calls_offered.ntc );

                $('#calls_offered .total').html(Master.formatNumber(response.call_volume.calls_offered.count));

                ///////////////// MISSED CALLS
                Master.trend_percentage( $('#missed_calls'), response.call_volume.calls_missed.pct_change, response.call_volume.calls_missed.pct_sign, response.call_volume.calls_missed.ntc );
                // Master.add_bg_rounded_class($('#missed_calls .total'), response.call_volume.calls_missed.count, 4);
                $('#missed_calls .total').html(Master.formatNumber(response.call_volume.calls_missed.count));
                $('#missed_calls .inbound .abandoned').html(Master.formatNumber(response.call_volume.calls_missed.abandoned));
                $('#missed_calls .outbound .voicemails').html(Master.formatNumber(response.call_volume.calls_missed.voicemail));


                ///////////////// AVG TALK TIME
                Master.trend_percentage( $('#avg_talk_time'), response.call_volume.talk_time.pct_change, response.call_volume.talk_time.pct_sign, response.call_volume.talk_time.ntc );
                $('#avg_talk_time').find('.total').html(Master.convertSecsToHrsMinsSecs(response.call_volume.talk_time.average));
                if(response.call_volume.calls_answered.min){
                    $('#avg_talk_time .inbound .lowest').html(Master.convertSecsToHrsMinsSecs(response.call_volume.calls_answered.min));
                }else{
                    $('#avg_talk_time .inbound .lowest').html('00:00:00');
                }

                if(response.call_volume.calls_answered.max){
                    $('#avg_talk_time .outbound .highest').html(Master.convertSecsToHrsMinsSecs(response.call_volume.calls_answered.max));
                }else{
                    $('#avg_talk_time .outbound .highest').html('00:00:00');
                }

                var call_volume_inbound = {

                    labels: response.call_volume.call_volume.time_labels,
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response.call_volume.call_volume.total_calls,
                        yAxisID: 'y-axis-1',
                    },{
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response.call_volume.call_volume.handled,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Voicemails',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response.call_volume.call_volume.voicemails,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Abandoned',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response.call_volume.call_volume.abandoned,
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

                ///////////////// CALL VOLUME GRAPH
                var ctx = document.getElementById('call_volume_inbound').getContext('2d');
                if(window.call_volume_inbound_chart != undefined){
                    window.call_volume_inbound_chart.destroy();
                }
                window.call_volume_inbound_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_inbound,
                    options: call_volume_options
                });
                
                ///////////////// CALL DURATION GRAPH
                var call_duration = {
                    labels: response.call_volume.call_duration.time_labels,
                    datasets: [{
                        label: 'Inbound',
                        borderColor: chartColors.green,
                        backgroundColor:'rgb(51,160,155, 0.55)',
                        fill: true,
                        data: response.call_volume.call_duration.duration,
                        yAxisID: 'y-axis-1',
                    }]
                };

                var show_decimal= Master.ylabel_format(response.call_volume.call_duration.duration);
                if(show_decimal){Master.ylabel_format(response.call_volume.call_duration.duration);}
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

                var ctx = document.getElementById('call_duration').getContext('2d');

                if(window.call_duration_chart != undefined){
                    window.call_duration_chart.destroy();
                }
                window.call_duration_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_duration,
                    options: call_duration_options
                });
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

        return $.ajax({
            async: true,
            url: '/admindashboard/agent_call_count',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, dateFilter:datefilter},
            success:function(response){

                Master.flip_card(response.call_count_reps.length, '#agent_call_count');
                Master.flip_card(response.call_time_reps.length, '#agent_calltime');
                $('#agent_call_count tbody, #agent_calltime tbody').empty();  
                $('#agent_call_count, #agent_calltime, #agent_call_count_graph, #agent_calltime_graph').parent().find('.no_data').remove();


                if(response.call_count_reps.length){
                    /// agent call count table
                    var call_count_trs;
                    for (var i = 0; i < response.call_count_table.length; i++) {
                        if(response.call_count_table[i].Rep != ''){
                            call_count_trs+='<tr><td>'+response.call_count_table[i].Rep+'</td><td>'+response.call_count_table[i].Campaign+'</td><td>'+response.call_count_table[i].Count+'</td></tr>';
                        }
                    }
                    $('#agent_call_count tbody').append(call_count_trs);

                    /// agent call time table
                    var calltime_trs;
                    for (var i = 0; i < response.call_time_table.length; i++) {
                        if(response.call_time_table[i].Rep != ''){
                            calltime_trs+='<tr><td>'+response.call_time_table[i].Rep+'</td><td>'+response.call_time_table[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.call_time_table[i].Duration)+'</td></tr>';
                        }
                    }
                    $('#agent_calltime tbody').append(calltime_trs);

                }else{
                                      
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_calltime, #agent_call_count_graph, #agent_calltime_graph');
                }

                // $('#agent_calltime').parent().find('.no_data').remove();

                ////////////////////////////////////////////////////////////
                ////    AGENT CALL COUNT GRAPH
                ///////////////////////////////////////////////////////////

                if(window.agent_call_count_chart != undefined){
                    window.agent_call_count_chart.destroy();
                }
                
                var response_length = response.call_count_counts.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.call_count_reps);

                var agent_call_count_data = {
                    datasets: [{
                        data: response.call_count_counts,
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
                    labels: response.call_count_reps
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

                var response_length = response.call_time_hms.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.call_time_reps);

                var agent_calltime_data = {
                    datasets: [{
                        data: response.call_time_secs,
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
                    labels: response.call_time_reps
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

    average_hold_time:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/admindashboard/avg_hold_time',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter},
            success:function(response){

                Master.trend_percentage( $('.avg_hold_time_card'), response.average_hold_time.pct_change, response.average_hold_time.pct_sign, response.average_hold_time.ntc );
                $('#avg_hold_time').html(response.average_hold_time.avg_hold_time);
                
                if(response.average_hold_time.min_hold_time){
                    $('.avg_hold_time_card .inbound .lowest').html(Master.convertSecsToHrsMinsSecs(response.average_hold_time.min_hold_time));
                }else{
                    $('.avg_hold_time_card .inbound .lowest').html('00:00:00');
                }

                if(response.average_hold_time.min_hold_time){
                    $('.avg_hold_time_card .outbound .highest').html(Master.convertSecsToHrsMinsSecs(response.average_hold_time.max_hold_time));
                }else{
                    $('.avg_hold_time_card .outbound .highest').html('00:00:00');
                }                
                
                $('#total_hold_time').html(response.average_hold_time.total_hold_time);

                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#avg_hold_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    agent_call_status:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/admindashboard/agent_call_status',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter},
            success:function(response){
                
                const dispos_obj = response.dispositions
                const dispos_obj_keys = Object.getOwnPropertyNames(dispos_obj);

                let chart_colors = Object.values(Dashboard.chartColors);
                let chart_colors_array=[];
                let j=0;
                for (let i=0; i < dispos_obj_keys.length; i++) {
                    if(j==chart_colors.length){
                        j=0;
                    }
                    chart_colors_array.push(chart_colors[j]);
                    j++;
                }
                                
                let dispos = [];
                for (let i=0; i < dispos_obj_keys.length; i++) {
                    dispos.push({
                        label: dispos_obj_keys[i],
                        backgroundColor: chart_colors_array[i],
                        data: Object.values(dispos_obj)[i].sort(),
                    });
                }

                let agent_call_status_data = {
                  labels: response.reps,
                        datasets: dispos
                };

                let agent_call_status_options={
                    responsive: true,
                    maintainAspectRatio:false,
                    legend: {  
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        } 
                    },
                    scales: {
                        
                        yAxes: [
                            {
                                stacked:true,
                                // type: 'linear',
                                position:'left',
                                scalePositionLeft: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Call Count'
                                },
                                ticks: {
                                        // display: false
                                    }
                            }
                        ],
                        xAxes: [{ stacked: true }],
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'label',
                       
                    }
                }

                $('.hidetilloaded').show();

                var ctx = document.getElementById('agent_call_status').getContext('2d');

                if(window.agent_call_status_chart != undefined){
                    window.agent_call_status_chart.destroy();
                }

                window.agent_call_status_chart = new Chart(ctx, {
                    type: 'horizontalBar',
                    data: agent_call_status_data,
                    options: agent_call_status_options
                });

                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#agent_call_status');
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

        return $.ajax({
            async: true,
            url: '/admindashboard/service_level',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter, answer_secs:answer_secs},
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

        return $.ajax({
            async: true,
            url: '/admindashboard/abandon_rate',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter},
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

        return $.ajax({
            async: true,
            url: '/admindashboard/rep_avg_handletime',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter},
            success:function(response){

                Master.flip_card(response.avg_handletime.length, '#rep_avg_handletime');
                $('#rep_avg_handletime tbody').empty();
                $('#rep_avg_handletime, #rep_avg_handletime_graph').parent().find('.no_data').remove();

                if(response.avg_handletime.length){
                        
                    var trs;
                    for (var i = 0; i < response.table.length; i++) {
                        if(response.table[i].Rep != ''){
                            trs+='<tr><td>'+response.table[i].Rep+'</td><td>'+response.table[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.table[i].AverageHandleTime)+'</td></tr>';
                        }
                    }

                    $('#rep_avg_handletime tbody').append(trs);

                }else{
                    $('<p class="no_data">No data yet</p>').insertBefore('#rep_avg_handletime, #rep_avg_handletime_graph');
                }


                ////////////////////////////////////////////////////////////
                ////    REP AVG HANDLE TIME GRAPH
                ///////////////////////////////////////////////////////////

                $('.max_handle_time').text(Master.convertSecsToHrsMinsSecs(response.max_handle_time));
                var rep_avg_handletime_data = {
                    datasets: [{
                        data: [response.total_avg_handle_time, response.remainder],
                        backgroundColor: [
                            Dashboard.chartColors.orange,
                            Dashboard.chartColors.grey,
                        ]
                    }]
                }

                var rep_avg_handletime_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:false,
                    },
                    elements: {
                            center: {
                            text: response.total_avg_handle_time+'%',
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
                
                var ctx = document.getElementById('rep_avg_handletime_graph').getContext('2d');

                ctx.fillText('0%' ,1,1);
                         ctx.fillText('100%',1,1);

                if(window.rep_avg_handletime_chart != undefined){
                    window.rep_avg_handletime_chart.destroy();
                }

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

    total_sales:function(datefilter){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/admindashboard/total_sales',
            type: 'POST',
            dataType: 'json',
            data:{dateFilter:datefilter},
            success:function(response){

                Master.trend_percentage( $('.total_sales'), response.total_sales.pct_change, response.total_sales.pct_sign, response.total_sales.ntc );
                if(response.total_sales.sales=="NAN%"){response.total_sales.sales='0'}
                Master.add_bg_rounded_class($('#total_sales'), response.total_sales.sales, 4);
                $('#total_sales').html(Master.formatNumber(response.total_sales.sales));
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#total_sales');
                Dashboard.display_error(div, textStatus, errorThrown);
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



