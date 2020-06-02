Chart.pluginService.register({
    beforeDraw: function (chart) {
        if (chart.config.options.elements.center) {
            //Get ctx from string
            var ctx = chart.chart.ctx;

            //Get options from the center object in options
            var centerConfig = chart.config.options.elements.center;
            var fontStyle = centerConfig.fontStyle || 'Arial';
            var txt = centerConfig.text;
            var color = Master.tick_color;
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
    chartColors2: {
        red: 'rgb(255,67,77, 0.55)',
        orange: 'rgb(228,154,49, 0.55)',
        yellow: 'rgb(255, 205, 86, 0.55)',
        green: 'rgb(51,160,155, 0.55)',
        blue: 'rgb(1,1,87, 0.55)',
        purple: 'rgb(153, 102, 255, 0.55)',
        grey: 'rgb(68,68,68, 0.55)'
    },

    datefilter: document.getElementById("datefilter").value,
    inorout: document.getElementById("inorout").value,
    inorout_toggled: false,
    time: new Date().getTime(),

    display_error: function (div, textStatus, errorThrown) {
        $(div).parent().find('.ajax_error').remove();
        $(div).parent().append('<p class="ajax_error alert alert-danger">'+Lang.get('js_msgs.reload_error_msg')+'</p>');
    },

    init: function () {
        $.when(this.get_compliance(this.chartColors)).done(function () {
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });

        $('body').on('click', '.js__modal_link', this.get_details);
    },

    get_details:function(e){

        e.preventDefault();
        var link = $(this).data('link');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            url: link,
            type: 'POST',
            dataType: 'json',
            data: {},

            success: function (response) {
                console.log(response);

                $('.agent_details tbody').empty();

                var reps = [];
                var pct_worked =[];

                if (response.agent_detail.agent_detail.length) {
                    var trs;
                    for (var i = 0; i < response.agent_detail.agent_detail.length; i++) {
                        reps.push(response.agent_detail.agent_detail[i].Rep);
                        pct_worked.push(response.agent_detail.agent_detail[i].PctWorkedInteger);
                        trs += '<tr><td>' + response.agent_detail.agent_detail[i].Date + '</td><td>' + response.agent_detail.agent_detail[i].Action + '</td><td>' + response.agent_detail.agent_detail[i].Details + '</td><td>' + response.agent_detail.agent_detail[i].WorkedTime + '</td><td>' + response.agent_detail.agent_detail[i].PausedTime + '</td><td>' + response.agent_detail.agent_detail[i].AllowedPausedTime + '</td></tr>';
                    }

                    $('.agent_details tbody').append(trs);
                }
            }
        });
    },

    refresh:function(datefilter, campaign, inorout){
        $.when(this.get_compliance(this.chartColors)).done(function(){
            $('.preloader').fadeOut('slow');
            Master.check_reload();
        });
    },

    get_compliance:function(chartColors){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            url: '/compliancedashboard/get_compliance',
            type: 'POST',
            dataType: 'json',
            data: {},

            success: function (response) {
                console.log(response);
                $('.filter_time_camp_dets p .selected_campaign').html(response.agent_compliance.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.agent_compliance.details[1]);
                $('.agent_compliance_table tbody').empty();

                var reps = [];
                var pct_worked =[];

                if (response.agent_compliance.agent_compliance.length) {
                    var trs;
                    for (var i = 0; i < response.agent_compliance.agent_compliance.length; i++) {
                        reps.push(response.agent_compliance.agent_compliance[i].Rep);
                        pct_worked.push(response.agent_compliance.agent_compliance[i].PctWorkedInteger);
                        trs += '<tr><td><a class="js__modal_link" data-link="'+response.agent_compliance.agent_compliance[i].detail_link+'" href="#" data-toggle="modal" data-target="#agentModal"><i data-id="{{$drip->id}}" class="far fa-eye"></i></a></td><td>' + response.agent_compliance.agent_compliance[i].Rep + '</td><td>' + response.agent_compliance.agent_compliance[i].WorkedTime + '</td><td>' + response.agent_compliance.agent_compliance[i].PausedTime + '</td><td>' + response.agent_compliance.agent_compliance[i].AllowedPausedTime + '</td><td>' + response.agent_compliance.agent_compliance[i].TotWorkedTime + '</td><td>' + response.agent_compliance.agent_compliance[i].PctWorked + '</td></tr>';
                    }

                    $('table.agent_compliance_table').DataTable().clear();
                    $('table.agent_compliance_table').DataTable().destroy();
                    $('.agent_compliance_table tbody').append(trs);
                    $('table.agent_compliance_table').DataTable({
                        fnDrawCallback: function(oSettings) {
                            if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
                              $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                            }
                        },
                        "autoWidth":false,
                        "bDestroy": true,
                        "responsive": true,
                        "language": {
                            "sEmptyTable":     Lang.get('js_msgs.no_data'),
                            "sInfo":           Lang.get('js_msgs.info'),
                            "sInfoEmpty":      Lang.get('js_msgs.info_empty'),
                            "sInfoFiltered":   Lang.get('js_msgs.info_filtered'),
                            "sInfoPostFix":    "",
                            "sInfoThousands":  ",",
                            "sLengthMenu":     Lang.get('js_msgs.length_menu'),
                            "sLoadingRecords": Lang.get('js_msgs.loading'),
                            "sProcessing":     Lang.get('js_msgs.processing'),
                            "sSearch":         Lang.get('js_msgs.search'),
                            "sZeroRecords":    Lang.get('js_msgs.zero_records'),
                            "oPaginate": {
                                "sFirst":    Lang.get('js_msgs.first'),
                                "sLast":     Lang.get('js_msgs.last'),
                                "sNext":     Lang.get('js_msgs.next'),
                                "sPrevious": Lang.get('js_msgs.previous')
                            },
                            "oAria": {
                                "sSortAscending":  Lang.get('js_msgs.ascending'),
                                "sSortDescending": Lang.get('js_msgs.descending')
                            }
                        }
                    });

                    if (window.agent_worked_chart != undefined) {
                        window.agent_worked_chart.destroy();
                    }

                    var chart_colors_array = Master.return_chart_colors_hash(reps);

                    let agent_worked_data = {
                        labels: reps,
                        datasets: [
                            {
                                backgroundColor: chartColors.green,
                                data: pct_worked
                            }
                        ]
                    };

                    let agent_worked_options = {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false,
                        },
                        scales: {
                            yAxes: [
                                {
                                    stacked: true,
                                    position: 'left',
                                    scalePositionLeft: true,
                                    scaleLabel: {
                                        display: true,
                                        labelString: '% '+ Lang.get('js_msgs.worked'),
                                        fontColor: Master.tick_color,
                                    },
                                    ticks: {
                                        fontColor: Master.tick_color,
                                    },
                                    gridLines: {
                                        color: Master.gridline_color,
                                    },
                                }
                            ]
                        },
                        tooltips: {
                            enabled: true,
                            mode: 'label',
                            callbacks: {
                                label: function(tooltipItems, data) {
                                    return tooltipItems.yLabel +'%';
                                }
                            }
                        }
                    }

                    var ctx = document.getElementById('agent_worked_graph').getContext('2d');

                    if (window.agent_worked_chart != undefined) {
                        window.agent_worked_chart.destroy();
                    }

                    window.agent_worked_chart = new Chart(ctx, {
                        type: 'bar',
                        data: agent_worked_data,
                        options: agent_worked_options
                    });
                }
            }
        });
    },

    title_options: {
        fontColor: '#144da1',
        fontSize: 16,
    }
}

$(document).ready(function () {

    Dashboard.init();

    $(".flipping_card").flip({trigger: 'manual',reverse:true});
    $(".flip_card_btn").on('click', function(){
        $(this).closest('.flipping_card').flip('toggle');
    });

    $('.dataTables_filter').addClass('flt_rgt');

});

// $(document).on('click', '.js__modal_link', function(e){
//     e.preventDefault();
//     e.stopPropagation();
// });

