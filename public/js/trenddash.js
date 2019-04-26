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
        this.get_avg_handle_time(this.chartColors);
        this.agent_calltime(this.chartColors);
        this.service_level(this.chartColors);
        Dashboard.eventHandlers();
    },

    eventHandlers: function () {
        $('.date_filters li a').on('click', this.filter_date);
        $('.filter_campaign li').on('click', this.filter_campaign);
        $('.submit_date_filter').on('click', this.custom_date_filter);
        $('.card-6 .btn-group .btn').on('click', this.toggle_inorout_btn_class);
        $('.callvolume_inorout .btn').on('click', this.call_volume_type);
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
                Dashboard.agent_calltime(Dashboard.chartColors);
                Dashboard.service_level(Dashboard.chartColors);
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.get_avg_handle_time(Dashboard.chartColors);
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
                Dashboard.agent_calltime(Dashboard.chartColors);
                Dashboard.service_level(Dashboard.chartColors);
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.get_avg_handle_time(Dashboard.chartColors);
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
            Dashboard.agent_calltime(Dashboard.chartColors);
            Dashboard.service_level(Dashboard.chartColors);
            Dashboard.get_call_volume(Dashboard.chartColors);
            Dashboard.get_avg_handle_time(Dashboard.chartColors);
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
            url: '/admindashboard/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { datefilter: datefilter, campaign: campaign, inorout: inorout },
            success: function (response) {
            }
        });
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
            url: '/trenddashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                $('.selected_datetime').html(response['call_volume']['details']);
                $('.selected_campaign').html(response['call_volume']['campaign']);

                var total_calls_int = 0;
                if (response['call_volume']['total'] != null) {
                    total_calls_int = response['call_volume']['total'];
                }
                $('.call_volume_details p.total').html('Total Calls: ' + total_calls_int);
                var call_volume_inbound = {

                    labels: response['call_volume']['inbound_time_labels'],
                    datasets: [{
                        label: 'Total',
                        borderColor: chartColors.green,
                        backgroundColor: chartColors.green,
                        fill: false,
                        data: response['call_volume']['total_inbound_calls'],
                        yAxisID: 'y-axis-1',
                    }, {
                        label: 'Handled',
                        borderColor: chartColors.blue,
                        backgroundColor: chartColors.blue,
                        fill: false,
                        data: response['call_volume']['inbound_handled'],
                        yAxisID: 'y-axis-1'
                    }, {
                        label: 'Voicemails',
                        borderColor: chartColors.grey,
                        backgroundColor: chartColors.grey,
                        fill: false,
                        data: response['call_volume']['inbound_voicemails'],
                        yAxisID: 'y-axis-1'
                    }, {
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
                    }, {
                        label: 'Dropped',
                        borderColor: chartColors.orange,
                        backgroundColor: chartColors.orange,
                        fill: false,
                        data: response['call_volume']['outbound_dropped'],
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

                // call volume inbound line graph
                var ctx = document.getElementById('call_volume_inbound').getContext('2d');
                if (window.call_volume_inbound_chart != undefined) {
                    window.call_volume_inbound_chart.destroy();
                }
                window.call_volume_inbound_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_inbound,
                    options: call_volume_options
                });

                // call volume outbound line graph
                var ctx = document.getElementById('call_volume_outbound').getContext('2d');
                if (window.call_volume_outbound_chart != undefined) {
                    window.call_volume_outbound_chart.destroy();
                }
                window.call_volume_outbound_chart = new Chart(ctx, {
                    type: 'line',
                    data: call_volume_outbound,
                    options: call_volume_options
                });
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);

            }
        });
    },

    get_avg_handle_time: function (chartColors) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/trenddashboard/call_details',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                if (response['call_details']['datetime'] != undefined) {
                    $('h2.avg_ht').html('Avg Handle Time: ' + response['call_details']['avg_ht'] + ' minutes');
                    $('h2.avg_tt').html('Avg Talk Time: ' + response['call_details']['avg_call_time'] + ' minutes');

                    var avg_handle_time_data = {
                        labels: response['call_details']['datetime'],
                        datasets: [{
                            label: 'Avg Handle Time',
                            borderColor: chartColors.green,
                            backgroundColor: 'rgba(51,160,155,0.6)',
                            fill: true,
                            data: response['call_details']['avg_handle_time'],
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    var call_details_data = {
                        labels: response['call_details']['datetime'],
                        datasets: [{
                            label: 'Talk Time',
                            borderColor: chartColors.green,
                            backgroundColor: chartColors.green,
                            fill: false,
                            data: response['call_details']['calls'],
                            yAxisID: 'y-axis-1',
                        }, {
                            label: 'Hold Time',
                            borderColor: chartColors.blue,
                            backgroundColor: chartColors.blue,
                            fill: false,
                            data: response['call_details']['hold_time'],
                            yAxisID: 'y-axis-1',
                        }, {
                            label: 'After Call Work',
                            borderColor: chartColors.orange,
                            backgroundColor: chartColors.orange,
                            fill: false,
                            data: response['call_details']['wrapup_time'],
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    var call_details_options = {
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

                    // // call duration inbound line graph
                    var ctx = document.getElementById('avg_handle_time').getContext('2d');

                    if (window.avg_handle_time_chart != undefined) {
                        window.avg_handle_time_chart.destroy();
                    }
                    window.avg_handle_time_chart = new Chart(ctx, {
                        type: 'line',
                        data: avg_handle_time_data,
                        options: call_details_options
                    });

                    var ctx = document.getElementById('call_details').getContext('2d');

                    if (window.call_details_chart != undefined) {
                        window.call_details_chart.destroy();
                    }
                    window.call_details_chart = new Chart(ctx, {
                        type: 'line',
                        data: call_details_data,
                        options: call_details_options
                    });
                }
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#avg_handle_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    agent_calltime: function (chartColors) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/trenddashboard/agent_calltime',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                if (response['agent_calltime']['avg_ct'] != undefined) {
                    $('h2.avg_ct').html('Avg Call Time: ' + response['agent_calltime']['avg_ct'] + ' minutes');
                    $('h2.avg_cc').html('Avg Call Count: ' + response['agent_calltime']['avg_cc'] + ' ');
                }

                var agent_talktime_data = {
                    labels: response['agent_calltime']['rep'],
                    datasets: [
                        {
                            label: "Call Time (minutes)",
                            backgroundColor: chartColors.green,
                            data: response['agent_calltime']['duration']
                        },
                        {
                            label: "Call Count",
                            backgroundColor: chartColors.orange,
                            fillOpacity: .5,
                            data: response['agent_calltime']['total_calls']
                        }
                    ]
                };

                var agent_talktime_options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }

                var ctx = document.getElementById('rep_talktime').getContext('2d');

                if (window.rep_talktime_chart != undefined) {
                    window.rep_talktime_chart.destroy();
                }

                window.rep_talktime_chart = new Chart(ctx, {
                    type: 'bar',
                    data: agent_talktime_data,
                    options: agent_talktime_options
                });

            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#agent_calltime');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    service_level: function (chartColors) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/trenddashboard/service_level',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                var baseline_cnt = response['service_level']['handled_calls'].length;
                var baseline = [];
                for (var i = 0; i < baseline_cnt; i++) {
                    baseline.push(100);
                }

                $('h2.avg_sl').html('Avg Service Level: ' + response['service_level']['avg'] + '%');
                var service_level_data = {

                    labels: response['service_level']['time'],
                    datasets: [{
                        label: 'Service Level ',
                        borderColor: chartColors.green,
                        backgroundColor: 'rgba(51,160,155,0.6)',
                        fill: true,
                        data: response['service_level']['servicelevel'],
                        yAxisID: 'y-axis-1'
                    }, {
                        type: 'line',
                        label: 'Service Level Goal',
                        data: baseline,
                        backgroundColor: 'rgba(238,238,238)'

                    }]
                };

                var service_level_options = {
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
                if (window.service_level_chart != undefined) {
                    window.service_level_chart.destroy();
                }
                window.service_level_chart = new Chart(ctx, {
                    type: 'line',
                    data: service_level_data,
                    options: service_level_options
                });

            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#service_level');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    toggle_inorout_btn_class: function () {
        $(this).siblings().removeClass('btn-primary');
        $(this).siblings().addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass('btn-primary');
    },

    call_volume_type: function () {
        Dashboard.inorout = $(this).data('type');
        datefilter = $('#datefilter').val();
        $('#inorout').val(Dashboard.inorout);
        // Dashboard.get_call_volume(Dashboard.inorout, datefilter, Dashboard.chartColors);
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

    $(".startdate").datepicker({
        maxDate: '0',
        onSelect: function () {

            var dt2 = $('.enddate');
            var startDate = $(this).datepicker('getDate');
            var minDate = $(this).datepicker('getDate');
            var dt2Date = dt2.datepicker('getDate');
            var dateDiff = (dt2Date - minDate) / (86400 * 1000);

            startDate.setDate(startDate.getDate() + 60);
            if (dt2Date == null || dateDiff < 0) {
                dt2.datepicker('setDate', minDate);
            }
            else if (dateDiff > 60) {
                dt2.datepicker('setDate', startDate);
            }

            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
        }
    });

    $('.enddate').datepicker({ maxDate: '0' });

});
