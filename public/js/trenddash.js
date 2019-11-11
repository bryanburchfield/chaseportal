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
        orange: 'rgb(228,154,49)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(51,160,155)',
        blue: 'rgb(1,1,87)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(68,68,68)'
    },
    chartColors2 : {
        red: 'rgb(255,67,77, 0.55)',
        orange: 'rgb(228,154,49, 0.55)',
        yellow: 'rgb(255, 205, 86, 0.55)',
        green: 'rgb(51,160,155, 0.55)',
        blue: 'rgb(1,1,87, 0.55)',
        purple: 'rgb(153, 102, 255, 0.55)',
        grey: 'rgb(68,68,68, 0.55)'
    },

    datefilter : document.getElementById("datefilter").value,
    inorout : document.getElementById("inorout").value,
    inorout_toggled:false,
    time: new Date().getTime(),

    init:function(){
        $.when(this.get_call_volume(this.inorout, this.datefilter, this.chartColors), this.get_avg_handle_time(this.datefilter, this.chartColors), this.agent_calltime(this.datefilter, this.chartColors, this.chartColors2), this.service_level(this.datefilter, this.chartColors), this.call_volume_type()).done(function(){
            
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
                
        Dashboard.eventHandlers();
    },

    eventHandlers:function(){
        $('.card-6 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
        $('.callvolume_inorout .btn').on('click', this.call_volume_type);
        $('.service_level_time a').on('click', this.set_service_level_time);
    },

    display_error:function(div, textStatus, errorThrown){
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reolad the page.</p>');
    },

    refresh:function(datefilter, campaign, inorout){
        $.when(this.get_call_volume(this.inorout, this.datefilter, this.chartColors), this.get_avg_handle_time(this.datefilter, this.chartColors), this.agent_calltime(this.datefilter, this.chartColors, this.chartColors2), this.service_level(this.datefilter, this.chartColors), this.call_volume_type()).done(function(){
            
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
    },

    get_call_volume:function(inorout, datefilter, chartColors){

        var activeBtn = $('.callvolume_inorout').find("[data-type='" + this.inorout + "']");
        $(activeBtn).siblings().addClass('btn-default');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/trenddashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                inorout:inorout,
                datefilter:datefilter
            },
            success:function(response){

                $('#call_volume_inbound, #call_volume_outbound').parent().find('.no_data').remove();

                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);

                var total_calls_int=0;
                if(response.call_volume.total != null){
                    total_calls_int=response.call_volume.total;
                }
                $('.call_volume_details p.total').html('Total Calls: '+Master.formatNumber(total_calls_int));

                if(!Master.has_data(response.call_volume.total_inbound_calls) && !Master.has_data(response.call_volume.inbound_handled) && !Master.has_data(response.call_volume.inbound_voicemails) && !Master.has_data(response.call_volume.inbound_abandoned)){
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#call_volume_inbound');
                }

                if(!Master.has_data(response.call_volume.total_outbound_calls) && !Master.has_data(response.call_volume.outbound_handled) && !Master.has_data(response.call_volume.outbound_dropped) ){
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#call_volume_outbound');
                }

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
                    // maintainAspectRatio: false,
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

                            // grid line settings
                            gridLines: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
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
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    get_avg_handle_time:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/trenddashboard/call_details',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                $('#avg_handle_time').parent().find('.no_data').remove();

                if( response.call_details.datetime != undefined){
                    $('h2.avg_ht').html('Avg Handle Time: '+Master.convertSecsToHrsMinsSecs(response.call_details.avg_ht));
                    $('h2.avg_tt').html('Avg Talk Time: '+Master.convertSecsToHrsMinsSecs(response.call_details.avg_call_time));

                    var avg_handle_time_data  = {
                        labels: response.call_details.datetime,
                        datasets: [{
                            label: 'Avg Handle Time',
                            borderColor: chartColors.green,
                            backgroundColor: 'rgba(51,160,155,0.6)',
                            fill: true,
                            data: response.call_details.avg_handle_time,
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    if(!Master.has_data(response.call_details.avg_handle_time)){
                        $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#avg_handle_time');
                    }

                    var show_decimal= Master.ylabel_format(response.call_details.avg_handle_time);

                    var avg_handle_time_options={
                        responsive: true,
                        maintainAspectRatio: false,
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

                                // grid line settings
                                gridLines: {
                                    drawOnChartArea: false, // only want the grid lines for one axis to show up
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

                    var call_details_data = {
                        labels: response.call_details.datetime,
                        datasets: [{
                            label: 'Talk Time',
                            borderColor: chartColors.green,
                            backgroundColor: chartColors.green,
                            fill: false,
                            data: response.call_details.calls,
                            yAxisID: 'y-axis-1',
                        },{
                            label: 'Hold Time',
                            borderColor: chartColors.blue,
                            backgroundColor: chartColors.blue,
                            fill: false,
                            data: response.call_details.hold_time,
                            yAxisID: 'y-axis-1',
                        },{
                            label: 'After Call Work',
                            borderColor: chartColors.orange,
                            backgroundColor: chartColors.orange,
                            fill: false,
                            data: response.call_details.wrapup_time,
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    $('#call_details').parent().find('.no_data').remove();

                    if(!Master.has_data(response.call_details.calls) && !Master.has_data(response.call_details.hold_time) && !Master.has_data(response.call_details.wrapup_time)){
                        $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#call_details');
                    }

                    var show_decimal2= Master.ylabel_format(response.call_details.wrapup_time);
                    var call_details_options={
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
                                        if(show_decimal2){
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

                                // grid line settings
                                gridLines: {
                                    drawOnChartArea: false, // only want the grid lines for one axis to show up
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

                    // // call duration inbound line graph
                    var ctx = document.getElementById('avg_handle_time').getContext('2d');

                    if(window.avg_handle_time_chart != undefined){
                        window.avg_handle_time_chart.destroy();
                    }
                    window.avg_handle_time_chart = new Chart(ctx, {
                        type: 'line',
                        data: avg_handle_time_data,
                        options: avg_handle_time_options
                    });

                    var ctx = document.getElementById('call_details').getContext('2d');

                    if(window.call_details_chart != undefined){
                        window.call_details_chart.destroy();
                    }
                    window.call_details_chart = new Chart(ctx, {
                        type: 'line',
                        data: call_details_data,
                        options: call_details_options
                    });

                    var max_hold_time_data = {
                        labels: response.call_details.datetime,
                        datasets: [
                          {
                            label: "Longest Hold Time (minutes)",
                            backgroundColor: chartColors.green,
                            data: response.call_details.max_hold
                          }
                        ]
                    };

                    var show_decimal= Master.ylabel_format(response.call_details.max_hold);

                    var max_hold_time_options={
                        responsive: true,
                        maintainAspectRatio:false,
                        legend: {  
                            position: 'bottom',
                            labels: {
                                boxWidth: 12
                            } },
                        scales: {
                            yAxes: [{
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
                            }]
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

                    $('#max_hold_time').parent().find('.no_data').remove();

                    if(!Master.has_data(response.call_details.max_hold)){
                        $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#max_hold_time');
                    }

                    var ctx = document.getElementById('max_hold_time').getContext('2d');

                    if(window.max_hold_time_chart != undefined){
                      window.max_hold_time_chart.destroy();
                    }

                    window.max_hold_time_chart = new Chart(ctx, {
                        type: 'bar',
                        data: max_hold_time_data,
                        options: max_hold_time_options
                    });
                }
            }
        });
    },

    agent_calltime:function(datefilter, chartColors, chartColors2){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/trenddashboard/agent_calltime',
            type: 'POST',
            dataType: 'json',
            data:{datefilter:datefilter},
            success:function(response){
                $('#rep_talktime').parent().find('.no_data').remove();

                if(!response.agent_calltime.rep.length){
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#rep_talktime');
                }

                if( response.agent_calltime.avg_ct != undefined){
                    $('h2.avg_ct').html('Avg Rep Time: '+Master.convertSecsToHrsMinsSecs(response.agent_calltime.avg_ct));
                    $('h2.avg_cc').html('Avg Call Count: '+response.agent_calltime.avg_cc +' ');
                }

                var agent_talktime_data = {
                  labels: response.agent_calltime.rep,
                        datasets: [
                          {
                            yAxisID: 'A',
                            label: "Call Time (minutes)",
                            backgroundColor: chartColors.green,
                            data: response.agent_calltime.duration
                          },
                          {
                            yAxisID: 'B',
                            label: "Call Count",
                            backgroundColor: chartColors.orange,
                            fillOpacity: .5, 
                            data: response.agent_calltime.total_calls
                          }
                        ]
                };

                var show_decimal= Master.ylabel_format(response.agent_calltime.duration);

                var agent_talktime_options={
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
                                id:'A',
                                type: 'linear',
                                position:'left',
                                scalePositionLeft: true,
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
                            },
                            {
                                id:'B',
                                type: 'linear',
                                position:'right',
                                scalePositionLeft: false,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Call Count'
                                },
                                ticks: {
                                    beginAtZero: true,
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItems, data) { 
                                if (tooltipItems.datasetIndex === 0) {
                                    return Master.convertSecsToHrsMinsSecs(tooltipItems.yLabel);
                                }else{
                                    return tooltipItems.yLabel;
                                }
                            }
                        }
                    }
                }

                var ctx = document.getElementById('rep_talktime').getContext('2d');

                if(window.rep_talktime_chart != undefined){
                    window.rep_talktime_chart.destroy();
                }

                window.rep_talktime_chart = new Chart(ctx, {
                    type: 'bar',
                    data: agent_talktime_data,
                    options: agent_talktime_options
                });
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#rep_talktime');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    service_level:function(datefilter, chartColors, answer_secs=20){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/trenddashboard/service_level',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter,
                answer_secs:answer_secs
            },
            success:function(response){

                $('#service_level').parent().find('.no_data').remove();
                $('.answer_secs').html(answer_secs);
                var baseline_cnt = response.service_level.handled_calls.length;
                var baseline=[];
                for (var i = 0; i < baseline_cnt; i++) {
                    baseline.push(answer_secs);
                }

                if(!Master.has_data(response.service_level.servicelevel)){
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#service_level');
                }

                $('h2.avg_sl').html('Avg Service Level: '+response.service_level.avg + '%');
                var service_level_data = {

                    labels: response.service_level.time,
                    datasets: [{
                        label: 'Service Level ',
                        borderColor: chartColors.orange,
                        backgroundColor: 'rgb(228,154,49, 0.55)',
                        fill: true,
                        data: response.service_level.servicelevel,
                        yAxisID: 'y-axis-1'
                    },{
                        type: 'line',
                        label: 'Call Answered by Time',
                        data: baseline,
                        backgroundColor: 'rgba(238,238,238)'

                    }]
                };

                var service_level_options={
                    responsive: true,
                    maintainAspectRatio: false,
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

                            // grid line settings
                            gridLines: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
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
                var ctx = document.getElementById('service_level').getContext('2d');
                if(window.service_level_chart != undefined){
                    window.service_level_chart.destroy();
                }
                window.service_level_chart = new Chart(ctx, {
                    type: 'line',
                    data: service_level_data,
                    options: service_level_options
                });
                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#service_level');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
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

        return $.ajax({
            async: true,
            url: '/trenddashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { inorout:inorout},
            success:function(response){
            }
        }); 
    },

    set_service_level_time:function(e){
        e.preventDefault();
        var answer_secs = $(this).attr('href');
        var datefilter = $('#datefilter').val();
        Dashboard.service_level(datefilter, Dashboard.chartColors, answer_secs);
    },

    title_options :{
        fontColor:'#144da1',
        fontSize:16,
    }
}

$(document).ready(function(){

    Dashboard.init();

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

    $('.enddate').datepicker({maxDate: '0'});
    $('.startdate').datepicker({maxDate: '0'});
    
});


