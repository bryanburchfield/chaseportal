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

    datefilter : document.getElementById("datefilter").value,
    first_search: true,
    active_camp_search: '',

    init:function(){
        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.campaign_stats(this.datefilter, this.chartColors), this.get_total_conversions(this.datefilter, this.chartColors), this.campaign_chart(this.datefilter, this.chartColors)).done(function(){
            $('.card_dropbtn').on('click', this.toggle_dotmenu);
            $('.preloader').fadeOut('slow');
            Dashboard.eventHandlers();
        });        
    },

    toggle_dotmenu:function(){
        $("#card_dropdown").toggle();
    },

    refresh(){
        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.campaign_stats(this.datefilter, this.chartColors), this.get_total_conversions(this.datefilter, this.chartColors), this.campaign_chart(this.datefilter, this.chartColors)).done(function(){
            $('.card_dropbtn').on('click', this.toggle_dotmenu);
            $('.preloader').fadeOut('slow');
        }); 
    },

    eventHandlers:function(){
        $('.date_filters li a').on('click', this.filter_date);
    },

    display_error:function(div, textStatus, errorThrown){
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reolad the page or change your date filters.</p>');
    },

    get_call_volume:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/agentcampaigndashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                $('#total_inbound .total').html(response.call_volume.rep_inbound);
                $('#avg_handle_time').html(response.call_volume.rep_avg_handle_time);
                $('#handled_calls .total').html(response.call_volume.rep_handled);
                $('#total_talktime').html(response.call_volume.rep_talk_time );

                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);

                var total_calls = parseInt(response.call_volume.outbound) + parseInt(response.call_volume.inbound) + parseInt(response.call_volume.manual);

                var response_handled = {
                    'rep_handled': response.call_volume.rep_handled,
                    'tot_handled': response.call_volume.tot_handled,
                }

                response_handled=Object.entries(response_handled);
                if(response_handled[0][1] > response_handled[1][1]){
                    $('.handled_stats .outer .'+response_handled[0][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});
                    
                    $('.handled_stats .outer .'+response_handled[1][0]).animate(
                        {width:response_handled[1][1]/ response_handled[0][1]*100+"%"},
                        {duration: 1000});
                }else{
                    
                   $('.handled_stats .outer .'+response_handled[1][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});

                    $('.handled_stats .outer .'+response_handled[0][0]).animate(
                        {width:response_handled[0][1]/ response_handled[1][1]*100+"%"},
                        {duration: 1000});
                }

                $('.handled_stats .outer .'+response_handled[0][0]).parent().next('.total').text(Dashboard.formatNumber(response_handled[0][1]));
                $('.handled_stats .outer .'+response_handled[1][0]).parent().next('.total').text(Dashboard.formatNumber(response_handled[1][1]));

                var response_avg_handle_time = {
                    'rep_avg_handle_time': response.call_volume.avg_handle_time_secs,
                    'rep_talk_time': response.call_volume.rep_talk_time_secs,
                }

                response_avg_handle_time=Object.entries(response_avg_handle_time);

                if(response_avg_handle_time[0][1] > response_avg_handle_time[1][1]){
                    $('.avg_handle_time_stats .'+response_avg_handle_time[0][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});
                    $('.avg_handle_time_stats .'+response_avg_handle_time[1][0]).animate(
                        {width:response_avg_handle_time[1][1]/ response_avg_handle_time[0][1]*100+"%"},
                        {duration: 1000});
                }else{
                   $('.avg_handle_time_stats .'+response_avg_handle_time[1][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});
                    $('.avg_handle_time_stats .'+response_avg_handle_time[0][0]).animate(
                        {width:response_avg_handle_time[0][1]/ response_avg_handle_time[1][1]*100+"%"},
                        {duration: 1000});
                }

                $('.avg_handle_time_stats .outer .'+response_avg_handle_time[0][0]).parent().next('.total').text(Dashboard.formatNumber(response_avg_handle_time[0][1]));
                $('.avg_handle_time_stats .outer .'+response_avg_handle_time[1][0]).parent().next('.total').text(Dashboard.formatNumber(response_avg_handle_time[1][1]));


            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume');
                Dashboard.display_error(div, textStatus, errorThrown);

            }
        });
    },

    formatNumber: function (num) {
        return Math.abs(num) > 999 ? Math.sign(num)*((Math.abs(num)/1000).toFixed(1)) + 'k' : num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    campaign_chart:function(datefilter, chartColors){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/agentcampaigndashboard/campaign_chart',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                var campaign_calls = {
                    labels: response.campaign_chart.times,
                    datasets: []
                };

                var j=0;
                const chart_colors = Object.keys(Dashboard.chartColors)
                var chart_colors_array=[];
                var j=0;
                for (var i=0; i < response.campaign_chart.campaign_calls.length; i++) {
                    if(j==chart_colors.length){
                        j=0;
                    }
                    chart_colors_array.push(eval('chartColors.'+chart_colors[j]));
                    j++;
                }

                for(var i=0; i<response.campaign_chart.campaign_calls.length;i++){
                    if(j==chart_colors_array.length){j=0;}
                    campaign_calls.datasets.push({
                            label: response.campaign_chart.campaign_calls[i].campaign,
                            borderColor: chart_colors_array[j],
                            backgroundColor: chart_colors_array[j],
                            fill: false,
                            data: response.campaign_chart.campaign_calls[i].calls,
                            yAxisID: 'y-axis-1',
                        });
                    j++;
                }

                var campaign_calls_options={
                    responsive: true,
                    maintainAspectRatio:false,
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
                    title: {
                        display: true,
                        text: Lang.get('js_msgs.total_calls')
                    }
                }

                // call volume inbound line graph
                var ctx = document.getElementById('campaign_calls').getContext('2d');
                if(window.campaign_calls_chart != undefined){
                    window.campaign_calls_chart.destroy();
                }
                window.campaign_calls_chart = new Chart(ctx, {
                    type: 'line',
                    data: campaign_calls,
                    options: campaign_calls_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#campaign_calls');
                Dashboard.display_error(div, textStatus, errorThrown);

            }
        });
    },

    get_total_conversions:function(datefilter, chartColors){

        return $.ajax({
            async: true,
            url: '/agentcampaigndashboard/get_sales',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                response=Object.entries(response);

                if(response[0][1] > response[1][1]){
                    $('.interactions_stats .'+response[0][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});
                    $('.interactions_stats .'+response[1][0]).animate(
                        {width:response[1][1]/ response[0][1]*100+"%"},
                        {duration: 1000});
                }else{
                   $('.interactions_stats .'+response[1][0]).animate(
                        {width:100+"%"},
                        {duration: 1000});
                    $('.interactions_stats .'+response[0][0]).animate(
                        {width:response[0][1]/ response[1][1]*100+"%"},
                        {duration: 1000});
                }

                $('.interactions_stats .outer .'+response[0][0]).parent().next('.total').text(Dashboard.formatNumber(response[0][1]));
                $('.interactions_stats .outer .'+response[1][0]).parent().next('.total').text(Dashboard.formatNumber(response[1][1]));
            }
        });
    },

    campaign_stats:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            async: true,
            url: '/agentcampaigndashboard/campaign_stats',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){

                $('.campaign_stats_table tbody, .campaign_totals_table tbody').empty();

                if (response.campaign_stats.Campaign.length) {
                    var trs='';
                    for (var i = 0; i < response.campaign_stats.Campaign.length; i++) {
                        trs += '<tr><td>' + response.campaign_stats.Campaign[i] + '</td><td>' + response.campaign_stats.AvgTalkTime[i] + '</td><td>' + response.campaign_stats.AvgHoldTime[i] + '</td><td>' + response.campaign_stats.AvgHandleTime[i] + '</td><td>' + response.campaign_stats.DropRate[i] + '</td></tr>';
                    }
                    $('.campaign_stats_table tbody').append(trs);
                }

                if (response.campaign_stats.CallsByCampaign.Campaign.length) {
                    var trs='';
                    for (var i = 0; i < response.campaign_stats.CallsByCampaign.Campaign.length; i++) {
                        trs += '<tr><td>' + response.campaign_stats.CallsByCampaign.Campaign[i] + '</td><td>' + response.campaign_stats.CallsByCampaign.Calls[i] + '</td><td>' + response.campaign_stats.CallsByCampaign.AbandonCalls[i] + '</td><td>' + response.campaign_stats.CallsByCampaign.VoiceMail[i] + '</td></tr>';
                    }
                    $('.campaign_totals_table tbody').append(trs);
                }

                var response_length = response.campaign_stats.TopTen.Campaign.length;
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

                var calls_by_camp = {
                  labels: response.campaign_stats.TopTen.Campaign,
                        datasets: [
                          {
                            label: "",
                            backgroundColor: chart_colors_array,
                            data: response.campaign_stats.TopTen.Calls
                          }
                        ]
                };

                var calls_by_camp_options={
                    responsive: true,
                    maintainAspectRatio:false,
                    legend: { display: false },
                    title: {
                        display: true,
                        text: Lang.get('js_msgs.top_ten_calls_by_camp')
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }

                // call status count bar graph
                var ctx = document.getElementById('calls_by_camp').getContext('2d');

                if(window.calls_by_camp_chart != undefined){
                  window.calls_by_camp_chart.destroy();
                }

                window.calls_by_camp_chart = new Chart(ctx, {
                    type: 'bar',
                    data: calls_by_camp,
                    options: calls_by_camp_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#calls_by_camp');
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
            async: true,
            url: '/agentcampaigndashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {datefilter: datefilter},
            success:function(response){
            }
        });
    },

    filter_date:function(e){
        var that = $(this);
        that.parent().siblings().removeClass('active');
        that.parent().addClass('active');
        datefilter = that.data('datefilter');
        $('#datefilter').val(datefilter);
        var campaigns=[];
        $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function() {
            campaigns.push(that.val());
        });

        Dashboard.datefilter = datefilter;
        
        if(datefilter !='custom'){
            $('.preloader').show();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: '/agentcampaigndashboard/update_filters',
                type: 'POST',
                dataType: 'json',
                data: {dateFilter:datefilter},
                success:function(response){
                    Dashboard.refresh(response);
                }
            });          
        }
    },

    call_volume_type: function(){
        Dashboard.inorout = $(this).data('type');
        datefilter = $('#datefilter').val();
        $('#inorout').val(Dashboard.inorout);
        Dashboard.get_call_volume(Dashboard.inorout, datefilter, Dashboard.chartColors);
        $(this).parent().parent().find('.inandout').hide();
        $(this).parent().parent().find('.'+Dashboard.inorout).show();
    },

    title_options :{
        fontColor:'#144da1',
        fontSize:16,
    },

}

$(document).ready(function(){

    Dashboard.init();

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.matches('.card_dropbtn')) {
            $('.card_dropdown-content').hide();
        }
    }

    $('.stop-propagation').on('click', function (e) {
        e.stopPropagation();
    });
    
    $('.filter_campaign').on('click', '.stop-propagation', function (e) {
        e.stopPropagation();
    });

    $(".startdate").datepicker({
        maxDate: '0',
        onSelect: function () {
            
            var dt2 = $('.enddate');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            var dt2Date = dt2.datepicker('getDate');
            var dateDiff = (dt2Date - minDate)/(86400 * 1000);
            
            startDate.setDate(startDate.getDate() + 60);
            if (dt2Date == null || dateDiff < 0) {
                    dt2.datepicker('setDate', minDate);
            }
            else if (dateDiff > 60){
                    dt2.datepicker('setDate', startDate);
            }

            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
        }
    });

});



