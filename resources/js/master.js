var Master = {

	chartColors: {
		red: 'rgb(255,67,77)',
		orange: 'rgb(228,154,49)',
		yellow: 'rgb(255, 205, 86)',
		green: 'rgb(51,160,155)',
		blue: 'rgb(1,1,87)',
		purple: 'rgb(153, 102, 255)',
		grey: 'rgb(68,68,68)'
	},

	curpage: '',
	pagesize: '',
	pag_link: '',
	sort_direction: '',
	th_sort: '',
	totpages: '',
	pdf_dl_link: '',

	init: function () {
		$('.pag').clone().insertAfter('div.table-responsive');
		$('.view_report_btn').on('click', this.view_report);
		// $('.add_user').on('submit', this.add_user);
		$('a.remove_user, a.remove_recip_fromall').on('click', this.remove_user);
		$('form.report_filter_form').on('submit', this.submit_report_filter_form);
		$('.report_results').on('click', '.pagination li a', this.click_pag_btn);
		$('body').on('click', '.reports_table thead th a span', this.sort_table);
		$('.report_results').on('change', '.curpage, .pagesize', this.change_pag_inputs);
		$('.reset_sorting_btn').on('click', this.reset_table_sorting);
		$('#campaign_usage #campaign_select, #lead_inventory_sub #campaign_select').on('change', this.get_subcampaigns); // check other reports to see if this needs to belongs to more than campaign_usage
		$('.report_download').on('click', '.report_dl_option.pdf', this.pdf_download_warning);
		$('#report_dl_warning .dl_report').on('click', this.pdf_download2);
		$('.query_dates_first .datetimepicker').on('change', this.query_dates_for_camps);
	},

	// populate campaign multi-select based on dates
	query_dates_for_camps: function () {
		var todate = $('.todate').val(),
			fromdate = $('.fromdate').val()
		report = $('form.report_filter_form').attr('id')
			;

		if (todate != '' && fromdate != '') {
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				}
			});
			$.ajax({
				url: 'get_campaigns',
				type: 'POST',
				dataType: 'json',
				async: false, /////////////////////// use async when rebuilding multi select menus
				data: {
					report: report,
					todate: todate,
					fromdate: fromdate
				},

				success: function (response) {

					$('#campaign_select').empty();
					var camps_select = '<option value""> </option>';
					for (var i = 0; i < response.campaigns.length; i++) {
						camps_select += '<option value="' + response.campaigns[i] + '">' + response.campaigns[i] + '</option>';
					}

					$('#campaign_select').append(camps_select);
					$("#campaign_select").multiselect('rebuild');
					$("#campaign_select").multiselect('refresh');

					// var camps_select='<option value""> </option>',
					// 	camps_title='',
					// 	camps_checkboxes='<li class="multiselect-item filter active" value="0"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span><input class="form-control multiselect-search" type="text" placeholder="Search"><span class="input-group-btn"><button class="btn btn-default multiselect-clear-filter" type="button"><i class="glyphicon glyphicon-remove-circle"></i></button></span></div></li><li class="multiselect-item multiselect-all active"><a tabindex="0" class="multiselect-all"><label class="checkbox"><input type="checkbox" value="multiselect-all">  Select all</label></a></li>'
					// ;

					// for(var i=0; i<response.campaigns.length; i++){
					// 	camps_title += response.campaigns[i]+',';
					// 	camps_select += '<option value="'+response.campaigns[i]+'">'+response.campaigns[i]+'</option>';
					// 	camps_checkboxes+= '<li class="active" style="display: list-item;"><a tabindex="0"><label class="checkbox"><input type="checkbox" value="'+response.campaigns[i]+'"> '+response.campaigns[i]+'</label></a></li>';
					// }

					// $('button.multiselect').attr('title', camps_title);
					// $('#campaign_select').append(camps_select);
					// $('.multiselect-selected-text').text('All Selected ('+response.campaigns.length+')');
					// $('.multiselect-container.dropdown-menu').append(camps_checkboxes);
					// $('.multiselect-container.dropdown-menu').find('li a label input').each(function() {
					// 	$(this).attr('checked', true);
					// });

					// var selected_camps=[];
					// $('body').on('click', '.checkbox input', function(){
					// 	if($(this).is(':checked')){
					// 		$(this).parent().parent().parent().addClass('active');
					// 		selected_camps.push($(this).val());
					// 	}else{
					// 		$(this).parent().parent().parent().removeClass('active');
					// 		selected_camps.pop($(this).val());
					// 	}
					// });
				}
			});
		}
	},

	pdf_download_warning: function (e) {
		e.preventDefault();
		var tot_rows = parseInt($('.totrows').val());
		$('.report_dl_warning .modal-footer button').show();

		if (tot_rows > 1000 && tot_rows < 2000) {
			$('#report_dl_warning').modal('toggle');
			$('.dl_alert.alert').removeClass('alert-danger');
			$('.dl_alert.alert').addClass('alert-warning');
			$('.dl_alert.alert p').text('This is a large dataset. It may be faster to download multiple smaller reports.');
		} else if (tot_rows >= 2000) {
			$('.dl_alert.alert').removeClass('alert-warning');
			$('.dl_alert.alert').addClass('alert-danger');
			$('.dl_alert.alert p').text('Report is too large to download. Please run smaller reports or choose a different format');
			$('.report_dl_warning .modal-footer button').hide();
			$('#report_dl_warning').modal('toggle');
		} else {
			pdf_dl_link = $('.report_dl_option.pdf').attr('href');
			window.open(pdf_dl_link, '_blank');
		}
	},

	pdf_download2: function () {
		pdf_dl_link = $('.report_dl_option.pdf').attr('href');
		window.open(pdf_dl_link);
		$('#report_dl_warning').modal('hide');
		$('.modal-backdrop').remove();
	},

	return_chart_colors: function (response_length, chartColors) {
		const chart_colors = Object.keys(Master.chartColors)
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

	get_subcampaigns: function (e) {
		var campaign = [];
		$('option:selected').each(function () {
			campaign.push($(this).val());
		});

		console.log(campaign);

		// if($('#subcampaign_select').length){
		// 	e.preventDefault();
		// 	var report = $('form.report_filter_form').attr('id');
		// return false;
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: 'master/reports/get_subcampaigns',
			type: 'POST',
			dataType: 'json',
			data: {
				report: report,
				campaign: campaign,
			},

			success: function (response) {
				console.log(response);
				$('#subcampaign_select').empty();

				var subcampaigns = '<option value""> </option>';
				for (var i = 0; i < response.subcampaigns.length; i++) {
					subcampaigns += '<option value="' + response.subcampaigns[i] + '">' + response.subcampaigns[i] + '</option>';
				}

				$('#subcampaign_select').append(subcampaigns);
				$('#subcampaign_select').append(subcampaigns);
				$("#subcampaign_select").multiselect('rebuild');
				$("#subcampaign_select").multiselect('refresh');
			}
		});
		// }
	},

	// add global user
	add_user: function (e) {
		e.preventDefault();

		var group_id = $('.group_id').val(),
			name = $('.name').val(),
			email = $('.email').val(),
			timezone = $('#timezone').val(),
			type = $('#type').val(),
			database = $('#database').val()
			;

		$('form.add_user .alert').remove();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: 'add_user',
			type: 'POST',
			dataType: 'json',
			data: {
				group_id: group_id,
				name: name,
				email: email,
				timezone: timezone,
				type: type,
				database: database
			},

			success: function (response) {

				if (response['add_user'] == false) {
					$('form.add_user').append('<div class="alert alert-danger">User alredy exists</div>');
				} else {
					$('form.add_user').append('<div class="alert alert-success">User successfully added</div>');
					$('.users').append('<p id="user' + response['add_user'][1] + '">' + response['add_user'][0] + ' - <span class="user_name">' + response['add_user'][2] + '</span><a data-toggle="modal" data-target="#deleteRecipModal" class="remove_user" href="#" data-user="' + response['add_user'][1] + '"><i class="glyphicon glyphicon-remove-sign"></i></a></p>');
					setTimeout(function () {
						$('.alert').remove();
					}, 4500);
				}
			}
		});
	},

	// remove global user
	remove_user: function (e) {
		e.preventDefault();
		var username = $(this).data('username');
		var userid = $(this).data('userid');

		$('#deleteRecipModal').find("#userid").val(userid);
		$('#deleteRecipModal').find("#username").val(username);
		$('span.username').text(username);
	},

	// select report from modal
	view_report: function () {
		$('.alert').hide();
		var selected_report = $('input.report_option:checked').val();

		if (selected_report != '' && selected_report != undefined) {
			// window.location ="reports.php?report="+selected_report;
		} else {
			$('#reports_modal .modal-footer').append('<div class="alert alert-danger"><p>Please select a report</p></div>');
		}
	},

	// filter form submission
	submit_report_filter_form: function (e) {
		e.preventDefault();
		$('.preloader').show();

		$([document.documentElement, document.body]).animate({
			scrollTop: $(".table-responsive").offset().top - 100
		}, 1500);

		Master.update_report('', '', 1, '', '');
	},

	// click a pagination button
	click_pag_btn: function (e) {
		e.preventDefault();

		if (!$(this).parent().hasClass('disabled')) {
			this.curpage = $('.curpage').val();
			this.pagesize = $('.pagesize').val();
			this.pag_link = $(this).data('paglink');
			this.sort_direction = $('.sort_direction').text();
			this.th_sort = $('.sorted_by').text();
			Master.update_report(this.th_sort, this.pagesize, this.curpage, this.pag_link, this.sort_direction);
		}
	},

	// sort by clicking th
	sort_table: function (e) {
		e.preventDefault();
		$('.preloader').show();

		var sortedby_parent = $(this).parent().parent();
		this.th_sort = $(sortedby_parent).text();
		$(sortedby_parent).siblings().find('a span').show();
		$(sortedby_parent).siblings().find('a span').removeClass('active');
		$(sortedby_parent).siblings().removeClass('active_column');
		$(sortedby_parent).addClass('active_column');
		$(this).siblings().hide();
		this.curpage = 1
		this.pagesize = 50;
		this.sort_direction = $(this).attr('class');

		if ($(this).hasClass('active')) {
			$(this).siblings().show();
			$(this).removeClass('active');
			$(this).siblings().addClass('active');
			$(this).hide();
			this.sort_direction = $(this).siblings().attr('class').split(' ')[0];
		} else {
			$(this).addClass('active');
		}

		Master.update_report(this.th_sort, this.pagesize, this.curpage, '', this.sort_direction);
	},

	// check if pag input values have changed
	change_pag_inputs: function () {
		var max_pages = parseInt($('.curpage').attr('max')),
			totrows = parseInt($('.totrows').val()),
			pagesize = parseInt($('.pagesize').data('prevval')),
			new_pagesize = parseInt($('.pagesize').val())
			;

		this.curpage = parseInt($('.curpage').val());
		this.sort_direction = $('.sort_direction').text();
		this.th_sort = $('.sorted_by').text();

		// check if page input is greater than max available pages
		if (parseInt($(this).val()) > max_pages && $(this).hasClass('curpage')) {
			var prevval = $(this).data('prevval');
			this.curpage = prevval;
			$('div.errors').text('Attempted page number greater than available pages').show(0).delay(4500).hide(0);
			return false;
		} else {
			if ($(this).hasClass('curpage')) {
				this.curpage = $(this).val();
			}

			// if users changes pagesize set curpage back to 1
			if (pagesize != new_pagesize) {
				this.curpage = 1;
			}

			if ($(this).hasClass('pagesize')) {
				this.pagesize = $(this).val();
				$('.pagesize').val(this.pagesize);
			}

			Master.update_report(this.th_sort, this.pagesize, this.curpage, '', this.sort_direction);
		}
	},

	// reset table sorting
	reset_table_sorting: function (e) {
		e.preventDefault();
		this.curpage = 1;
		this.pagesize = 50;
		// $(this).prev('h3').text('Not sorted');
		Master.update_report('', this.pagesize, this.curpage, '', '');
	},

	update_report: function (th_sort = '', pagesize = '', curpage = '', pag_link = '', sort_direction = '') {

		var form_data = $('form.report_filter_form').serialize(),
			report = $('#report').val(),
			pagesize = $('.pagesize').val()
			;

		if (curpage == '') { curpage = $('.curpage').val(); }
		if (report == '') { report = $('#report').val(); }
		if (curpage != pag_link && pag_link != '') { curpage = pag_link; }
		if (th_sort == pag_link) { th_sort = ''; }

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		
		console.log(form_data);

		$.ajax({
			url: 'update_report',
			type: 'POST',
			dataType: 'json',
			data: {
				curpage: curpage,
				pagesize: pagesize,
				th_sort: th_sort,
				sort_direction: sort_direction,
				form_data: form_data,
				report: report
			},

			success: function (response) {

				console.log(response);

				// hide / empty everything and run report
				$('.table-responsive, .pag, .report_errors').empty();
				$('.report_download, .reset_sorting, .pag, .preloader, .report_errors').hide();

				// check for errors
				if (response.errors.length >= 1) {
					for (var i = 0; i < response.errors.length; i++) {
						$('.report_errors').show();
						$('.report_errors').append(response.errors[i] + '<br>');
					}

					return false;
				}

				// check for result by counting total page
				if (response.params.totrows) {

					this.totpages = response.params.totpages;
					this.curpage = response.params.curpage;
					this.th_sort = th_sort;
					this.sort_direction = response.params.orderby['Campaign'];

					// append table
					$('.table-responsive').append(response.table);

					// show download options
					$('.report_download').show();

					// set active class to the th that was sorted
					for (var i = 0; i < $('.reports_table thead th').length; i++) {
						if ($('.reports_table thead th:eq(' + i + ')').text() == this.th_sort) {
							$('.reports_table thead th:eq(' + i + ')').addClass('active_column');
							$('.reports_table thead th:eq(' + i + ')').find('span.' + sort_direction).addClass('active');
						}
					}

					// pagination - show pag if more than one page
					if (response.params.totpages > 1) {
						$('.pag').append(response.pag).show();
						$('.pagination').find('li').removeClass('active');
						$('.pagination li a[data-paglink="' + this.curpage + '"]').parent().addClass('active');
					}

					// show sort order and reset button if sorting is active
					if (this.th_sort) {
						$('.reset_sorting h3').html('Sorted in <span class="sort_direction">' + sort_direction + '</span> order by <span class="sorted_by">' + this.th_sort + '</span>');
						$('.reset_sorting').show();
					}
					// if no result	
				} else {
					$('.table-responsive').empty();
					$('.pag').empty();
					$('.report_download').hide();
					$('.reset_sorting').hide();
					$('.report_errors').append('No results found').show();
				}

				if (response.params.report == 'campaign_usage') {
					Master.campaign_usage(response);
				}

				if (response.params.report == 'campaign_call_log') {
					Master.campaign_call_log(response);
				}

				if (response.params.report == 'lead_inventory') {
					Master.lead_inventory(response);
				}
			}
		}); /// end ajax
	}, /// end update_report function

	campaign_usage: function (response) {

		$('.hidetilloaded').show();
		var chartColors = Master.chartColors;

		var xaxis_labels = [];
		for (var i = 0; i < response.extras['callable'].length; i++) {
			xaxis_labels.push(i);
		}

		// return false;
		var leads_by_attempt_data = {
			labels: xaxis_labels,
			datasets: [
				{
					label: "Callable",
					backgroundColor: chartColors.green,
					data: response.extras['callable']
				},
				{
					label: "NonCallable",
					backgroundColor: chartColors.orange,
					data: response.extras['noncallable']
				}
			]
		};

		var leads_by_attempt_options = {
			responsive: true,
			maintainAspectRatio: false,
			legend: {
				position: 'bottom',
				labels: {
					boxWidth: 12
				}
			},
			scales: {

				xAxes: [{
					stacked: true,
				}],
				yAxes: [{
					stacked: true,
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}

		var ctx = document.getElementById('leads_by_attempt').getContext('2d');

		if (window.leads_by_attempt_chart != undefined) {
			window.leads_by_attempt_chart.destroy();
		}

		window.leads_by_attempt_chart = new Chart(ctx, {
			type: 'bar',
			data: leads_by_attempt_data,
			options: leads_by_attempt_options
		});

		if (window.subcampaigns_chart != undefined) {
			window.subcampaigns_chart.destroy();
		}

		var response_length = response.extras['subcampaigns'].length;
		var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

		var subcampaigns = [];
		var subcampaigns_cnt = [];
		for (var i = 0; i < response.extras['subcampaigns'].length; i++) {
			subcampaigns_cnt.push(response.extras['subcampaigns'][i]['Cnt']);
			subcampaigns.push(response.extras['subcampaigns'][i]['Subcampaign']);
		}

		if (response_length) {
			var subcampaigns_data = {
				datasets: [{
					data: subcampaigns_cnt,
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
					text: 'CALLABLE LEADS BY SUBCAMPAIGN'
				},
				labels: subcampaigns
			};

			var subcampaigns_options = {
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
					text: 'CALLABLE LEADS BY SUBCAMPAIGN'
				},
			}

			var ctx = document.getElementById('subcampaigns').getContext('2d');

			window.subcampaigns_chart = new Chart(ctx, {
				type: 'doughnut',
				data: subcampaigns_data,
				options: subcampaigns_options
			});
		} else {
			$('<h2 class="card_title">CALLABLE LEADS BY SUBCAMPAIGN</h2><p class="no_data">No data yet</p>').insertBefore('#subcampaigns');
		}

		$('#subcampaigns').parent().find('.card_title').remove();
		$('#subcampaigns').parent().find('.no_data').remove();

		if (window.call_stats_chart != undefined) {
			window.call_stats_chart.destroy();
		}

		var response_length = response.extras['callstats'].length;
		var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

		var call_stats = [];
		var call_stats_cnt = [];
		for (var i = 0; i < response.extras['callstats'].length; i++) {
			call_stats_cnt.push(response.extras['callstats'][i]['Cnt']);
			call_stats.push(response.extras['callstats'][i]['CallStatus']);
		}

		if (response_length) {
			var call_stats_data = {
				datasets: [{
					data: call_stats_cnt,
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
					text: 'NON-CALLABLE LEADS BY DISPOSITION'
				},
				labels: call_stats
			};

			var call_stats_options = {
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
					text: 'NON-CALLABLE LEADS BY DISPOSITION'
				},
			}

			var ctx = document.getElementById('call_stats').getContext('2d');

			window.call_stats_chart = new Chart(ctx, {
				type: 'doughnut',
				data: call_stats_data,
				options: call_stats_options
			});
		}

		$('#call_stats').parent().find('.card_title').remove();
		$('#call_stats').parent().find('.no_data').remove();
	},

	campaign_call_log: function (response) {
		$('.rm_rptble_class').find('table').removeClass('reports_table');
		$('.rm_rptble_class table th').find('a').remove();
		$('.hidetilloaded').show();
		var chartColors = Master.chartColors;

		var xaxis_labels = [];
		for (var i = 0; i < response.extras['calldetails'].length; i++) {
			xaxis_labels.push(response.extras['calldetails'][i]['Time']);
		}

		var handled_calls = [];
		for (var i = 0; i < response.extras['calldetails'].length; i++) {
			handled_calls.push(response.extras['calldetails'][i]['HandledCalls']);
		}

		var total_calls = [];
		for (var i = 0; i < response.extras['calldetails'].length; i++) {
			total_calls.push(response.extras['calldetails'][i]['TotCalls']);
		}

		var call_volume_data = {

			labels: xaxis_labels,
			datasets: [{
				label: 'Handled Calls ',
				borderColor: chartColors.green,
				backgroundColor: 'rgba(51,160,155,0.6)',
				fill: true,
				data: handled_calls,
				yAxisID: 'y-axis-1'
			}, {
				label: 'Total Calls',
				borderColor: chartColors.orange,
				backgroundColor: chartColors.orange,
				fill: false,
				data: total_calls,
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
		var ctx = document.getElementById('call_volume').getContext('2d');
		if (window.call_volume_chart != undefined) {
			window.call_volume_chart.destroy();
		}

		window.call_volume_chart = new Chart(ctx, {
			type: 'line',
			data: call_volume_data,
			options: call_volume_options
		});

		/////////////////////////////////////////////////////////
		// agent vs system calls
		////////////////////////////////////////////////////////

		var chart_colors_array = Master.return_chart_colors(2, chartColors);
		var agent_sys_calls = [];
		agent_sys_calls.push(response.extras['donut']['AgentCalls']);
		agent_sys_calls.push(response.extras['donut']['SystemCalls']);

		var agent_system_calls_data = {
			datasets: [{
				data: agent_sys_calls,
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

			labels: ['Agent Calls', 'System Calls']
		};

		var agent_system_calls_options = {
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
				text: 'Agent vs System Calls'
			},
		}

		var ctx = document.getElementById('agent_system_calls').getContext('2d');

		if (window.agent_system_calls_chart != undefined) {
			window.agent_system_calls_chart.destroy();
		}

		window.agent_system_calls_chart = new Chart(ctx, {
			type: 'doughnut',
			data: agent_system_calls_data,
			options: agent_system_calls_options
		});

		/////////////////////////////////////////////////////////
		// call status count
		////////////////////////////////////////////////////////
		var callstatus = [];
		var callstatus_label = [];
		var response_length = response.extras['stats'].length
		var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

		for (var i = 0; i < response_length; i++) {
			callstatus.push(response.extras['stats'][i]['Count']);
			callstatus_label.push(response.extras['stats'][i]['CallStatus']);
		}

		var callstatus_data = {
			datasets: [{
				data: callstatus,
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

			labels: callstatus_label
		};

		var callstatus_options = {
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
				text: 'Call Status Count'
			},
		}

		var ctx = document.getElementById('callstatus').getContext('2d');

		if (window.callstatus_chart != undefined) {
			window.callstatus_chart.destroy();
		}

		window.callstatus_chart = new Chart(ctx, {
			type: 'doughnut',
			data: callstatus_data,
			options: callstatus_options
		});
	},

	lead_inventory: function (response) {
		$('.total_leads').html('<b>Available Leads: ' + response.extras['AvailableLeads'] + '</b>');
		$('.available_leads').html('<b>Total Leads: ' + response.extras['TotalLeads'] + '</b>');

	}
}

$(document).ready(function () {
	Master.init();

	$('.view_report_btn').on('click', function () {

		$('.alert').hide();
		var report = $('input.report_option:checked').val();

		// if(report != '' && report != undefined){
		// 	window.location ="reports.php?report="+report;
		// }else{
		// 	$('#reports_modal .modal-footer').append('<div class="alert alert-danger"><p>Please select a report</p></div>');
		// }

		// $.ajax({
		// 	url: '/master/reports/'+report,
		// 	type: 'POST',
		// 	dataType: 'json',
		// 	data: { 'report': report },
		// 	success: function (response) {
		// 		alert(report);
		// 		// console.log(report);
		// 		// return false;
		// 		if (!$('.sidebar').hasClass('active')) {
		// 			$('.sidebar').toggle();
		// 			$('.preloader').show();
		// 			window.location = "/reports/"+report;
		// 		} else {
		// 			window.location = "/reports/"+report;
		// 		}

		// 	}
		// });
	});

	$('#deleteRecipModal').on('show', function (e) {
		console.log($(this));
		var link = e.relatedTarget(),
			modal = $(this),
			username = link.data("username"),
			userid = link.data("userid");

		modal.find("#userid").val(userid);
		modal.find("#username").val(username);
	});

	$('#sidebarCollapse').on('click', function () {
	    $('#sidebar').toggleClass('active');
	});

});

$(window).load(function() {
	$('.preloader').fadeOut('slow');
});