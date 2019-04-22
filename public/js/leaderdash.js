Chart.pluginService.register({
    beforeDraw: function (chart) {
        if (chart.config.options.elements.center) {
            //Get ctx from string
            var ctx = chart.chart.ctx;

            //Get options from the center object in options
            var centerConfig = chart.config.options.elements.center;
            var fontStyle = centerConfig.fontStyle || 'Arial';
            var txt = centerConfig.text;
            var color = '#203047';
            var sidePadding = centerConfig.sidePadding || 20;
            var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
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
            ctx.font = fontSizeToUse + "px " + fontStyle;
            ctx.fillStyle = color;

            //Draw text in center
            ctx.fillText(txt, centerX, centerY);
        }
    }
});

var Dashboard = {

    chartColors: {
        red: 'rgb(255,67,77)',
        orange: 'rgb(228,154,49)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(51,160,155)',
        blue: 'rgb(1,1,87)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(68,68,68)'
    },

    datefilter: document.getElementById("datefilter").value,
    campaign: document.getElementById("campaign").value,
    inorout: document.getElementById("inorout").value,
    inorout_toggled: false,

    init: function () {
        this.update_filters(this.datefilter, this.campaign, this.inorout);
        this.get_call_volume(this.chartColors);
        this.leader_board();
        this.calls_by_campaign(this.chartColors);
        Dashboard.eventHandlers();
    },

    eventHandlers: function () {
        $('.date_filters li a').on('click', this.filter_date);
        $('.filter_campaign li').on('click', this.filter_campaign);
        $('.submit_date_filter').on('click', this.custom_date_filter);
        $('.card-12 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
    },

    display_error: function (div, textStatus, errorThrown) {
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reolad the page.</p>');
    },

    filter_date: function (e) {
        e.preventDefault();

        $(this).parent().siblings().removeClass('active');
        $(this).parent().addClass('active');
        var campaign = $('#campaign').val();
        var inorout = $('#inorout').val();

        datefilter = $(this).data('datefilter');

        $('#datefilter').val(datefilter);
        $('#inorout').val();

        if (datefilter != 'custom') {
            $('.preloader').show(400, function () {
                Dashboard.update_filters(datefilter, campaign, inorout);
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.leader_board();
                Dashboard.calls_by_campaign(Dashboard.chartColors);
            });
        }
        $('.preloader').fadeOut('slow');
    },

    filter_campaign: function () {
        $(this).siblings().removeClass('active')
        $(this).addClass('active');

        var campaign = $(this).text();
        $('#campaign').val(campaign);

        var datefilter = $('#datefilter').val();
        var inorout = $('#inorout').val();

        if (datefilter != 'custom') {
            $('.preloader').show(400, function () {
                Dashboard.update_filters(datefilter, campaign, inorout);
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.leader_board();
                Dashboard.calls_by_campaign(Dashboard.chartColors);
            });
        }
        $('.preloader').fadeOut('slow');
    },

    custom_date_filter: function () {
        $('#datefilter_modal').hide();
        $('.modal-backdrop').hide();

        $('.preloader').show(400, function () {
            var start_date = $('.startdate').val(),
                end_date = $('.enddate').val()
                ;
            datefilter = start_date + ' ' + end_date;

            var inorout = $('#inorout').val();
            var campaign = $('#campaign').val();

            $('#inorout').val();

            $('.startdate').val('');
            $('.enddate').val('');
            $('#datefilter_modal').modal('toggle');
            $('#datefilter').val(start_date + ' ' + end_date);

            Dashboard.update_filters(datefilter, campaign, inorout);
            Dashboard.get_call_volume(Dashboard.chartColors);
            Dashboard.leader_board();
            Dashboard.calls_by_campaign(Dashboard.chartColors);
        });

        $('.preloader').fadeOut('slow');
    },

    update_filters: function (datefilter, campaign, inorout) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: 'leaderdashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { datefilter: datefilter, campaign: campaign, inorout: inorout },
            success: function (response) {
            }
        });
    },

    leader_board: function () {
        $.ajax({
            'async': false,
            url: 'leaderdashboard/leader_board',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('table.table tbody').empty();
                var tr = '<tr class="lowpad"><th>Rep</th><th># Calls</th><th>Talk Time</th><th># Sales</th></tr>';

                for (var i = 0; i < response['leader_board'].length; i++) {
                    tr += '<tr class="results"><td>' + response['leader_board'][i]['Rep'] + '</td><td>' + response['leader_board'][i]['Call Count'] + '</td><td>' + response['leader_board'][i]['Talk Secs'] + '</td><td>' + response['leader_board'][i]['Sales'] + '</td></tr>';
                }

                $('table.table tbody').append(tr);
            }
        });
    },

    return_chart_colors: function (response_length, chartColors) {
        const chart_colors = Object.keys(Dashboard.chartColors)
        var chart_colors_array = [];

        var j = 0;
        for (var i = 0; i < response_length; i++) {
            if (j == chart_colors.length) {
                j = 0;
            }
            chart_colors_array.push(eval('chartColors.' + chart_colors[j]));
            j++;
        }

        return chart_colors_array;
    },

    get_call_volume: function (chartColors) {

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
        $.ajax({
            'async': false,
            url: 'leaderdashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                $('.filter_time_camp_dets p').html(response['call_volume']['details']);

                $('.total_calls_out p').html(response['call_volume']['tot_outbound']);
                $('.total_calls_in p').html(response['call_volume']['tot_inbound']);

                var total_calls_int = 0;
                if (response['call_volume']['total'] != null) {
                    total_calls_int = response['call_volume']['total'];
                }
                $('.call_volume_details p.total').html('Total Calls: ' + total_calls_int);
                var call_volume_data = {

                    labels: response['call_volume']['time_labels'],
                    datasets: [{
                        label: 'Inbound',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['call_volume']['inbound'],
                        yAxisID: 'y-axis-1',
                    }, {
                        label: 'Outbound',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['call_volume']['outbound'],
                        yAxisID: 'y-axis-1'
                    }, {
                        label: 'Manual',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response['call_volume']['manual'],
                        yAxisID: 'y-axis-1'
                    }]
                };

                var call_volume_options = {
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

                // call volume line graph
                var ctx = document.getElementById('call_volume').getContext('2d');
                if (window.call_volume_chart != undefined) {
                    window.call_volume_chart.destroy();
                }
                window.call_volume_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_data,
                    options: call_volume_options
                });


            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);

            }
        });
    },

    calls_by_campaign: function (chartColors) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: 'leaderdashboard/calls_by_campaign',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                if (window.calls_by_campaign_chart != undefined) {
                    window.calls_by_campaign_chart.destroy();
                }

                var response_length = response['calls_by_campaign']['call_count'].length;
                var chart_colors_array = Dashboard.return_chart_colors(response_length, chartColors);

                var calls_by_campaign_data = {
                    datasets: [{
                        data: response['calls_by_campaign']['call_count'],
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
                        fontColor: '#203047',
                        fontSize: 16,
                        display: true,
                        text: 'AGENT CALL COUNT'
                    },
                    labels: response['calls_by_campaign']['agent_call_campaigns']
                };

                var agent_calls_by_campaign_data = {
                    datasets: [{
                        data: response['calls_by_campaign']['rep_call_count'],
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
                        fontColor: '#203047',
                        fontSize: 16,
                        display: true,
                        // text: 'AGENT CALL COUNT'
                    },
                    labels: response['calls_by_campaign']['agent_call_campaigns']
                };

                var calls_by_campaign_options = {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: true,
                    }, title: {
                        fontColor: '#203047',
                        fontSize: 16,
                        display: true,
                        text: 'CALLS BY CAMPAIGN'
                    },
                }

                var agent_calls_by_campaign_options = {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: true,
                    }, title: {
                        fontColor: '#203047',
                        fontSize: 16,
                        display: true,
                        text: 'AGENT CALLS BY CAMPAIGN'
                    },
                }

                var ctx = document.getElementById('calls_by_campaign').getContext('2d');

                window.calls_by_campaign_chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: calls_by_campaign_data,
                    options: calls_by_campaign_options
                });

                var ctx = document.getElementById('agent_calls_by_campaign').getContext('2d');

                window.calls_by_campaign_chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: agent_calls_by_campaign_data,
                    options: agent_calls_by_campaign_options
                });
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#avg_handle_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    call_volume_type: function () {
        Dashboard.inorout = $(this).data('type');
        datefilter = $('#datefilter').val();
        $('#inorout').val(Dashboard.inorout);
        $(this).parent().parent().find('.inandout').hide();
        $(this).parent().parent().find('.' + Dashboard.inorout).show();
    },

    title_options: {
        fontColor: '#144da1',
        fontSize: 16,
    }
}

$(document).ready(function () {

    Dashboard.init();

    $('.count').each(function () {
        $(this).prop('Counter', 0).animate({
            Counter: $(this).text()
        }, {
                duration: 1500,
                easing: 'swing',
                step: function (now) {
                    $(this).text(Math.ceil(now));
                }
            });
    });

    $('.enddate').datepicker({ maxDate: '0' });
    $('.startdate').datepicker({ maxDate: '0' });

});
