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

		/// dashboard widgets
		$.when(this.call_volume(this.datefilter, this.chartColors)).done(function () {
		    $('.preloader').fadeOut('slow');
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
				console.log(response);

				$('.filter_time_camp_dets p .selected_campaign').html(response.call_volume.details[0]);
                $('.filter_time_camp_dets p .selected_datetime').html(response.call_volume.details[1]);
				$('#connect .total').html(response.call_volume.connect_pct);
				$('#system_call .total').html(response.call_volume.system_pct);
				$('#total_minutes .total').html(Master.convertSecsToHrsMinsSecs(response.call_volume.total_seconds));
				$('#total_calls .total').html(response.call_volume.total_calls);

				////////////////////////////////////////////////////////////
				////    MINUTES BY CALLSTATUS DOUGNUT GRAPH
				///////////////////////////////////////////////////////////

				if (window.minutes_by_callstatus_chart != undefined) {
				    window.minutes_by_callstatus_chart.destroy();
				}

				var response_length = response.call_volume.callstatuses.length;
				const callstatuses_obj = response.call_volume.callstatuses
                const callstatuses_obj_keys = Object.getOwnPropertyNames(callstatuses_obj);
                var chart_colors_array = Master.return_chart_colors_hash(callstatuses_obj_keys);

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
				    labels: callstatuses_obj_keys,
				    elements: {
				        center: {
				            color: '#203047',
				            fontStyle: 'Segoeui',
				            sidePadding: 15
				        }
				    },
				};

				var callstatus_by_minutes_options = {
				    responsive: true,
				    legend: {
				        display: false
				    },
				    tooltips: {
				        enabled: true,
				    }
				}

				var ctx = document.getElementById('callstatus_by_minutes_graph').getContext('2d');

				if (window.minutes_by_callstatus_chart != undefined) {
                    window.minutes_by_callstatus_chart.destroy();
                }

				window.minutes_by_callstatus_chart = new Chart(ctx, {
				    type: 'doughnut',
				    data: callstatus_by_minutes_data,
				    options: callstatus_by_minutes_options
				});

				////////////////////////////////////////////////////////////
				////    CALLS & MINUTES PER DAY BAR GRAPH
				///////////////////////////////////////////////////////////

				const calls_minutes_per_day_obj = response.call_volume.dates
                const calls_minutes_per_day_obj_keys = Object.getOwnPropertyNames(calls_minutes_per_day_obj);
                var chart_colors_array = Master.return_chart_colors_hash(calls_minutes_per_day_obj_keys);
				let call_minutes = [];
				let call_count = [];

				if (calls_minutes_per_day_obj_keys.length) {
				    for (let i = 0; i < calls_minutes_per_day_obj_keys.length; i++) {
				    	console.log(Object.values(calls_minutes_per_day_obj)[i]['Seconds']);
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
                                    labelString: 'HH:MM:SS'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    // callback: function(value, index, values) {
                                    //     if(show_decimal){
                                    //         return Math.round((parseInt(value) /60) * 10) / 10;
                                    //     }else{
                                    //         return Math.round(parseInt(value) / 60);
                                    //     }
                                    // }
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
                                return Master.convertSecsToHrsMinsSecs(tooltipItems.yLabel);
                            }
                        }
                    }
                }

                if (window.calls_minutes_per_day_chart != undefined) {
                    window.calls_minutes_per_day_chart.destroy();
                }
                
                var ctx = document.getElementById('calls_minutes_per_day_graph').getContext('2d');

                window.calls_minutes_per_day_chart = new Chart(ctx, {
                    type: 'bar',
                    data: calls_minutes_per_day_data,
                    options: calls_minutes_per_day_options
                });
			}
		});
	},

	refresh: function (datefilter, campaign) {

	    $.when(
	        this.call_volume(datefilter, this.chartColors),
	        ).done(function () {
	            $('.preloader').fadeOut('slow');
	            // Dashboard.resizeCardTableDivs();
	            Master.check_reload();
	        });

	},
}

$(document).ready(function(){
	Dashboard.init();
});