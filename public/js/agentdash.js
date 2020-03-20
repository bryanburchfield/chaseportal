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
        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.campaign_stats(this.datefilter, this.chartColors), this.get_total_conversions(this.datefilter), this.campaign_chart(this.datefilter, this.chartColors)).done(function(){
            $('.card_dropbtn').on('click', this.toggle_dotmenu);
            $('.preloader').fadeOut('slow');
            Dashboard.eventHandlers();
        });        
    },

    toggle_dotmenu:function(){
        $("#card_dropdown").toggle();
    },

    refresh(){
        $.when(this.get_call_volume(this.datefilter, this.chartColors), this.campaign_stats(this.datefilter, this.chartColors), this.get_total_conversions(this.datefilter), this.campaign_chart(this.datefilter, this.chartColors)).done(function(){
            $('.card_dropbtn').on('click', this.toggle_dotmenu);
            $('.preloader').fadeOut('slow');
        }); 
    },

    eventHandlers:function(){
        $('.date_filters li a').on('click', this.filter_date);
        $('.campaign_search').on('keyup', this.search_campaigns);
        $('.filter_campaign').on('click', '.campaign_group', this.adjust_campaign_filters);
        $('.select_campaign').on('click', this.filter_campaign);
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

        $.ajax({
            async: true,
            url: '/agentdashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                $('#avg_handle_time').html(response.call_volume.avg_handle_time);
                $('#total_outbound .total').html(response.call_volume.tot_outbound);
                $('#total_inbound .total').html(response.call_volume.tot_inbound);
                $('#total_talktime').html(response.call_volume.tot_talk_time);

                $('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);

                var total_calls = parseInt(response.call_volume.outbound) + parseInt(response.call_volume.inbound) + parseInt(response.call_volume.manual);

                

            },error: function (jqXHR,textStatus,errorThrown) {
                var div = $('#call_volume');
                Dashboard.display_error(div, textStatus, errorThrown);

            }
        });
    },

    campaign_chart:function(datefilter, chartColors){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentdashboard/campaign_chart',
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

    get_total_conversions:function(datefilter){

        $.ajax({
            async: true,
            url: '/agentdashboard/get_sales',
            type: 'POST',
            dataType: 'json',
            data:{
                datefilter:datefilter
            },
            success:function(response){
                $('.total_conversions').html(response.total_sales);
            }
        });
    },

    campaign_stats:function(datefilter, chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            async: true,
            url: '/agentdashboard/campaign_stats',
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
                        trs += '<tr><td>' + response.campaign_stats.CallsByCampaign.Campaign[i] + '</td><td>' + response.campaign_stats.CallsByCampaign.Calls[i] + '</td></tr>';
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
                var div = $('#rep_performance');
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
            url: '/agentdashboard/update_filters',
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
                url: '/agentdashboard/update_filters',
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

    search_campaigns: function () {
        var query = $(this).val();

        if (Dashboard.first_search) {
            if ($('.filter_campaign li').hasClass('active')) {
                Dashboard.active_camp_search = $('.filter_campaign li.active').text();
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/agentdashboard/campaign_search',
            type: 'POST',
            dataType: 'json',
            data: { query: query },
            success: function (response) {

                var is_array = Array.isArray(response.search_result);
                var obj = response['search_result'];
                $('.filter_campaign .checkbox').remove();
                var campaign_searchresults = '';

                if (!is_array) {
                    var obj = Object.keys(obj).map(function (key) {
                        return [obj[key]];
                    });
                }

                var checked;

                for (var i = 0; i < obj.length; i++) {
                    checked = obj[i].selected;
                    if (checked) { checked = 'checked'; } else { checked = ''; }
                    campaign_searchresults += '<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" ' + checked + ' value="' + obj[i].value + '" name="campaigns"><span>' + obj[i].name + '</span></label></div>';
                }

                Dashboard.first_search = false;

                $('.filter_campaign').append(campaign_searchresults);
            }
        });
    },

    adjust_campaign_filters: function () {

        // Get amount of selected checkboxes
        var checked = [];
        $('.campaign_label input:checked').each(function () {
            checked.push($(this).attr('name'));
        });

        /// check if target is NOT All Camps
        if ($(this).val() != '') {
            // See if others are checked
            if (checked.length) {
                // check if All Camps is checked
                if ($('.filter_campaign .campaign_group').eq(0).is(':checked')) {
                    // uncheck all camps because others are being selected
                    $('.filter_campaign .campaign_group').eq(0).removeAttr('checked');
                }
            }
        } else { /// ALL camps is being checked
            // check if All Camps was already checked
            if ($('.filter_campaign .campaign_group').eq(0).is(':checked')) {
                $('.filter_campaign .campaign_group').removeAttr('checked'); /// uncheck all other camps
                $('.filter_campaign .campaign_group').eq(0).prop('checked', true); // recheck all camps
            }

            if (!checked.length) { // if nothing is selected reselect All Camps because something has to be checked
                $('.filter_campaign .campaign_group').eq(0).prop('checked', true);
            }
        }
    },

    // ran when submit is clicked in the interaction menu
    filter_campaign: function () {

        $('.preloader').show();

        datefilter = $('#datefilter').val();
        var checked = $(".campaign_group:checkbox:checked").length;
        $('.alert').remove();
        $('.campaign_search').val('');

        if (checked) {
            $('.filter_campaign').parent().removeClass('open');
            $('.filter_campaign').prev('.dropdown-toggle').attr('aria-expanded', false);
            var campaigns = [];
            $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function () {
                campaigns.push($(this).val());
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/agentdashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { campaign: campaigns },
            success: function (response) {
                Dashboard.set_campaigns(response);
            }
        });
    },

    // ran after submit is clicked in the interaction menu, after filter_campaign()
    set_campaigns: function (response) {
        var campaigns = [];
        $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function () {
            campaigns.push($(this).val());
            //// if total is selected, uncheck all checkboxes
            if ($(this).val() == '') {
                $('.filter_campaign .checkbox label input[name="campaigns"]:checkbox').removeAttr('checked');
            }
        });

        var is_array = Array.isArray(response.campaigns);
        var obj = response['campaigns'];
        $('.filter_campaign .checkbox').remove();
        var campaign_searchresults = '';

        if (!is_array) {
            var obj = Object.keys(obj).map(function (key) {
                return [obj[key]];
            });
        }
        var checked;

        for (var i = 0; i < obj.length; i++) {
            checked = obj[i].selected;
            if (checked) { checked = 'checked'; } else { checked = ''; }
            campaign_searchresults += '<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" ' + checked + ' value="' + obj[i].value + '" name="campaigns"><span>' + obj[i].name + '</span></label></div>';
        }

        $('.filter_campaign').append(campaign_searchresults);

        Dashboard.refresh(datefilter);
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



