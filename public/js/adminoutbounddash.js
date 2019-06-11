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

    init:function(){
        this.get_call_volume(this.inorout, this.datefilter, this.chartColors);
        this.agent_talk_time(this.datefilter, this.chartColors);
        this.sales_per_hour_per_rep(this.datefilter, this.chartColors);
        this.calls_by_campaign(this.datefilter, this.chartColors);
        this.total_calls(this.datefilter);
        Dashboard.eventHandlers();
    },

    eventHandlers:function(){
        $('.date_filters li a').on('click', this.filter_date);
        $('.filter_campaign li').on('click', this.filter_campaign);
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

    // call volume, call duration line graphs & total minutes
    get_call_volume:function(inorout, datefilter, chartColors){

        var activeBtn = $('.callvolume_inorout').find("[data-type='" + this.inorout + "']");
        $(activeBtn).siblings().addClass('btn-default');
        $.ajax({
            'async': false,
            url: '../../adminoutbounddash/app/ajax/call_volume.php',
            type: 'POST',
            dataType: 'json',
            data:{
                inorout:inorout,
                datefilter:datefilter
            },
            success:function(response){

                $('#total_minutes').find('.total').html(response['call_volume']['total']);
                $('#total_minutes').find('p.inbound').html(response['call_volume']['total_inbound_duration']);
                $('#total_minutes').find('p.outbound').html(response['call_volume']['total_outbound_duration']);

                var call_volume_inbound = {

                    labels: response['call_volume']['inbound_time_labels'],
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['call_volume']['total_inbound_calls'],
                        yAxisID: 'y-axis-1',
                    },{
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response['call_volume']['inbound_handled'],
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Voicemails',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response['call_volume']['inbound_voicemails'],
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Abandoned',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['call_volume']['inbound_abandoned'],
                        yAxisID: 'y-axis-1'
                    }]
                };

                var call_volume_outbound = {
                    labels: response['call_volume']['outbound_time_labels'],
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['call_volume']['total_outbound_calls'],
                        yAxisID: 'y-axis-1',
                    }, {
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response['call_volume']['outbound_handled'],
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Dropped',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['call_volume']['outbound_dropped'],
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
                        labels: response['call_volume']['duration_time'],
                        datasets: [{
                            label: 'Inbound',
                            borderColor: chartColors.orange,
                            backgroundColor: chartColors.orange,
                            fill: false,
                            data: response['call_volume']['inbound_duration'],
                            yAxisID: 'y-axis-1',
                        },{
                            label: 'Outbound',
                            borderColor: chartColors.green,
                            backgroundColor: chartColors.green,
                            fill: false,
                            data: response['call_volume']['outbound_duration'],
                            yAxisID: 'y-axis-1',
                        }]
                    };

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

        $.ajax({
            'async': false,
            url: '../../adminoutbounddash/app/ajax/sales_per_hour_per_rep.php',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){
                console.log(response);
                var tot_mins = $('#total_minutes .outbound .data.outbound').text();
                tot_mins = parseInt(tot_mins);
                var tot_sales = response['total_sales'];
                
                if(tot_sales){
                    var sales_per_hour = (tot_mins / 60) / tot_sales;
                    sales_per_hour = Math.round(sales_per_hour * 100) / 100;
                    $('#sales_per_hour').text(sales_per_hour);
                }else{
                    $('#sales_per_hour').text('0');
                }

                $('#total_sales').html(response['total_sales']);
                $('#sales_per_hour_per_rep tbody').empty();
                if(response['sales_per_hour_per_rep'].length){
                
                    var trs;
                    for (var i = 0; i < response['sales_per_hour_per_rep'].length; i++) {
                        if(response['sales_per_hour_per_rep'][i]['Rep'] != ''){
                            trs+='<tr><td>'+response['sales_per_hour_per_rep'][i]['Rep']+'</td><td>'+response['sales_per_hour_per_rep'][i]['Sales']+'</td><td>'+response['sales_per_hour_per_rep'][i]['PerHour']+'</td></tr>';
                        }
                    }
                    $('#sales_per_hour_per_rep').append(trs);
                }else{
                    $('#sales_per_hour_per_rep').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#sales_per_hour_per_rep');
                }

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#sales_per_hour_per_rep');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    calls_by_campaign:function(datefilter, chartColors){

        $.ajax({
            'async': false,
            url: '../../adminoutbounddash/app/ajax/calls_by_campaign.php',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                if(window.calls_by_campaign_chart != undefined){
                    window.calls_by_campaign_chart.destroy();
                }
                console.log(response);
                console.log(response['Campaigns'].length);

                var response_length = response['Counts'].length;
                var chart_colors_array= Dashboard.return_chart_colors(response_length, chartColors);

               var calls_by_campaign_data = {
                    datasets: [{
                        data: response['Counts'],
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
                    title: {
                        fontColor:'#203047',
                        fontSize:16,
                        display: true,
                        text: 'AGENT CALL COUNT'
                    },
                    labels: response['Campaigns']
                };
                
                var calls_by_campaign_options={
                    responsive: true,
                    legend: {
                    display: false
                    },
                    tooltips: {
                        enabled:true,
                    },title: {
                        fontColor:'#203047',
                        fontSize:16,
                        display: true,
                        text: 'CALLS BY CAMPAIGN'
                    },
                }

                var ctx = document.getElementById('calls_by_campaign').getContext('2d');

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

        $.ajax({
            'async': false,
            url: '../../adminoutbounddash/app/ajax/agent_talk_time.php',
            type: 'POST',
            dataType: 'json',
            data:{campaign:campaign, datefilter:datefilter},
            success:function(response){
                console.log(response);

                $('#agent_call_count tbody').empty();
                $('#agent_talk_time tbody').empty();

                if(response['agent_call_count'].length){
                
                    let trs;
                    for (var i = 0; i < response['agent_call_count'].length; i++) {
                        if(response['agent_call_count'][i]['Rep'] != ''){
                            trs+='<tr><td>'+response['agent_call_count'][i]['Rep']+'</td><td>'+response['agent_call_count'][i]['Count']+'</td><td>'+response['agent_call_count'][i]['AvgCount']+'</td></tr>';
                        }
                    }
                    $('#agent_call_count').append(trs);
                }else{
                    $('#agent_call_count').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_call_count');
                }

                if(response['agent_talk_time'].length){
                
                    let trs;
                    for (var i = 0; i < response['agent_talk_time'].length; i++) {
                        if(response['agent_talk_time'][i]['Rep'] != ''){
                            trs+='<tr><td>'+response['agent_talk_time'][i]['Rep']+'</td><td>'+response['agent_talk_time'][i]['Duration']+'</td><td>'+response['agent_talk_time'][i]['AvgDuration']+'</td></tr>';
                        }
                    }
                    $('#agent_talk_time').append(trs);
                }else{
                    $('#agent_talk_time').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#agent_talk_time');
                }

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#agent_talk_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });        
    },

    total_calls:function(datefilter){

        $.ajax({
            'async': false,
            url: '../../adminoutbounddash/app/ajax/total_calls.php',
            type: 'POST',
            dataType: 'json',
            data:{datefilter:datefilter},
            success:function(response){
                console.log(response);
                $('#total_calls .total').html(response['total_calls']['total']);
                $('#total_calls p.inbound').html(response['total_calls']['inbound']);
                $('#total_calls p.outbound').html(response['total_calls']['outbound']);
                $('.filter_time_camp_dets p').html(response['total_calls']['details']);
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#total_calls .divider');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    update_datefilter:function(datefilter){
        $.ajax({
            url: '../../adminoutbounddash/app/ajax/update_datefilter.php',
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
        
        if(datefilter !='custom'){
            $('.preloader').show();
           $.ajax({
            url: '../../adminoutbounddash/app/ajax/set_campaign.php',
            type: 'POST',
            dataType: 'json',
            data: {datefilter:datefilter,campaign: campaign, inorout:inorout},
            success:function(response){
                Dashboard.get_call_volume(inorout, datefilter, Dashboard.chartColors);
                Dashboard.agent_talk_time(datefilter, Dashboard.chartColors);
                Dashboard.sales_per_hour_per_rep(datefilter, Dashboard.chartColors);
                Dashboard.calls_by_campaign(datefilter, Dashboard.chartColors);
                Dashboard.total_calls(datefilter);
                Dashboard.update_datefilter(datefilter);
                $('.preloader').fadeOut('slow');
            }
          });          
        }
    },

    filter_campaign:function(){

        $('.preloader').show();

        $(this).siblings().removeClass('active')
        $(this).addClass('active');
        var active_date = $('.date_filters li.active');
        datefilter = $('#datefilter').val();
        var inorout =$('#inorout').val();
        var campaign = $(this).text();

        $.ajax({
            url: '../../adminoutbounddash/app/ajax/set_campaign.php',
            type: 'POST',
            dataType: 'json',
            data: {datefilter:datefilter,campaign: campaign, inorout:inorout},
            success:function(response){
                Dashboard.get_call_volume(inorout, datefilter, Dashboard.chartColors);                
                Dashboard.agent_talk_time(datefilter, Dashboard.chartColors);
                Dashboard.sales_per_hour_per_rep(datefilter, Dashboard.chartColors);
                Dashboard.calls_by_campaign(datefilter, Dashboard.chartColors);
                Dashboard.total_calls(datefilter);
                Dashboard.update_datefilter(datefilter);

                $('.preloader').fadeOut('slow');
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
        $('#inorout').val();

        $('.startdate').val('');
        $('.enddate').val('');
        $('#datefilter_modal').modal('toggle');
        $('#datefilter').val(start_date + ' ' + end_date);
        
        Dashboard.get_call_volume(inorout, start_date + ' ' + end_date, Dashboard.chartColors);        
        Dashboard.agent_talk_time(start_date + ' ' + end_date, Dashboard.chartColors);
        Dashboard.sales_per_hour_per_rep(start_date + ' ' + end_date, Dashboard.chartColors);
        Dashboard.calls_by_campaign(start_date + ' ' + end_date, Dashboard.chartColors);
        Dashboard.total_calls(start_date + ' ' + end_date);
        Dashboard.update_datefilter(start_date + ' ' + end_date);
        
        $('.preloader').fadeOut('slow');
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

    resizeCardTableDivs();

    if ($(window).width() > 1010) {
        $(window).on('resize', function(){
            resizeCardTableDivs();
        });
    }

    function resizeCardTableDivs(){
        var height_dt = $('.get_hgt').height();
        console.log(height_dt);
        // $('.set_hgt').height(height_dt);
        // height_dt=height_dt-20;
        $('.set_hgt').css({'min-height':height_dt});
        $('.set_hgt').css({'max-height':height_dt});
        
    }

    $('.enddate').datepicker({maxDate: '0'});
    $('.startdate').datepicker({maxDate: '0'});
    
});


