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
	    blue: 'rgb(1,1,87)',
	    orange: 'rgb(228,154,49)',
	    green: 'rgb(51,160,155)',
	    grey: 'rgb(98,98,98)',
	    yellow: 'rgb(255, 205, 86)',
	    lightblue: 'rgb(66, 134, 244)'
	},

	datefilter: document.getElementById("datefilter").value,
	databases: '',
	time: new Date().getTime(),

	init:function(){
		$.when(this.call_volume(this.datefilter, this.chartColors)).done(function () {
		    Master.check_reload();
		});
	},

	call_volume:function(datefilter, chartColors){
		var campaign = $('.filter_campaign li ').text();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/admindurationdashboard/call_volume',
			type: 'POST',
			dataType: 'json',
			data: {
				datefilter:datefilter,
				campaign: campaign
			},

			success: function (response) {

				$('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);
				$('#connect .total').html(response.call_volume.connect_pct);
				$('#system_call .total').html(response.call_volume.system_pct);
				$('#total_minutes .total').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total_seconds));
				$('#total_calls .total').html(response.call_volume.total_calls);

				$('#callstatus_by_minutes_graph, #calls_minutes_per_day_graph, #calls_by_campaign').parent().find('.no_data').remove();

				$('#callstatus_by_minutes_graph').closest('.flipping_card').flip(true);

				////////////////////////////////////////////////////////////
				////    MINUTES BY CALLSTATUS DOUGNUT GRAPH
				///////////////////////////////////////////////////////////

				if (window.minutes_by_callstatus_chart != undefined) {
				    window.minutes_by_callstatus_chart.destroy();
				}

				if(Object.keys(response.call_volume.callstatuses).length){
					const callstatuses_obj = response.call_volume.callstatuses
	                const callstatuses_obj_keys = Object.getOwnPropertyNames(callstatuses_obj);
	                var chart_colors_array = Master.return_chart_colors_hash(callstatuses_obj_keys);

	                $('#call_status_table tbody').empty();

	                if (callstatuses_obj_keys.length) {
	                    var trs;
	                    for (var i = 0; i < callstatuses_obj_keys.length; i++) {
	                        if (callstatuses_obj_keys[i] != '') {
	                            trs += '<tr><td>' + callstatuses_obj_keys[i] + '</td><td>'+Object.values(response.call_volume.callstatuses)[i].Minutes+'</td><td>'+Object.values(response.call_volume.callstatuses)[i].Count+'</td></tr>';
	                        }
	                    }
	                    $('#call_status_table tbody').append(trs);
	                } else {
	                    $('<div class="alert alert-info no_data">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#call_status_table, #sales_per_hour_per_rep_graph');
	                }


					let callstatuses = [];

					if (callstatuses_obj_keys.length) {
					    for (let i = 0; i < callstatuses_obj_keys.length; i++) {
					        callstatuses.push(Object.values(callstatuses_obj)[i]['Minutes']);
					    }
					}

					var callstatus_by_minutes_data = {
						datasets: [{
							data:callstatuses,
							backgroundColor: chart_colors_array,
						}],
					    elements: {
					        center: {
					            color: '#203047',
					            fontStyle: 'Segoeui',
					            sidePadding: 15
					        }
					    },
					    labels: callstatuses_obj_keys,
					};

					var callstatus_by_minutes_options = {
					    responsive: true,
					    legend: {
                        	display: false,
                        	fontColor: Master.tick_color,
	                        labels: {
	                            fontColor: Master.tick_color
	                        },
	                    },
					    tooltips: {
					        enabled: true,
					    }
					}

					var ctx = document.getElementById('callstatus_by_minutes_graph').getContext('2d');

					window.minutes_by_callstatus_chart = new Chart(ctx, {
					    type: 'doughnut',
					    data: callstatus_by_minutes_data,
					    options: callstatus_by_minutes_options
					});
				}else{
					$('<div class="alert alert-info no_data">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#callstatus_by_minutes_graph');
				}

				////////////////////////////////////////////////////////////
				////    CALLS & MINUTES PER DAY BAR GRAPH
				///////////////////////////////////////////////////////////

				if (window.calls_minutes_per_day_chart != undefined) {
				    window.calls_minutes_per_day_chart.destroy();
				}

				if(Object.keys(response.call_volume.dates).length){
					const calls_minutes_per_day_obj = response.call_volume.dates
	                const calls_minutes_per_day_obj_keys = Object.getOwnPropertyNames(calls_minutes_per_day_obj);
	                var chart_colors_array = Master.return_chart_colors_hash(calls_minutes_per_day_obj_keys);
					let call_minutes = [];
					let call_count = [];

					if (calls_minutes_per_day_obj_keys.length) {
					    for (let i = 0; i < calls_minutes_per_day_obj_keys.length; i++) {
					        call_minutes.push(Object.values(calls_minutes_per_day_obj)[i]['Seconds']);
					        call_count.push(Object.values(calls_minutes_per_day_obj)[i]['Count']);
					    }
					}

					var calls_minutes_per_day_data = {
	                 	labels: calls_minutes_per_day_obj_keys,
	                    datasets: [
	                      {
	                        yAxisID: 'A',
	                        label: Lang.get('js_msgs.call_time'),
	                        backgroundColor: chartColors.green,
	                        data: call_minutes
	                      },
	                      {
	                        yAxisID: 'B',
	                        label: Lang.get('js_msgs.call_count'),
	                        backgroundColor: chartColors.orange,
	                        fillOpacity: .5, 
	                        data: call_count
	                      }
	                    ]
	                };

	                var calls_minutes_per_day_options={
	                    responsive: true,
	                    maintainAspectRatio:false,
	                    legend: {  
	                        position: 'bottom',
	                        labels: {
	                            boxWidth: 12,
	                            fontColor: Master.tick_color,
	                        } 
	                    },
	                    scales: {
	                        xAxes: [{
	                            ticks: {
	                                fontColor: Master.tick_color,
	                            },
	                            gridLines: {
	                                color: Master.gridline_color,
	                            },
	                        }],
	                        yAxes: [

	                            {
	                                gridLines: {
	                                    color: Master.gridline_color,
	                                },
	                                id:'A',
	                                type: 'linear',
	                                position:'left',
	                                scalePositionLeft: true,
	                                scaleLabel: {
	                                    fontColor: Master.tick_color,
	                                    display: true,
	                                    labelString: Lang.get('js_msgs.minutes')
	                                },
	                                ticks: {
	                                    beginAtZero: true,
	                                    callback: function(value, index, values) {
	                                       return Math.round((parseInt(value) /60) * 10) / 10;
	                                    }
	                                }
	                            },
	                            {
	                                id:'B',
	                                type: 'linear',
	                                position:'right',
	                                scalePositionLeft: false,
	                                scaleLabel: {
	                                    fontColor: Master.tick_color,
	                                    display: true,
	                                    labelString: Lang.get('js_msgs.call_count')
	                                },
	                                ticks: {
	                                    fontColor: Master.tick_color,
	                                    beginAtZero: true,
	                                }
	                            }

	                        ]
	                    },
	                    tooltips: {
	                        enabled: true,
	                        mode: 'single',
	                        callbacks: {
	                            label: function (tooltipItems, data) {
	                                if (tooltipItems.datasetIndex === 0) {
	                                    return Master.convertSecsToHrsMinsSecs(tooltipItems.yLabel);
	                                }else{
	                                    return tooltipItems.yLabel;
	                                }
	                            }
	                        }
	                    }
	                }

	                var ctx = document.getElementById('calls_minutes_per_day_graph').getContext('2d');

	                window.calls_minutes_per_day_chart = new Chart(ctx, {
	                    type: 'bar',
	                    data: calls_minutes_per_day_data,
	                    options: calls_minutes_per_day_options
	                });
				}else{
					$('<div class="alert alert-info no_data">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#calls_minutes_per_day_graph');
				}

                ///// CALLS BY CAMPAIGN TABLE
                $('#calls_by_campaign tbody').empty();

                var calls_by_campaign_trs='';
                if(response.call_volume.campaigns.length){
	                for (var i=0; i < response.call_volume.campaigns.length; i++) {
	                    calls_by_campaign_trs+= '<tr class="results"><td>'+response.call_volume.campaigns[i].Campaign+'</td><td>'+Master.formatNumber(response.call_volume.campaigns[i].Count)+'</td><td>'+Master.convertSecsToHrsMinsSecs(response.call_volume.campaigns[i].Seconds)+'</td></tr>';
	                }

	                calls_by_campaign_trs+= '<tr class="results"><td><b>Total</b></td><td><b>'+Master.formatNumber(response.call_volume.total_calls)+'</b></td><td><b>'+Master.convertSecsToHrsMinsSecs(response.call_volume.total_seconds)+'</b></td></tr>';
	                $('#calls_by_campaign tbody').append(calls_by_campaign_trs);
	            }else{
	            	$('<div class="alert alert-info no_data top45">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#calls_by_campaign');
	            }

	            // fade out preloader here because .done is not working
	            $('.preloader').fadeOut('slow');
	            Dashboard.resizeDivs();
			}
		});
	},

	refresh: function (datefilter, campaign) {

	    $.when(
	        this.call_volume(datefilter, this.chartColors)).done(function () {
	            Master.check_reload();
	        });
	},

	resizeDivs:function(){
		var outer_height = $('#callstatus_by_minutes_graph').outerHeight();
		$('#calls_minutes_per_day_graph').css({'min-height' : outer_height + 25});
	}
}

$(document).ready(function(){
	Dashboard.init();

	$(".flipping_card").flip({ trigger: 'manual', reverse: true });
    $(".flip_card_btn").on('click', function () {
        $(this).closest('.flipping_card').flip('toggle');
    });

	$(window).on('resize', function(){
	    if ($(window).width() > 1010) {
	    Dashboard.resizeDivs();
	    }
	});
});