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
    time: new Date().getTime(),

    init:function(){
        $.when(this.get_call_volume(this.inorout, this.datefilter, this.chartColors), this.call_details(this.datefilter, this.chartColors), this.sales_per_campaign(this.datefilter, this.chartColors, this.chartColors2)).done(function(){

            $('.preloader').fadeOut('slow');
            Master.check_reload();
            Dashboard.resizeDivs();
        });

        Dashboard.eventHandlers();
    },

    eventHandlers:function(){
        $('.card-12 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
    },

    display_error:function(div, textStatus, errorThrown){
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reolad the page.</p>');
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
        $.when(this.get_call_volume(this.inorout, this.datefilter, this.chartColors), this.call_details(this.datefilter, this.chartColors), this.sales_per_campaign(this.datefilter, this.chartColors, this.chartColors2)).done(function(){

            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
    },

    call_details:function(datefilter, chartColors){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/leaderdashboard/call_details',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                Master.flip_card(response.call_details.repsales.length, '#agent_sales_per_hour');

                $('.salesleaderboardtable, #agent_sales_per_hour, #agent_sales_per_hour_graph').parent().find('.no_data').remove();                
                $('.salesleaderboardtable tbody, #agent_sales_per_hour tbody').empty();

                var leaderboard_trs='<tr class="lowpad"><th>Rep</th><th># Calls</th><th>Talk Time</th><th># Sales</th></tr>';
                for (var i=0; i < response.call_details.leaders.length; i++) {
                    leaderboard_trs+= '<tr class="results"><td>'+response.call_details.leaders[i].Rep+'</td><td>'+Master.formatNumber(response.call_details.leaders[i].CallCount)+'</td><td>'+response.call_details.leaders[i].TalkSecs+'</td><td>'+Master.formatNumber(response.call_details.leaders[i].Sales)+'</td></tr>';
                }

                $('.salesleaderboardtable tbody').append(leaderboard_trs);

               
                if(response.call_details.repsales.length){
                    var agent_sales_trs;

                    for (var i=0; i < response.call_details.repsales.length; i++) {
                        agent_sales_trs+= '<tr class="results"><td>'+response.call_details.repsales[i].Rep+'</td><td>'+response.call_details.repsales[i].PerHour+'</td></tr>';
                    }

                    $('#agent_sales_per_hour tbody').append(agent_sales_trs);
                }else{
                    $('#agent_sales_per_hour_graph, #agent_sales_per_hour tbody').empty();
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#agent_sales_per_hour_graph, #agent_sales_per_hour tbody');
                }

                if(window.rep_avg_handletime_chart != undefined){
                    window.rep_avg_handletime_chart.destroy();
                }

                var response_length = response.Sales.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.Rep);

                var agent_sales_per_hour_data = {
                    datasets: [{
                        data: response.Sales,
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
                    labels: response.Rep
                };

                var agent_sales_per_hour_options={
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled:true,
                        mode: 'single',
                    }
                }

                var ctx = document.getElementById('agent_sales_per_hour_graph').getContext('2d');

                window.agent_sales_per_hour_chart = new Chart(ctx,{
                    type: 'doughnut',
                    data: agent_sales_per_hour_data,
                    options: agent_sales_per_hour_options
                });
            }
        });
    },

    get_call_volume:function(inorout, datefilter, chartColors){

        $('.callvolume_inorout').find('button').removeClass('btn-primary');
        $('.callvolume_inorout').find('button').removeClass('btn-default');
        var activeBtn = $('.callvolume_inorout').find("[data-type='" + this.inorout + "']");
        activeBtn.addClass('btn-primary');
        $(activeBtn).siblings().addClass('btn-default');
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/leaderdashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                inorout:inorout,
                datefilter:datefilter
            },
            success:function(response){
                
                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);

                $('.total_calls_out p').html(Master.formatNumber(response.call_volume.tot_outbound));
                $('.total_calls_in p').html(Master.formatNumber(response.call_volume.tot_inbound));

                var total_calls_int=0;
                if(response.call_volume.total != null){
                    total_calls_int=response.call_volume.total;
                }
                $('.call_volume_details p.total').html('Total Calls: '+total_calls_int);
                var call_volume_data = {

                    labels: response.call_volume.time_labels,
                    datasets: [{
                        label: 'Inbound',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response.call_volume.inbound,
                        yAxisID: 'y-axis-1',
                    },{
                        label: 'Outbound',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response.call_volume.outbound,
                        yAxisID: 'y-axis-1'
                    },{
                        label: 'Manual',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response.call_volume.manual,
                        yAxisID: 'y-axis-1'
                    }]
                };

                var call_volume_options={
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
                    },
                    tooltips: {
                        enabled:true,
                        mode: 'single'
                        
                    }
                }

                // call volume line graph
                var ctx = document.getElementById('call_volume').getContext('2d');
                if(window.call_volume_chart != undefined){
                    window.call_volume_chart.destroy();
                }
                window.call_volume_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_data,
                    options: call_volume_options
                });
                
              
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);
                
            } 
        });
    },

    sales_per_campaign:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/leaderdashboard/sales_per_campaign',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                Master.flip_card(response.Campaign.length, '#sales_per_campaign');

                var sales_camp_arr = [];
                for(var i=0;i<response.Campaign.length;i++){
                    sales_camp_arr.push({Campaign:response.Campaign[i], Sales:response.Sales[i]});
                }

                $('#sales_per_campaign, #sales_per_campaign_graph').parent().find('.no_data').remove();

                $('#sales_per_campaign tbody').empty();
                var spc_trs;
                for (var i=0; i < sales_camp_arr.length; i++) {
                    spc_trs+= '<tr class="results"><td>'+sales_camp_arr[i].Campaign+'</td><td>'+Master.formatNumber(sales_camp_arr[i].Sales)+'</td></tr>';
                }

                $('#sales_per_campaign tbody').append(spc_trs);

                if(response.Campaign.length){
                    $('#sales_per_campaign_graph, #sales_per_campaign').show();
                }else{
                    $('#sales_per_campaign tbody').empty();
                    $('<div class="alert alert-info no_data">No data yet</div>').insertBefore('#sales_per_campaign_graph, #sales_per_campaign');
                }

                 if(window.sales_per_campaign_chart != undefined){
                     window.sales_per_campaign_chart.destroy();
                 }

                 var response_length = response.Campaign.length;
                var chart_colors_array= Master.return_chart_colors_hash(response.Campaign);

                var sales_per_campaign_data = {
                     datasets: [{
                         data: response.Sales,
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
                     labels: response.Campaign
                 };

                 var sales_per_campaign_options={
                     responsive: true,
                     legend: {
                     display: false
                     },
                     tooltips: {
                         enabled:true,
                     }
                 }

                 var ctx = document.getElementById('sales_per_campaign_graph').getContext('2d');

                 window.sales_per_campaign_chart = new Chart(ctx,{
                     type: 'doughnut',
                     data: sales_per_campaign_data,
                     options: sales_per_campaign_options
                 });

                
                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#sales_per_campaign_graph');
                Dashboard.display_error(div, textStatus, errorThrown);
            } 
        });
    },  

    call_volume_type: function(){
        Dashboard.inorout = $(this).data('type');
        datefilter = $('#datefilter').val();
        $('#inorout').val(Dashboard.inorout);
        $(this).parent().parent().find('.inandout').hide();
        $(this).parent().parent().find('.'+Dashboard.inorout).show();

        var inorout = Dashboard.inorout;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/leaderdashboard/update_filters',
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
    },

    resizeDivs:function(){

        if ($(window).width() > 767) {
            var height_dt = $('.get_hgt').outerHeight();

            $('.set_hgt').css({'min-height':height_dt});  
            $('.set_hgt').css({'max-height':height_dt});    
            $('.total_calls_in, .total_calls_out ').css({'min-height':(height_dt / 2) -20, 'max-height':(height_dt / 2) -20});

            var height_dt2 = $('.get_ldr_ht').innerHeight();
            
            $('.leader_table_div').css({'min-height':height_dt2-25});  
            $('.leader_table_div').css({'max-height':height_dt2-25});  

            $('.leader_table_div').height(height_dt2);
        }
    }
}

$(document).ready(function(){

    $(".flipping_card").flip({trigger: 'manual',reverse:true});
    $(".flip_card_btn").on('click', function(){
        $(this).closest('.flipping_card').flip('toggle');
    });

    Dashboard.init();
        
    $(window).on('resize', function(){
        if ($(window).width() > 1010) {
        Dashboard.resizeDivs();
        }
    });

    
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



