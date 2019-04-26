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
        blue: 'rgb(1,1,87)',
        orange: 'rgb(228,154,49)',
        green: 'rgb(51,160,155)',
        grey: 'rgb(98,98,98)',
        yellow: 'rgb(255, 205, 86)',
        lightblue: 'rgb(66, 134, 244)'
    },

    datefilter: document.getElementById("datefilter").value,
    campaign: document.getElementById("campaign").value,
    inorout: document.getElementById("inorout").value,
    inorout_toggled: false,

    init: function () {
        this.update_filters(this.datefilter, this.campaign, this.inorout);
        this.get_call_volume(this.chartColors);
        this.agent_call_count(this.chartColors);
        this.completed_calls();
        this.average_hold_time();
        this.service_level();
        this.abandon_rate();
        this.rep_avg_handletime();
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
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">Something went wrong. Please reload the page.</p>');
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

    filter_date: function (e) {
        e.preventDefault();

        $(this).parent().siblings().removeClass('active');
        $(this).parent().addClass('active');
        var campaign = $('#campaign').val();
        var inorout = $('#inorout').val();

        datefilter = $(this).data('datefilter');
        $('#datefilter').val(datefilter);

        if (datefilter != 'custom') {
            $('.preloader').show(400, function () {
                Dashboard.update_filters(datefilter, campaign, inorout);
                Dashboard.completed_calls();
                Dashboard.average_hold_time();
                Dashboard.abandon_rate();
                Dashboard.agent_call_count(Dashboard.chartColors);
                Dashboard.service_level();
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.rep_avg_handletime();
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
                Dashboard.completed_calls();
                Dashboard.average_hold_time();
                Dashboard.abandon_rate();
                Dashboard.agent_call_count(Dashboard.chartColors);
                Dashboard.service_level();
                Dashboard.get_call_volume(Dashboard.chartColors);
                Dashboard.rep_avg_handletime();
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

            $('.startdate').val('');
            $('.enddate').val('');
            $('#datefilter_modal').modal('toggle');
            $('#datefilter').val(start_date + ' ' + end_date);

            Dashboard.update_filters(datefilter, campaign, inorout);
            Dashboard.completed_calls();
            Dashboard.average_hold_time();
            Dashboard.abandon_rate();
            Dashboard.agent_call_count(Dashboard.chartColors);
            Dashboard.service_level();
            Dashboard.rep_avg_handletime();
            Dashboard.get_call_volume(Dashboard.chartColors);
        });
        $('.preloader').fadeOut('slow');
    },

    // call volume, call duration line graphs & total minutes
    get_call_volume: function (chartColors) {
        var activeBtn = $('.callvolume_inorout').find("[data-type='" + this.inorout + "']");
        $(activeBtn).siblings().addClass('btn-default');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/call_volume',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#total_minutes').find('.total').html(response['call_volume']['total']);
                $('#total_minutes').find('p.inbound').html(response['call_volume']['total_inbound_duration'].toLocaleString());
                $('#total_minutes').find('p.outbound').html(response['call_volume']['total_outbound_duration'].toLocaleString());

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


                if (!Dashboard.inorout_toggled) {

                    var call_duration = {
                        labels: response['call_volume']['duration_time'],
                        datasets: [{
                            label: 'Inbound',
                            borderColor: chartColors.orange,
                            backgroundColor: chartColors.orange,
                            fill: false,
                            data: response['call_volume']['inbound_duration'],
                            yAxisID: 'y-axis-1',
                        }, {
                            label: 'Outbound',
                            borderColor: chartColors.green,
                            backgroundColor: chartColors.green,
                            fill: false,
                            data: response['call_volume']['outbound_duration'],
                            yAxisID: 'y-axis-1',
                        }]
                    };

                    var call_duration_options = {
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

                    if (window.call_duration_chart != undefined) {
                        window.call_duration_chart.destroy();
                    }
                    window.call_duration_chart = new Chart(ctx, {
                        type: 'line',
                        data: call_duration,
                        options: call_duration_options
                    });
                }
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#call_volume_inbound');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    // agent call count & agent call time pie graphs
    agent_call_count: function (chartColors) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/agent_call_count',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                $('#agent_call_count').parent().find('.card_title').remove();
                $('#agent_call_count').parent().find('.no_data').remove();

                if (window.agent_callcnt_chart != undefined) {
                    window.agent_callcnt_chart.destroy();
                }

                var response_length = response['agent_call_count']['count'].length;
                var chart_colors_array = Dashboard.return_chart_colors(response_length, chartColors);



                if (response['agent_call_count']['count'].length) {

                    var agent_call_count_data = {
                        datasets: [{
                            data: response['agent_call_count']['count'],
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
                        labels: response['agent_call_count']['rep']
                    };

                    var agent_call_count_options = {
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
                            text: 'AGENT CALL COUNT'
                        },
                    }

                    var ctx = document.getElementById('agent_call_count').getContext('2d');

                    window.agent_callcnt_chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: agent_call_count_data,
                        options: agent_call_count_options
                    });
                } else {
                    $('<h2 class="card_title">AGENT CALL COUNT</h2><p class="no_data">No data yet</p>').insertBefore('#agent_call_count');
                }

                $('#agent_calltime').parent().find('.card_title').remove();
                $('#agent_calltime').parent().find('.no_data').remove();

                if (window.agent_calltime_chart != undefined) {
                    window.agent_calltime_chart.destroy();
                }

                // check that each duration is not 0
                var dur = false;
                for (var i = 0; i < response['agent_call_count']['duration'].length; i++) {
                    if (response['agent_call_count']['duration'][i]) {
                        dur = true;
                    }
                    break;
                }

                if (response['agent_call_count']['duration'].length && dur) {

                    var agent_calltime_data = {
                        datasets: [{
                            data: response['agent_call_count']['duration'],
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
                            text: 'AGENT CALLTIME'
                        },
                        labels: response['agent_call_count']['rep']
                    };

                    var agent_calltime_options = {
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
                            text: 'AGENT CALLTIME'
                        },
                    }

                    var ctx = document.getElementById('agent_calltime').getContext('2d');

                    window.agent_calltime_chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: agent_calltime_data,
                        options: agent_calltime_options
                    });
                } else {
                    $('#agent_calltime').parent().find('.card_title').remove();
                    $('<h2 class="card_title">AGENT CALLTIME</h2><p class="no_data">No data yet</p>').insertBefore('#agent_calltime');
                }

            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#agent_call_count');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    completed_calls: function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/completed_calls',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#completed_calls .total').html(response['completed_calls']['total']);
                $('#completed_calls p.inbound').html(response['completed_calls']['inbound'].toLocaleString());
                $('#completed_calls p.outbound').html(response['completed_calls']['outbound'].toLocaleString());
                $('.selected_datetime').html(response['completed_calls']['details']);
                $('.selected_campaign').html(response['completed_calls']['campaign']);
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#completed_calls .divider');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    average_hold_time: function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/avg_hold_time',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#avg_hold_time').html(response['average_hold_time']['avg_hold_time']);
                $('#total_hold_time').html(response['average_hold_time']['total_hold_time']);
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#avg_hold_time');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    service_level: function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/service_level',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                var service_level_data = {
                    datasets: [{
                        data: [response['service_level']['service_level'], response['service_level']['remainder']],
                        backgroundColor: [
                            Dashboard.chartColors.green,
                            Dashboard.chartColors.grey,
                        ],
                        label: 'Dataset 1'
                    }]
                }

                var service_level_options = {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: false,
                    },
                    elements: {
                        center: {
                            text: response['service_level']['service_level'] + '%',
                            color: '#203047',
                            fontStyle: 'Arial',
                            sidePadding: 15
                        }
                    },
                    title: {
                        fontColor: '#203047',
                        fontSize: 16,
                        display: true,
                        text: 'SERVICE LEVEL'
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },

                    circumference: Math.PI,
                    rotation: Math.PI,
                    cutoutPercentage: 70, // precent
                }

                var ctx = document.getElementById('service_level').getContext('2d');

                if (window.service_level_chart != undefined) {
                    window.service_level_chart.destroy();
                }

                window.service_level_chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: service_level_data,
                    options: service_level_options
                });
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#service_level');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    abandon_rate: function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            'async': false,
            url: '/admindashboard/abandon_rate',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#abandon_calls').html(response['abandon_rate']['abandon_calls'].toLocaleString());
                $('#abandon_rate').html(response['abandon_rate']['abandon_rate']);
            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#abandon_calls');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
    },

    rep_avg_handletime: function () {

        $.ajax({
            'async': false,
            url: '/admindashboard/rep_avg_handletime',
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                $('#rep_avg_handletime').parent().find('.no_data').remove();

                if (response['rep_avg_handletime'].length) {

                    var trs;
                    for (var i = 0; i < response['rep_avg_handletime'].length; i++) {
                        if (response['rep_avg_handletime'][i]['Rep'] != '') {
                            trs += '<tr><td>' + response['rep_avg_handletime'][i]['Rep'] + '</td><td>' + response['rep_avg_handletime'][i]['Average Handle Time'] + '</td></tr>';
                        }
                    }
                    $('#rep_avg_handletime').append(trs);
                } else {
                    $('#rep_avg_handletime').empty();
                    $('<p class="no_data">No data yet</p>').insertBefore('#rep_avg_handletime');
                }

            }, error: function (jqXHR, textStatus, errorThrown) {
                var div = $('#rep_avg_handletime');
                Dashboard.display_error(div, textStatus, errorThrown);
            }
        });
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
        Dashboard.inorout_toggled = true;
        $(this).parent().parent().find('.inandout').hide(0, function () {
            $(this).parent().parent().find('.' + Dashboard.inorout).show();
        });
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
