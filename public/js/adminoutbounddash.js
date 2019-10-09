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
    time: new Date().getTime(),
    total_dials:'',

    init:function(){
        $.when(this.agent_talk_time(this.datefilter, this.chartColors), this.get_call_volume(this.datefilter, this.chartColors), this.total_calls(this.datefilter, this.chartColors), this.sales_per_hour_per_rep(this.datefilter, this.chartColors), this.calls_by_campaign(this.datefilter, this.chartColors), this.avg_wait_time(this.datefilter, this.chartColors), this.agent_call_status(this.datefilter)).done(function(){
            Dashboard.resizeCardTableDivs();
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
        
        $('#avg_wait_time').closest('.flipping_card').flip(true);
    },

    display_error:function(div, textStatus, errorThrown){
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reload the page.</p>');
    },

    refresh:function(datefilter, campaign){

        $.when(this.agent_talk_time(datefilter, this.chartColors), this.get_call_volume(datefilter, this.chartColors), this.total_calls(datefilter, this.chartColors), this.sales_per_hour_per_rep(datefilter, this.chartColors), this.calls_by_campaign(datefilter, this.chartColors), this.avg_wait_time(datefilter, this.chartColors), this.agent_call_status(this.datefilter)).done(function(){
            $('.preloader').fadeOut('slow');
            Dashboard.resizeCardTableDivs();
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
            url: '/adminoutbounddashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                /////// TOTAL DURATION
                $('#total_contacts_card').find('.total').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total_duration.duration));
                
                ////// CALL VOLUME
                var call_volume_outbound = {
                    labels: response.call_volume.call_volume.time_labels,
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response.call_volume.call_volume.total_calls,
                        yAxisID: 'y-axis-1',
                    }, {
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response.call_volume.call_volume.handled,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Dropped',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response.call_volume.call_volume.dropped,
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

                ////// CALL DURATION
                var call_duration = {   
                    labels: response.call_volume.call_duration.time_labels,
                    datasets: [{
                        label: 'Outbound',
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

        return $.ajax({
            async: true,
            url: '/adminoutbounddashboard/sales_per_hour_per_rep',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                $('#conversion_rate').html(response.total_sales.conversion_rate +'%');
                $('#conversion_rate').closest('.flipping_card').flip(true);
                $('#total_sales').closest('.flipping_card').flip(true);

                Master.trend_percentage( $('.sales_per_hour'), response.sales_per_hour.pct_change, response.sales_per_hour.pct_sign, response.sales_per_hour.ntc );
                Master.trend_percentage( $('.total_sales_card '), response.total_sales.pct_change, response.total_sales.pct_sign, response.total_sales.ntc );
                $('#sales_per_hour_per_rep tbody').empty(); 
                $('#sales_per_hour_per_rep, #sales_per_hour_per_rep_graph').parent().find('.no_data').remove();

                var tot_mins = $('#total_contacts_card .outbound .data.outbound').text();
                tot_mins = parseInt(tot_mins);
                var tot_sales = response.total_sales.total;

                if(tot_sales){
                    $('#sales_per_hour').text(response.sales_per_hour.total);
                }else{
                    $('#sales_per_hour').text('0');
                }

                Master.add_bg_rounded_class($('#sales_per_hour'), response.sales_per_hour.total, 5);
                Master.add_bg_rounded_class($('#total_sales'), response.total_sales.total, 4);


                $('#total_sales').html(Master.formatNumber(response.total_sales.total));
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
                    $('<p class="no_data">No data yet</p>').insertBefore('#sales_per_hour_per_rep, #sales_per_hour_per_rep_graph');
                }


            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#sales_per_hour_per_rep');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
    },

    avg_wait_time:function(datefilter, chartColors){
        var campaign = $('.filter_campaign li ').text();
        return $.ajax({
            async: true,
            url:'/adminoutbounddashboard/avg_wait_time',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                $('#avg_wait_time_graph, #avg_wait_time').parent().find('.no_data').remove();

                $('#avg_wait_time tbody').empty();
                if(response.Avgs.length){
                    var trs;
                    for (var i = 0; i < response.Table.length; i++) {
                        if(response.Table[i].Rep != ''){
                            trs+='<tr><td>'+response.Table[i].Rep+'</td><td>'+response.Table[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.Table[i].Avg)+'</td></tr>';
                        }
                    }
                    $('#avg_wait_time tbody').append(trs);
                }else{
                    $('<p class="no_data">No data yet</p>').insertBefore('#avg_wait_time, #avg_wait_time_graph');
                }

                ////////////////////////////////////////////////////////////
                ////    AVG WAIT TIME GRAPH
                ///////////////////////////////////////////////////////////

                if(window.avg_wait_time_chart != undefined){
                    window.avg_wait_time_chart.destroy();
                }

                var response_length = response.Reps.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.Reps);

                var avg_wait_time_data = {
                    datasets: [{
                        data: response.Avgs,
                        backgroundColor: chart_colors_array
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.Reps
                };
                
                var avg_wait_time_options={
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

                var ctx = document.getElementById('avg_wait_time_graph').getContext('2d');

                window.avg_wait_time_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: avg_wait_time_data,
                    options: avg_wait_time_options
                });

                Dashboard.resizeCardTableDivs();
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#avg_wait_time_graph');
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
        
        return $.ajax({
            async: true,
            url: '/adminoutbounddashboard/calls_by_campaign',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                Master.flip_card(response.Table.length, '#calls_by_campaign');
                $('#calls_by_campaign, #calls_by_campaign_graph').parent().find('.no_data').remove();
                $('#calls_by_campaign tbody').empty();

                if(response.Table.length){
                
                    let trs;
                    for (var i = 0; i < response.Table.length; i++) {
                        if(response.Table[i].Campaign != ''){
                            trs+='<tr><td>'+response.Table[i].Campaign+'</td><td>'+Master.formatNumber(response.Table[i].CallCount)+'</td></tr>';
                        }
                    }
                    $('#calls_by_campaign tbody').append(trs);
                }else{
                    $('<p class="no_data">No data yet</p>').insertBefore('#calls_by_campaign, #calls_by_campaign_graph');
                }

                if(window.calls_by_campaign_chart != undefined){
                    window.calls_by_campaign_chart.destroy();
                }

                var response_length = response.Counts.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.Campaigns);

                var calls_by_campaign_data = {
                    datasets: [{
                        data: response.Counts,
                        backgroundColor: chart_colors_array
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
                    maintainAspectRatio: false,
                    legend: {
                    display: false
                    },
                    tooltips: {
                        enabled:true,
                    }
                }

                var ctx = document.getElementById('calls_by_campaign_graph').getContext('2d');
               
                window.calls_by_campaign_chart = new Chart(ctx,{
                    type: 'horizontalBar',
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

        return $.ajax({
            async: true,
            url: '/adminoutbounddashboard/agent_talk_time',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){

                Master.flip_card(response.call_count_reps.length, '#agent_call_count');
                Master.flip_card(response.talk_time_reps.length, '#agent_talk_time');

                $('#agent_call_count, #agent_talk_time, #agent_call_count_graph, #agent_talk_time_graph').parent().find('.no_data').remove();
                
                $('#agent_call_count tbody').empty();
                $('#agent_talk_time tbody').empty();

                if(response.call_count_table.length){
                    
                    let trs;
                    for (var i = 0; i < response.call_count_table.length; i++) {
                        if(response.call_count_table[i].Rep != ''){
                            trs+='<tr><td>'+response.call_count_table[i].Rep+'</td><td>'+response.call_count_table[i].Campaign+'</td><td>'+Master.formatNumber(response.call_count_table[i].Count)+'</td></tr>';
                        }
                    }
                    $('#agent_call_count tbody').append(trs);
                }else{
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_call_count_graph');
                }

                if(response.talk_time_table.length){
                    $('#agent_talk_time').show();
                    let trs;
                    for (var i = 0; i < response.talk_time_table.length; i++) {
                        if(response.talk_time_table[i].Rep != ''){
                            trs+='<tr><td>'+response.talk_time_table[i].Rep+'</td><td>'+response.talk_time_table[i].Campaign+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.talk_time_table[i].Duration)+'</td></tr>';
                        }
                    }
                    $('#agent_talk_time tbody').append(trs);
                }else{
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count, #agent_talk_time, #agent_call_count_graph, #agent_talk_time_graph');
                }
                

                ////////////////////////////////////////////////////////////
                ////    AGENT CALL COUNT GRAPH
                ///////////////////////////////////////////////////////////

                if(window.agent_call_count_chart != undefined){
                    window.agent_call_count_chart.destroy();
                }

                var response_length = response.call_count_reps.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.call_count_reps);

                var agent_call_count_data = {
                    datasets: [{
                        data: response.call_count_counts,
                        backgroundColor: chart_colors_array
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

                var response_length = response.talk_time_reps.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.talk_time_reps);

                var agent_talk_time_data = {
                    datasets: [{
                        data: response.talk_time_secs,
                        backgroundColor: chart_colors_array,
                        
                    }],
                    elements: {
                            center: {
                            color: '#203047', 
                            fontStyle: 'Segoeui', 
                            sidePadding: 15 
                        }
                    },
                    labels: response.talk_time_reps
                };

                var agent_talk_time_options={
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItem, data) { 
                                return  Master.convertSecsToHrsMinsSecs(data['datasets'][0]['data'][tooltipItem['index']]);
                            }
                        }
                    }
                }

                var ctx = document.getElementById('agent_talk_time_graph').getContext('2d');
                
                if(window.agent_talk_time_chart != undefined){
                    window.agent_talk_time_chart.destroy();
                }

                window.agent_talk_time_chart = new Chart(ctx,{
                    type: 'horizontalBar',
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

        return $.ajax({
            async: true,
            url: '/adminoutbounddashboard/total_calls',
            type: 'POST',
            dataType: 'json',
            data:{datefilter:datefilter},
            success:function(response){

                $('.filter_time_camp_dets p .selected_campaign').html(response.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.details[1]);
                $('#total_contacts').html(response.total_contacts.total);
                Master.add_bg_rounded_class($('#total_contacts'), response.total_contacts.total, 4);
                
                var total_contacts=parseInt(response.total_contacts.total); 
                if(total_contacts == 0){
                    var contact_rate = 0;
                }else{
                    var contact_rate = response.total_dials.total/total_contacts;
                }           
                
                contact_rate = contact_rate.toFixed(2);
                $('#contact_rate').html(contact_rate +'%');

                Master.trend_percentage( $('#total_calls'), response.total_dials.pct_change, response.total_dials.pct_sign, response.total_dials.ntc );
                Master.add_bg_rounded_class($('#total_calls .total'), response.total_dials.total, 4);
                Dashboard.total_dials=response.total_dials.total;

                $('#total_calls .total').html(Master.formatNumber(response.total_dials.total));
                // $('.filter_time_camp_dets p').html(response.details);
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#total_calls .divider');
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
                console.log(response);

                $('#agent_call_status').parent().find('.no_data').remove();

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
                        data: Object.values(dispos_obj)[i],
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
                                    labelString: 'Reps'
                                }
                            }
                        ],
                        xAxes: [{ stacked: true }],
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'label',
                        filter: function (tooltipItem, data) {
                            var datapointValue = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

                            if (datapointValue) {
                                return true;
                            }
                        }
                       
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

                if(!response.reps.length){
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_status');
                }                
            }
        });
    },

    resizeCardTableDivs:function(){

        var height_dt = $('.get_hgt .front').innerHeight();
        $('.set_hgt').css({'height':height_dt });
        $('.set_hgt canvas').css({'height':height_dt -50, 'padding-bottom' : 20 });
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
    Dashboard.resizeCardTableDivs();

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
            Dashboard.resizeCardTableDivs();
        });
    }

    $('.enddate').datepicker({maxDate: '0'});
    $('.startdate').datepicker({maxDate: '0'});
    
    
});



