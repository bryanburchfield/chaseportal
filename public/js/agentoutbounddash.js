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

    init:function(){

        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.rep_performance(this.datefilter, this.chartColors), this.call_status_count(this.datefilter, this.chartColors), this.get_sales(this.datefilter)).done(function(){
            $('.card_dropbtn').on('click', this.toggle_dotmenu);
            $('.preloader').fadeOut('slow');
            Dashboard.eventHandlers();
        }); 
    },

    refresh(){
        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.rep_performance(this.datefilter, this.chartColors), this.call_status_count(this.datefilter, this.chartColors), this.get_sales(this.datefilter)).done(function(){
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

    get_sales:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentoutbounddashboard/get_sales',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                $('#total_sales').html(response.total_sales.Sales);
                $('#sales_per_hour').html(response.total_sales.SalesPerHour);
            }
        });
    },    

    get_call_volume:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentoutbounddashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                
                $('#total_outbound .total').html(parseInt(response['call_volume']['tot_outbound']) + parseInt(response['call_volume']['tot_manual']));
                $('#total_inbound .total').html(response['call_volume']['tot_inbound']);

                var total_calls = parseInt(response['call_volume']['outbound']) + parseInt(response['call_volume']['inbound']) + parseInt(response['call_volume']['manual']);

                $('.inbound_total').html(response['call_volume']['tot_inbound']);
                $('.outbound_total').html(response['call_volume']['tot_outbound']);
                $('.manual_total').html(response['call_volume']['tot_manual']);
                $('.total_calls').html(response['call_volume']['tot_total']);
                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);
                
                var call_volume = {

                    labels: response['call_volume']['time'],
                    datasets: [{
                        label: Lang.get('js_msgs.inbound'),
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['call_volume']['inbound'],
                        yAxisID: 'y-axis-1',
                    },{
                        label: Lang.get('js_msgs.outbound'),
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['call_volume']['outbound'],
                        yAxisID: 'y-axis-1'
                    },{
                        label: Lang.get('js_msgs.manual'),
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response['call_volume']['manual'],
                        yAxisID: 'y-axis-1'
                    }]
                };


                var call_volume_options={
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
                var ctx = document.getElementById('call_volume').getContext('2d');
                if(window.call_volume_chart != undefined){
                    window.call_volume_chart.destroy();
                }
                window.call_volume_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume,
                    options: call_volume_options
                });
                
            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume');
                Dashboard.display_error(div, textStatus, errorThrown);
                
            } 
        });
    },

    rep_performance:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentoutbounddashboard/rep_performance',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                
                var call_total = response['rep_performance']['calls_time'],
                    paused_total = response['rep_performance']['paused_time'],
                    waiting_total = response['rep_performance']['waiting_time'],
                    wrapup_total = response['rep_performance']['wrapup_time'],
                    total_total=response['rep_performance']['total']
                ;

                $('.call_total').text(call_total);
                $('.paused_total').text(paused_total);
                $('.waiting_total').text(waiting_total);
                $('.wrapup_total').text(wrapup_total);
                $('.total_total').text(total_total);

                var rep_performance = {
                    labels: response['rep_performance']['time'],
                    datasets: [{
                        label: Lang.get('js_msgs.calls'),
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['rep_performance']['calls'],
                        yAxisID: 'y-axis-1',
                    },{
                        label: Lang.get('js_msgs.paused'),
                        borderColor: chartColors.red,
                        backgroundColor: chartColors.red,
                        fill: false,
                        data: response['rep_performance']['paused'],
                        yAxisID: 'y-axis-1',
                    },{
                        label: Lang.get('js_msgs.waiting'),
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['rep_performance']['waiting'],
                        yAxisID: 'y-axis-1',
                    },
                    {
                        label: Lang.get('js_msgs.wrapup'),
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response['rep_performance']['wrapup'],
                        yAxisID: 'y-axis-1',
                    }
                    ]
                };

                var rep_performance_options={
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
                        text: Lang.get('js_msgs.actions_by_day')
                    }
                }

                // call duration inbound line graph
                var ctx = document.getElementById('rep_performance').getContext('2d');

                if(window.rep_performance_chart != undefined){
                    window.rep_performance_chart.destroy();
                }
                window.rep_performance_chart = new Chart(ctx, {
                    type: 'line',
                    data: rep_performance,
                    options: rep_performance_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#rep_performance');
                Dashboard.display_error(div, textStatus, errorThrown);
            } 
        });
    },

    call_status_count:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentoutbounddashboard/call_status_count',
            type: 'POST',
            dataType: 'json',
            data:{ datefilter:datefilter},
            success:function(response){
                
                var response_length = response['call_status_count']['labels'].length;
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
                
                var call_status_count = {
                  labels: response['call_status_count']['labels'],
                        datasets: [
                          {
                            label: "",
                            backgroundColor: chart_colors_array,
                            data: response['call_status_count']['data']
                          }
                        ]
                };

                var call_status_count_options={
                    responsive: true,
                    maintainAspectRatio:false,
                    legend: { display: false },
                    title: {
                        display: true,
                        text: Lang.get('js_msgs.call_status_by_type')
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
                var ctx = document.getElementById('call_status_count').getContext('2d');

                if(window.call_status_count_chart != undefined){
                  window.call_status_count_chart.destroy();
                }

                window.call_status_count_chart = new Chart(ctx, {
                    type: 'bar',
                    data: call_status_count,
                    options: call_status_count_options
                });

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_status_count');
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
            url: '/agentoutbounddashboard/update_filters',
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
                url: '/agentoutbounddashboard/update_filters',
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



