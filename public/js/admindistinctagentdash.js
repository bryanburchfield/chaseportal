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
		Dashboard.eventHandlers();
	},

	eventHandlers: function () {
        $('.logins_drilldown').on('click', 'a', this.change_login_details);
    },

	call_volume:function(datefilter, chartColors){
		var campaign = $('.filter_campaign li ').text();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/admindistinctagentdashboard/call_volume',
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
				$('#distinct_agent_count .total').html(response.call_volume.rep_count);
				$('#avg_reps .total').html(response.call_volume.avg_reps);

				$('#distinct_reps_per_camp_graph, #logins_per_day_graph').parent().find('.no_data').remove();

				////////////////////////////////////////////////////////////
				////    DISTINCT REPS PER CAMP GRAPH
				///////////////////////////////////////////////////////////

				if (window.distinct_reps_per_camp_chart != undefined) {
				    window.distinct_reps_per_camp_chart.destroy();
				}

				if(Object.keys(response.call_volume.campaigns).length){
					const campaigns_obj = response.call_volume.campaigns
	                const campaigns_obj_keys = Object.getOwnPropertyNames(campaigns_obj);
	                var chart_colors_array = Master.return_chart_colors_hash(campaigns_obj_keys);
					let campaigns = [];
					campaigns.push(Object.values(campaigns_obj));

					var distinct_reps_per_camp_data = {
						datasets: [{
							data:campaigns[0],
							backgroundColor: chart_colors_array,
						}],
					    labels: campaigns_obj_keys,
					    elements: {
					        center: {
					            color: '#203047',
					            fontStyle: 'Segoeui',
					            sidePadding: 15
					        }
					    },
					};

					var distinct_reps_per_camp_options = {
					    responsive: true,
					    legend: {
					        display: false
					    },
					    tooltips: {
					        enabled: true,
					    },
					}

					var ctx = document.getElementById('distinct_reps_per_camp_graph').getContext('2d');

					if (window.distinct_reps_per_camp_chart != undefined) {
	                    window.distinct_reps_per_camp_chart.destroy();
	                }

					window.distinct_reps_per_camp_chart = new Chart(ctx, {
					    type: 'doughnut',
					    data: distinct_reps_per_camp_data,
					    options: distinct_reps_per_camp_options
					});
				}else{
					$('<div class="alert alert-info no_data">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#distinct_reps_per_camp_graph');
				}

				// ////////////////////////////////////////////////////////////
				// ////    LOGINS PER DAY BAR GRAPH
				// ///////////////////////////////////////////////////////////


				if (window.logins_per_day_chart != undefined) {
				    window.logins_per_day_chart.destroy();
				}

				if(Object.keys(response.call_volume.dates).length){
					const logins_per_day_obj = response.call_volume.dates
	                const logins_per_day_obj_keys = Object.getOwnPropertyNames(logins_per_day_obj);
	                var chart_colors_array = Master.return_chart_colors_hash(logins_per_day_obj_keys);
					let logins = [];

					if (logins_per_day_obj_keys.length) {
					    for (let i = 0; i < logins_per_day_obj_keys.length; i++) {
					        logins.push(Object.values(logins_per_day_obj));
					    }
					}

					var days='';
					for(var i=0;i<logins_per_day_obj_keys.length;i++){
						days+='<a href="'+logins_per_day_obj_keys[i]+'">'+logins_per_day_obj_keys[i]+'</a>';
					}

					$('.logins_drilldown').append(days);

					var logins_per_day_data = {
	                 	labels: logins_per_day_obj_keys,
	                    datasets: [
	                      {
	                        yAxisID: 'A',
	                        label: Lang.get('js_msgs.call_time'),
	                        backgroundColor: chartColors.green,
	                        data: logins[0]
	                      },
	                    ]
	                };

	                var logins_per_day_options={
	                    responsive: true,
	                    maintainAspectRatio:false,
	                    legend: {
                            display: false
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
	                        yAxes: [{
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
	                                    labelString: Lang.get('js_msgs.logins_per_day'),
	                                },
	                                ticks: {
	                                    fontColor: Master.tick_color,
	                                    beginAtZero: true,
	                                },
	                            }],
	                    },
	                    tooltips: {
	                        enabled: true,
	                        mode: 'single',
	                        callbacks: {
	                            label: function (tooltipItems, data) {
	                            	return tooltipItems.yLabel;
	                            }
	                        }
	                    }
	                }

	                var ctx = document.getElementById('logins_per_day_graph').getContext('2d');

	                window.logins_per_day_chart = new Chart(ctx, {
	                    type: 'bar',
	                    data: logins_per_day_data,
	                    options: logins_per_day_options
	                });

	            }else{
	            	$('<div class="alert alert-info no_data">' + Lang.get('js_msgs.no_data') + '</div>').insertBefore('#logins_per_day_graph');
	            }

                ///// ACTIONS TABLE
                $('#actions tbody').empty();

                var actions_trs='';
                if(response.call_volume.actions.length){
	                for (var i=0; i < response.call_volume.actions.length; i++) {
	                    actions_trs+= '<tr class="results"><td>'+response.call_volume.actions[i].Date+'</td><td>'+response.call_volume.actions[i].Rep+'</td><td>'+response.call_volume.actions[i].Action+'</td></tr>';
	                }
	            }

	            $('table#actions').DataTable().clear();
	            $('table#actions').DataTable().destroy();

                $('#actions tbody').append(actions_trs);
                $('table#actions').DataTable({
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

                $('table#actions').addClass('bs-select');

                // fade out preloader here because .done is not working
	            $('.preloader').fadeOut('slow');
			}
		});
	},

	change_login_details:function(e){
		e.preventDefault();
		var interval = $(this).attr('href');
		console.log(interval);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/admindistinctagentdashboard/get_login_details',
			type: 'POST',
			dataType: 'json',
			data: {
				interval:interval,
			},

			success: function (response) {
				console.log(response);
			}
		});

	},

	refresh: function (datefilter, campaign) {

	    $.when(
	        this.call_volume(datefilter, this.chartColors)).done(function () {
	            Master.check_reload();
	        });
	},
}

$(document).ready(function(){
	Dashboard.init();
});