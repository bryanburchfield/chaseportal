// var colorHash = new ColorHash();

var Master = {

	chartColors : {
        red: 'rgb(255,67,77)',
        orange: 'rgb(228,154,49)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(51,160,155)',
        blue: 'rgb(1,1,87)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(68,68,68)'
    },

	curpage:'',
	pagesize:'',
	pag_link:'',
	sort_direction:'',
	th_sort:'',
	totpages:'',
	pdf_dl_link:'',
	first_search:true,
	active_camp_search:'',
	dataTable:$('#dataTable').DataTable({
        responsive: true,
    }),
    cdr_dataTable: $('#cdr_dataTable').DataTable({
		responsive: true,
		dom: 'Bfrtip',
		        buttons: [
		                 'excelHtml5',
		                 'csvHtml5',
		                 'pdfHtml5'
		             ]
	}),

	init:function(){

		$('.pag').clone().insertAfter('div.table-responsive');
		$('.view_report_btn').on('click', this.view_report);
		$('.add_user').on('submit', this.add_user);
		$('.edit_user').on('submit', this.edit_user);
		$('.users').on('click', 'a.edit_user', this.populate_user_edit);
		$('#deleteUserModal .remove_recip').on('click', this.remove_user);
		$('.users table tbody').on('click', 'a.remove_user', this.pass_user_removemodal);
		$('.users table tbody').on('click', 'a.user_links', this.pass_user_linkmodal);
		$('form.report_filter_form').on('submit', this.submit_report_filter_form);
		$('.pag').on('click', '.pagination li a', this.click_pag_btn);
		$('body').on('click', '.reports_table thead th a span', this.sort_table);
		$('.pag').on('change', '.curpage, .pagesize', this.change_pag_inputs);
		$('.reset_sorting_btn').on('click', this.reset_table_sorting);
		$('#campaign_usage #campaign_select, #lead_inventory_sub #campaign_select').on('change', this.get_subcampaigns); 
		$('.report_download').on('click', '.report_dl_option.pdf', this.pdf_download_warning);
		$('#report_dl_warning .dl_report').on('click', this.pdf_download2);
		$('.query_dates_first .datetimepicker').on('change', this.query_dates_for_camps);
		$('#uploader_camp_info').on('submit', this.uploader_details);
		$('#settingsForm').on('submit', this.update_uploader_info);
		$('#file_upload').on('submit', this.upload_file);
		$('#import').on('submit', this.import);
		$('.card_dropbtn').on('click', this.toggle_dotmenu);
		$('.percentage').on('change', this.set_percentages);
		$('.campaign_search').on('keyup', this.search_campaigns);
		$('.select_database').on('click', this.select_database);
		$('.reports .switch input').on('click', this.toggle_automated_reports);
		$('.cdr_lookup_form').on('submit', this.cdr_lookup);
		$('a.getAppToken').on('click', this.copy_link);
		$('.select_campaign').on('click', this.filter_campaign);
		$('.date_filters li a').on('click', this.filter_date);
		$('.submit_date_filter').on('click', this.custom_date_filter);
        $('.filter_campaign').on('click', '.campaign_group', this.adjust_campaign_filters);
	},

    return_chart_colors_hash:function(reps){

        var chart_colors_array=[];
        var customHash = function(str) {
            var hash = 0;
            for(var i = 0; i < str.length; i++) {
                hash += str.charCodeAt(i);
            }

            return hash;
        };

        var colorHash = new ColorHash({hue: [ {min: 200, max: 255}, {min: 90, max: 205}, {min: 70, max: 150} ]});

        var new_hash;
        var new_rgb;
        for (var i=0;i<reps.length;i++) {
            new_hash=colorHash.rgb(reps[i]);
            new_rgb="rgb("+new_hash[0]+","+new_hash[1]+","+new_hash[2]+")";
            chart_colors_array.push(new_rgb);
        }

        return chart_colors_array;

        // const chart_colors = Object.keys(Dashboard.chartColors)
        // var chart_colors_array=[];
        
        // var j=0;
        // for (var i=0; i < reps; i++) {
        //     if(j==chart_colors.length){
        //         j=0;
        //     }
        //     chart_colors_array.push(eval('chartColors.'+chart_colors[j]));
        //     j++;
        // }

        // return chart_colors_array;
    },

	formatNumber:function(x) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	},

	convertSecsToHrsMinsSecs:function(secs) {
	    let sec_num = parseInt(secs, 10)
	    let hours   = Math.floor(sec_num / 3600)
	    let minutes = Math.floor(sec_num / 60) % 60
	    let seconds = sec_num % 60

	    return [hours,minutes,seconds]
	        .map(v => v < 10 ? "0" + v : v)
	        .filter((v,i) => v  || i > 0)
	        .join(":")
	},

	add_bg_rounded_class:function(selector, data, limit) {
        if(data == 0 || data.toString().length < limit){
            selector.addClass('bg_rounded');
        }else{
            selector.removeClass('bg_rounded');
        }
    },

    ylabel_format:function(data){
        var show_decimal=false;
        
        for(var i=0;i<data.length;i++){
            if(data[i] > 300){
                show_decimal=false;
                break;
            }else{
                show_decimal=true;
            }
        }

        return show_decimal;
    },

    flip_card:function(len, sel){
        if(len < 15){
            $(sel).closest('.flipping_card').flip(true);
        }else{
        	$(sel).closest('.flipping_card').flip(false);
        }
    },

    trend_percentage:function(selector, change_perc, up_or_down, not_comparable){
        // if there is data to compare
        if(!not_comparable){
            selector.find('.trend_indicator').show();
            selector.find('.trend_indicator span').text(change_perc+'%');
            selector.find('.trend_indicator').removeClass('up down');
            selector.find('.trend_arrow').removeClass('arrow_up arrow_down');

            // if percentage is up
            if(up_or_down){
                selector.find('.trend_indicator').addClass('up');
                selector.find('.trend_arrow').addClass('arrow_up');

            }else{ // if percentage is down
                selector.find('.trend_indicator').addClass('down');
                selector.find('.trend_arrow').addClass('arrow_down');
            }
        }else{
            selector.find('.trend_indicator').hide();
        }
    },

    filter_date:function(){
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
                url: 'dashboards/update_filters',
                type: 'POST',
                dataType: 'json',
                data: {dateFilter:datefilter},
                success:function(response){
                    Master.set_campaigns(response);
                }
            });          
        }
    },

    custom_date_filter:function(){
        $('.preloader').show();
        $('#datefilter_modal').hide();
        $('.modal-backdrop').hide();
        
        var start_date = $('.startdate').val(),
            end_date = $('.enddate').val()
        ;
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        datefilter = start_date + ' ' + end_date;

        $('.startdate').val('');
        $('.enddate').val('');
        $('#datefilter_modal').modal('toggle');
        $('#datefilter').val(start_date + ' ' + end_date);
        Dashboard.datefilter = datefilter;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'dashboards/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {dateFilter:datefilter},
            success:function(response){
                Master.set_campaigns(response);

            }
        });
    },

    // check/uncheck campaigns based on whats being clicked
    adjust_campaign_filters:function(){

        // Get amount of selected checkboxes
        var checked = [];
        $('.campaign_label input:checked').each(function() {
            checked.push($(this).attr('name'));
        });

        /// check if target is NOT All Camps
        if($(this).val() !=''){
            // See if others are checked
            if(checked.length){
                // check if All Camps is checked
                if($('.filter_campaign .campaign_group').eq(0).is(':checked')){
                    // uncheck all camps because others are being selected
                    $('.filter_campaign .campaign_group').eq(0).removeAttr('checked');
                }
            }            
        }else{ /// ALL camps is being checked
            // check if All Camps was already checked
            if($('.filter_campaign .campaign_group').eq(0).is(':checked')){
                $('.filter_campaign .campaign_group').removeAttr('checked'); /// uncheck all other camps
                $('.filter_campaign .campaign_group').eq(0).prop('checked',true); // recheck all camps
            }

            if(!checked.length){ // if nothing is selected reselect All Camps because something has to be checked
                $('.filter_campaign .campaign_group').eq(0).prop('checked',true);
            }
        }
    },

    // ran after submit is clicked in the interaction menu, after filter_campaign()
    set_campaigns:function(response){
        var campaigns=[];
        $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function() {
            campaigns.push($(this).val());
            //// if total is selected, uncheck all checkboxes
            if($(this).val()==''){
                $('.filter_campaign .checkbox label input[name="campaigns"]:checkbox').removeAttr('checked');
            }
        });

        var is_array = Array.isArray(response.campaigns);               
        var obj = response['campaigns'];
        $('.filter_campaign .checkbox').remove();
        var campaign_searchresults='';

        if(!is_array){                      
            var obj = Object.keys(obj).map(function(key) {
                return [obj[key]];
            });
        }
        var checked;
        
        for(var i=0;i<obj.length;i++){
            checked=obj[i].selected;
            if(checked){checked='checked';}else{checked='';}
            campaign_searchresults+='<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" '+checked+' value="'+obj[i].value+'" name="campaigns"><span>'+obj[i].name+'</span></label></div>';
        }

        $('.filter_campaign').append(campaign_searchresults);

        Dashboard.refresh(datefilter);
    },

    // ran when submit is clicked in the interaction menu
    filter_campaign:function(){

        $('.preloader').show();

        datefilter = $('#datefilter').val();
        var checked = $(".campaign_group:checkbox:checked").length;
        $('.alert').remove();
        $('.campaign_search').val('');

        if(checked){
            $('.filter_campaign').parent().removeClass('open');
            $('.filter_campaign').prev('.dropdown-toggle').attr('aria-expanded', false);
            var campaigns=[];
            $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function() {
                campaigns.push($(this).val());
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'dashboards/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {campaign: campaigns},
            success:function(response){
                Master.set_campaigns(response);
            }
        });
    },

    search_campaigns:function(){
    	var query = $(this).val();

    	if(Master.first_search){
    		if($('.filter_campaign li').hasClass('active')){
    			Master.active_camp_search = $('.filter_campaign li.active').text();
    		}
    	}
    	
    	$.ajaxSetup({
    	    headers: {
    	        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    	    }
    	});

		$.ajax({
			url: 'dashboards/campaign_search',
			type: 'POST',
			dataType: 'json',
			data: {query: query},
			success:function(response){
				
				var is_array = Array.isArray(response.search_result);				
				var obj = response['search_result'];
				$('.filter_campaign .checkbox').remove();
				var campaign_searchresults='';

				if(!is_array){    					
					var obj = Object.keys(obj).map(function(key) {
						return [obj[key]];
					});
				}

				var checked;
                    
                for(var i=0;i<obj.length;i++){
                    checked=obj[i].selected;
                    if(checked){checked='checked';}else{checked='';}
                    campaign_searchresults+='<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" '+checked+' value="'+obj[i].value+'" name="campaigns"><span>'+obj[i].name+'</span></label></div>';
                }

				Master.first_search=false;

				$('.filter_campaign').append(campaign_searchresults);
			}
		});    		
    },

    select_database:function(e){
        e.preventDefault();

    	var type = $('.page_type').val();
    	var checked = $(".database_group:checkbox:checked").length;
    	$('.alert').remove();
    	if(checked){
            $(this).parent().parent().removeClass('open');
    		$('.db_select .dropdown').removeClass('open');
    		$('.db_select .dropdown-toggle').attr('aria-expanded', false);
    		var databases=[];
    		$('input[name="databases"]:checked').each(function() {
    			databases.push($(this).val());
    		});

    		if(type != 'report'){
    			Master.set_databases(databases);
    		}else{

    			$.ajaxSetup({
    			    headers: {
    			        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    			    }
    			});

	    		$.ajax({
		            url: 'set_database',
		            type: 'POST',
		            dataType: 'json',
		            data: {databases:databases},
		            success:function(response){
		                $('.preloader').fadeOut('slow');
		            }
		        }); 
    		}
    		
    	}else{
            $(this).parent().parent().addClass('open');
            $('.db_select .dropdown-toggle').attr('aria-expanded', true);
    		$('.db_select').append('<div class="alert alert-danger">At least one database must be selected</div>');
    	}
    },

    set_databases:function(databases){
        Dashboard.databases=databases;
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        var datefilter = $('#datefilter').val();
        $('.preloader').show();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'dashboards/update_filters',
            type: 'POST',
            dataType: 'json',
            data: {databases:databases},
            success:function(response){
                Dashboard.refresh(datefilter);
            }
        });  
    },

    toggle_automated_reports:function(){
    	var active;
    	var report = $(this).parent().parent().parent().data('report');

    	if($(this).is(':checked')){
    		$(this).attr('Checked','Checked');
    		active=1;
    	}else{
    		$(this).removeAttr('Checked');
    		active=0;
    	}

    	$.ajaxSetup({
    	    headers: {
    	        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    	    }
    	});

    	$.ajax({
    		url: 'toggle_automated_report',
    		type:'POST',
    		data:{
    			active:active,
    			report:report
    		},
    		success:function(response){
    		}
    	});
    },

	upload_file:function(e){
		e.preventDefault();
		$('#file_upload').parent().find('.alert').remove();

		function hasExtension(inputID, exts) {
		    var fileName = document.getElementById(inputID).value;
		    return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
		}

		if (!hasExtension('file', ['.csv'])) {
			$('#file_upload').parent().append('<div class="alert alert-danger col-sm-4">Invalid File Format. Please use .csv.</div>');
			return false;
		}

		var form = document.getElementById('file_upload');
		var formData = new FormData(form);

		formData.append('file', file);
		  
		var xhr = new XMLHttpRequest();
		xhr.open('POST', form.getAttribute('action'), true);
		xhr.send(formData);
		xhr.onload = function () {
		    if (xhr.readyState === xhr.DONE) {
		    	
		        if (xhr.status === 200) {
		            var obj = JSON.parse(xhr.responseText);

		            if(obj.errors[0]){
		            	$('#file_upload').parent().append('<div class="alert alert-danger col-sm-4">Invalid CSV contents.</div>');
		            	return false;
		            }
		            $('.imported_data_field').val(xhr.responseText);
		            Master.initCSVTable(obj.data.contents);
		        }
		      }
		  };
		  return false; 
	},

	initCSVTable:function(_data){
        var data = [];
        var array_keys= [], array_values= [];

        for (i = 0; i < _data.length; i++){
            array_keys= [];
            array_values = [i+1];
            for (var key in _data[i]) {
                array_keys.push(key);
                array_values.push(_data[i][key]);
            }
            data.push(array_values);
        }
        Master.initTableHeader(array_keys);

        Master.dataTable.clear();
        Master.dataTable.rows.add(data);
        Master.dataTable.draw();
    },

    initSettingTable:function(_data){
        var i, html = "";
        html += "<tr>";
        html += "<td>BT07.chasedatacorp.com</td>";
        html += "<td>" + _data['Campaign_A'] + "</td>";
        html += "<td>" + _data['Subcampaign_A'] + "</td>";
        html += "<td>" + _data['Rate_A'] + "</td>";
        html += "</tr>";

        html += "<tr>";
        html += "<td>BT15.chasedatacorp.com</td>";
        html += "<td>" + _data['Campaign_B'] + "</td>";
        html += "<td>" + _data['Subcampaign_B'] + "</td>";
        html += "<td>" + _data['Rate_B'] + "</td>";
        html += "</tr>";

        $('#settingsTable tbody').html(html);
    },

    initTableHeader:function(arr){
        
    	Master.dataTable.clear();
        Master.dataTable.destroy();
        var html = "<tr><th>#</th>";
        for (var i = 0 ;  i < arr.length ; i++){
            html += "<th>" + arr[i] + "</th>";
        }
        $('#dataTable thead').html(html);

        Master.dataTable = $('#dataTable').DataTable({
            responsive: true,
        });
    },

	update_uploader_info:function(e){
		e.preventDefault();
		var form_data = $(this).serialize();
		$('#settingModal').modal('toggle');

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'uploader_action',
			type: 'POST',
			dataType: 'json',
			data: {
				form_data: form_data
			},
			success:function(response){
				Master.set_uploader_info(response);
			}
		});	
	},

	uploader_details:function(e){
		e.preventDefault();
		var form_data = $(this).serialize();

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'uploader_action',
			type: 'POST',
			dataType: 'json',
			data: {
				form_data: form_data
			},
			success:function(response){
				if(response.data.status == 'success'){
					$('.uploader_part1').hide();
					$('.uploader_part2').show();

					Master.set_uploader_info(response);
				}else{
					var errors;
					for (var i=0; i <response.errors; i++) {
						errors+='<p>'+response.errors[i]+'</p>';
					}
					$('.errors').append('<div class="alert alert-danger">'+errors+'</div>');
				}
			}
		});		
	},

	set_uploader_info:function(response){

		$('td.uploader_details').remove();
		var server1_dets = '<td class="uploader_details">'+response.data.Campaign_A+'</td><td class="uploader_details">'+response.data.Subcampaign_A+'</td><td class="uploader_details">'+response.data.Rate_A+'%</td>';
		var server2_dets = '<td class="uploader_details">'+response.data.Campaign_B+'</td><td class="uploader_details">'+response.data.Subcampaign_B+'</td><td class="uploader_details">'+response.data.Rate_B+'%</td>';
		$('#settingsTable .server1').append(server1_dets);
		$('#settingsTable .server2').append(server2_dets);

		$('input[name="Campaign_A"]').val(response.data.Campaign_A);
		$('input[name="Campaign_B"]').val(response.data.Campaign_B);
		$('input[name="Subcampaign_A"]').val(response.data.Subcampaign_A);
		$('input[name="Subcampaign_B"]').val(response.data.Subcampaign_B);
		$('input[name="Rate_A"]').val(response.data.Rate_A);
		$('input[name="Rate_B"]').val(response.data.Rate_B);
	},

	import:function(e){
		e.preventDefault();
		var contents = JSON.parse($('.imported_data_field').val());
		contents = contents.data.contents;
		var action = $('#import .input_action').val();

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'uploader_action',
			type: 'POST',
			dataType: 'json',
			data: {
				// contents:contents,
				action:action
			},
			success:function(response){
				$('div.uploader_msg').empty();
				if(response.data.status=='success'){
					$('div.uploader_msg').append('<div class="alert alert-success">Uploaded Successfully</div>');
				}else{
					$('div.uploader_msg').append('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
				}
			}
		});	
	},

	// populate campaign multi-select based on dates
	query_dates_for_camps:function(){
        console.log('asdf');
		var todate = $('.todate').val(),
			fromdate = $('.fromdate').val()
			report = $('form.report_filter_form').attr('id')
		;

		if(todate !='' && fromdate !=''){

			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			    }
			});
            console.log(report +' '+ todate +' '+ fromdate);
			$.ajax({
				url: 'get_campaigns',
				type: 'POST',
				dataType: 'json',
				async: false, /////////////////////// use async when rebuilding multi select menus
				data: {
					report:report,
					todate: todate,
					fromdate:fromdate
				},

				success:function(response){
                    console.log(response);

					$('#campaign_select').empty();
					var camps_select;
					for(var i=0; i<response.campaigns.length; i++){
						camps_select += '<option value="'+response.campaigns[i]+'">'+response.campaigns[i]+'</option>';
					}

					$('#campaign_select').append(camps_select);
					$("#campaign_select").multiselect('rebuild');
					$("#campaign_select").multiselect('refresh');
                    console.log(report);
					$('#'+ report+ ' #campaign_select')
						.multiselect({nonSelectedText: 'Select Campaign',})
						.multiselect('selectAll', false)
				    	.multiselect('updateButtonText');				
				}
			});			
		}
	},

	pdf_download_warning:function(e){
		e.preventDefault();
		var tot_rows = parseInt($('.totrows').val());
		$('.report_dl_warning .modal-footer button').show();

		if(tot_rows > 1000 && tot_rows < 2000){
			$('#report_dl_warning').modal('toggle');
			$('.dl_alert.alert').removeClass('alert-danger');
			$('.dl_alert.alert').addClass('alert-warning');
			$('.dl_alert.alert p').text('This is a large dataset. It may be faster to download multiple smaller reports.');
		}else if(tot_rows >= 2000){
			$('.dl_alert.alert').removeClass('alert-warning');
			$('.dl_alert.alert').addClass('alert-danger');
			$('.dl_alert.alert p').text('Report is too large to download. Please run smaller reports or choose a different format');
			$('.report_dl_warning .modal-footer button').hide();
			$('#report_dl_warning').modal('toggle');
		}else{
			pdf_dl_link=$('.report_dl_option.pdf').attr('href');
			window.open(pdf_dl_link, '_blank');
		}
	},

	pdf_download2:function(){
		pdf_dl_link=$('.report_dl_option.pdf').attr('href');
		window.open(pdf_dl_link);
		$('#report_dl_warning').modal('hide');
		$('.modal-backdrop').remove();	
	},

	return_chart_colors:function(response_length, chartColors){
	    const chart_colors = Object.keys(Master.chartColors)
	    var chart_colors_array=[];

	    var j=0;
	    for (var i=0; i < response_length; i++) {
	        if(j==chart_colors.length){
	            j=0;
	        }
	        chart_colors_array.push(eval('chartColors.'+chart_colors[j]));
	        j++;
	    }

	    return chart_colors_array;
	},

	get_subcampaigns:function(e){
		var campaign;

		$('#campaign_select option:selected').each(function() {
		    campaign = $(this).val();
		});

		if($('#subcampaign_select').length){
			e.preventDefault();
			var report = $('form.report_filter_form').attr('id');

			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			    }
			});

			$.ajax({
				url: 'get_subcampaigns',
				type: 'POST',
				dataType: 'json',
				data: {
					report:report,
					campaign: campaign,
				},

				success:function(response){
                    console.log(response);
					$('#subcampaign_select').empty();
					
					var subcampaigns='<option value""> </option>';
					for(var i=0; i<response.subcampaigns.length;i++){
						subcampaigns+='<option value="'+response.subcampaigns[i]+'">'+response.subcampaigns[i]+'</option>';
					}

					$('#subcampaign_select').append(subcampaigns);
				}
			});
		}
	},

	// add global user
	add_user:function(e){
		e.preventDefault();
		
		var group_id = $('.group_id').val(),
			name = $('.name').val(),
			email = $('.email').val(),
			tz = $('#tz').val(),
			// user_type = $('#user_type').val(),
			db = $('#db').val(),
			additional_dbs = $('#additional_dbs').val()
		;

		var dialer = db.slice(-2);

		$('form.add_user .alert').remove();

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'admin/add_user',
			type: 'POST',
			dataType: 'json',
			data: {
				group_id:group_id,
				name: name,
				email:email,
				tz:tz,
				db:db,
				additional_dbs:additional_dbs
			},

			success:function(response){

				if(response.errors){

					$('form.add_user').append('<div class="alert alert-danger">'+response.errors+'</div>');
					$('.alert-danger').show();
				}else{
					$('form.add_user').append('<div class="alert alert-success">User successfully added</div>');
					setTimeout(function(){ 
						$('.alert').remove();
						$('form.add_user').trigger("reset");
						window.location.href = "/dashboards/admin";
					}, 3500);
				}
			}
		});	
	},

	// edit global user
	edit_user:function(e){
		e.preventDefault();
		var form = $('form.edit_user');
		var group_id = form.find('.group_id').val(),
			user_id = form.find('#user_id').val(),
			name = form.find('.name').val(),
			email = form.find('.email').val(),
			tz = form.find('#tz').val(),
			db = form.find('#db').val(),
			additional_dbs = form.find('#additional_dbs').val()
		;

		$('form.edit_user .alert').remove();

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'admin/update_user',
			type: 'POST',
			dataType: 'json',
			data: {
				id:user_id,
				group_id:group_id,
				name: name,
				email:email,
				tz:tz,
				db:db,
				additional_dbs:additional_dbs
			},

			success:function(response){

				if(response.errors){
					$('form.edit_user').append('<div class="alert alert-danger">'+response.errors+'</div>');
					$('.alert-danger').show();
				}else{
					$('form.edit_user').append('<div class="alert alert-success">User successfully updated</div>');
					$('.alert-success').show();
					$('form.edit_user').trigger("reset");
					setTimeout(function(){
						window.location.href = "/dashboards/admin";
					}, 3500);
				}
			}
		});	
	},

	populate_user_edit:function(e){
		e.preventDefault();
		$('ul.nav-tabs a[href="#edit_user"]').tab('show');
		var user_id = $(this).attr('href');
		var dialer = $(this).data('dialer');

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});
		
		$.ajax({
			url: 'admin/get_user',
			type: 'POST',
			dataType: 'json',
			data: {id: user_id},
			success:function(response){
				$('html,body').scrollTop($('body').scrollTop());

				$('#edit_dialer'+dialer).addClass('in');
				$('#edit_dialer'+dialer).attr('aria-expanded', true);
				$('#edit_heading'+dialer+' h4 a').attr('aria-expanded', true);
				var form = $('form.edit_user');
				form.find('.group_id').val(response.group_id);
				form.find('.name').val(response.name);
				form.find('.email').val(response.email);
				form.find('#tz').val(response.tz);
				form.find('#user_type').val(response.user_type);
				form.find('#db').val(response.db);
				form.find('#additional_dbs').val(response.additional_dbs);
				form.find('#user_id').val(response.id);
			}
		});
	},

	pass_user_removemodal:function(){
		
		var id = $(this).data('user');
		var name = $(this).data('name');

		$('#deleteUserModal .user_id').val(id);
		$('#deleteUserModal .name').val(name);
		$('#deleteUserModal .username').html(name);
	},

	pass_user_linkmodal:function(){
		var id = $(this).data('user'),
			name = $(this).data('name'),
			app_token = $(this).data('token')
		;

		$('#userLinksModal .user_id').val(id);
		$('#userLinksModal .name').val(name);
		$('#userLinksModal .app_token').val(app_token);
		$('a.getAppToken span.url_token').text(app_token);
		$('#userLinksModal .username').html(id+' '+name);
	},

	// remove global user
	remove_user:function(e){
		e.preventDefault();
		var id = $('#deleteUserModal .user_id').val();

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'admin/delete_user',
			type:'POST',
			dataType:'json',
			data:{
				id:id
			},
			success:function(response){
				
				$('.users table tbody tr#user'+id).remove();
				$('#deleteUserModal').modal('toggle');

			}
		});
	},

	copy_link:function(e){
		e.preventDefault();

		$(this).tooltip({
		    animated: 'fade',
		    placement: 'left',
		    trigger: 'click'
		});

		setTimeout(function () {
            $('.tooltip').fadeOut('slow');
        }, 3500);

		var $temp = $("<input>");
	    $("#userLinksModal .modal-body").append($temp);
	    $temp.val($(this).text()).select();
	    document.execCommand("copy");
	    $temp.remove();	    
	},

	// select report from modal
	view_report:function(){
		$('.alert').hide();
		var selected_report = $('input.report_option:checked'). val();

		if(selected_report != '' && selected_report != undefined){
			window.location ="reports.php?report="+selected_report;
		}else{
			$('#reports_modal .modal-footer').append('<div class="alert alert-danger"><p>Please select a report</p></div>');
		}
	},

	// filter form submission
	submit_report_filter_form:function(e){
		e.preventDefault();
		$('.preloader').show();

		$([document.documentElement, document.body]).animate({
	        scrollTop: $(".table-responsive").offset().top -100
	    }, 1500);

	    Master.update_report('', '', 1, '', '');
	},

	// click a pagination button
	click_pag_btn:function(e){
		e.preventDefault();

		if(!$(this).parent().hasClass('disabled')){
			this.curpage = $('.curpage').val();
			this.pagesize = $('.pagesize').val();
			this.pag_link = $(this).data('paglink');
			this.sort_direction=$('.sort_direction').text();
			this.th_sort = $('.sorted_by').text();
			Master.update_report(this.th_sort, this.pagesize, this.curpage, this.pag_link, this.sort_direction);
		}
	},

	// sort by clicking th
	sort_table:function(e){
		e.preventDefault();
		$('.preloader').show();

		var sortedby_parent = $(this).parent().parent();
		this.th_sort = $(sortedby_parent).text();
		$(sortedby_parent).siblings().find('a span').show();
		$(sortedby_parent).siblings().find('a span').removeClass('active');
		$(sortedby_parent).siblings().removeClass('active_column');	
		$(sortedby_parent).addClass('active_column');
		$(this).siblings().hide();
		this.curpage=1
		this.pagesize=50;
		this.sort_direction = $(this).attr('class');		

		if($(this).hasClass('active')){
			$(this).siblings().show();
			$(this).removeClass('active');
			$(this).siblings().addClass('active');
			$(this).hide();
			this.sort_direction = $(this).siblings().attr('class').split(' ')[0];
		}else{
			$(this).addClass('active');
		}
		
		Master.update_report(this.th_sort, this.pagesize, this.curpage,'',this.sort_direction);
	},

	// check if pag input values have changed
	change_pag_inputs:function(){
		var max_pages = parseInt($('.curpage').attr('max')),
			totrows = parseInt($('.totrows').val()),
			pagesize = parseInt($('.pagesize').data('prevval')),
			new_pagesize = parseInt($('.pagesize').val())
		;

		this.curpage = parseInt($('.curpage').val());
		this.sort_direction=$('.sort_direction').text();
		this.th_sort = $('.sorted_by').text();

		// check if page input is greater than max available pages
		if(parseInt($(this).val()) > max_pages && $(this).hasClass('curpage')){			
			var prevval = $(this).data('prevval');
			this.curpage = prevval;
			$('div.errors').text('Attempted page number greater than available pages').show(0).delay(4500).hide(0);
			return false;
    	}else{
    		if($(this).hasClass('curpage')){
    			this.curpage = $(this).val();
    		}

    		// if users changes pagesize set curpage back to 1
    		if(pagesize != new_pagesize){
    			this.curpage=1;
    		}

    		if($(this).hasClass('pagesize')){
    			this.pagesize = $(this).val();
    			$('.pagesize').val(this.pagesize);
    		}
    		
    		Master.update_report(this.th_sort, this.pagesize, this.curpage, '', this.sort_direction);
    	}
	},

	// reset table sorting
	reset_table_sorting:function(e){
		e.preventDefault();
		this.curpage = 1;
		this.pagesize = 50;
		// $(this).prev('h3').text('Not sorted');
		Master.update_report('', this.pagesize, this.curpage, '', '');
	},

	update_report: function(th_sort='', pagesize='', curpage='', pag_link='', sort_direction=''){

		var form_data = $('form.report_filter_form').serialize(),
			report = $('#report').val(),
			pagesize = $('.pagesize').val()
		;

		if(curpage == ''){curpage = $('.curpage').val();}
		if(report == ''){report = $('#report').val();}
		if(curpage != pag_link && pag_link != ''){curpage = pag_link;}
		if(th_sort == pag_link){th_sort='';}
		
		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'update_report',
			type: 'POST',
			dataType: 'json',
			data: {
				curpage: curpage,
				pagesize:pagesize,
				th_sort:th_sort,
				sort_direction:sort_direction,
				form_data:form_data,
				report:report
			},

			success:function(response){

				if($('#sidebar').hasClass('active')){
					$('#sidebar').removeClass('active');
				}
				$('.table-responsive.report_table').show();
				// hide / empty everything and run report
				$('.table-responsive, .pag, .report_errors').empty();
				$('.report_download, .reset_sorting, .pag, .preloader, .report_errors').hide();

				// check for errors
				if(response.errors.length >=1){
					for (var i = 0; i< response.errors.length; i++) {
						$('.report_errors').show();
						$('.report_errors').append(response.errors[i]+'<br>');
					}

					return false;
				}

				// check for result by counting total page
				if(response.params.totrows){

					this.totpages = response.params.totpages;
					this.curpage = response.params.curpage;
					this.th_sort = th_sort;
					this.sort_direction = response.params.orderby.Campaign;

					// append table
					$('.table-responsive').append(response.table);

					// show download options
					$('.report_download').show();

					// set active class to the th that was sorted
					for(var i=0; i< $('.reports_table thead th').length; i++){
						if($('.reports_table thead th:eq('+i+')').text() == this.th_sort){
							$('.reports_table thead th:eq('+i+')').addClass('active_column');
							$('.reports_table thead th:eq('+i+')').find('span.'+sort_direction).addClass('active');
						}
					}

					// pagination - show pag if more than one page
					if(response.params.totpages > 1){
						$('.pag').append(response.pag).show();
						$('.pagination').find('li').removeClass('active');
						$('.pagination li a[data-paglink="' + this.curpage +'"]').parent().addClass('active');
					}

					// show sort order and reset button if sorting is active
					if(this.th_sort){
						$('.reset_sorting h3').html('Sorted in <span class="sort_direction">'+sort_direction+'</span> order by <span class="sorted_by">' + this.th_sort+'</span>');
						$('.reset_sorting').show();
					}
				// if no result	
				}else{
					$('.table-responsive').empty();
					$('.pag').empty();
					$('.report_download').hide();
					$('.reset_sorting').hide();
					$('.report_errors').append('No results found').show();
				}

				if(response.params.report == 'campaign_usage'){
					Master.campaign_usage(response);
				}

				if(response.params.report == 'campaign_call_log'){
					Master.campaign_call_log(response);
				}

				if(response.params.report == 'lead_inventory'){
					Master.lead_inventory(response);
				}		


			}
		}); /// end ajax
	}, /// end update_report function

	cdr_lookup:function(e){
		e.preventDefault();
		$('.preloader').show();
		var phone = $('#phone').val(),
			fromdate = $('.fromdate').val(),
			todate = $('.todate').val(),
			search_type = $("input[name='search_type']:checked").val()
		;

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
			url: 'admin/cdr_lookup',
			type: 'POST',
			dataType: 'json',
			data: {
				phone: phone,
				fromdate:fromdate,
				todate:todate,
				search_type:search_type
			},
			success:function(response){

				$('.report_filters.card').parent().find('.alert').remove();
				$('.cdr_results_table tbody').empty();

				if($('#sidebar').hasClass('active')){
					$('#sidebar').removeClass('active');
				}

				if(response.search_result.length){

					$('.cdr_table').show();

					var _data =response.search_result;
					var trs = [];
					var array_keys= [], array_values= [];
					for (i = 0; i < _data.length; i++){
					    array_keys= [];
					    array_values = [];
					    for (var key in _data[i]) {
					        array_keys.push(key);
					        array_values.push(_data[i][key]);
					    }
					    trs.push(array_values);
					}

					var ths = "";
					for (var i = 0 ;  i < array_keys.length ; i++){
					    ths += "<th>" + array_keys[i] + "</th>";
					}
					$('#cdr_dataTable thead').html(ths);
					Master.cdr_dataTable.clear();
					Master.cdr_dataTable.rows.add(trs);
					Master.cdr_dataTable.draw();

				}else{
					$('.cdr_table').hide();
					$('<div class="alert alert-danger">No records found</div>').insertAfter('.report_filters.card')
				}
				
				$('.preloader').fadeOut('slow');
			}
		})		
	},

	campaign_usage:function(response){
		
		$('.hidetilloaded').show();
		var chartColors = Master.chartColors;
		
		var xaxis_labels=[];
		for(var i=0; i<response.extras.callable.length;i++){
			xaxis_labels.push(i);	
		}

		// return false;
		var leads_by_attempt_data = {
		  labels: xaxis_labels,
		        datasets: [
		          {
		            label: "Callable",
		            backgroundColor: chartColors.green,
		            data: response.extras.callable
		          },
		          {
		            label: "NonCallable",
		            backgroundColor: chartColors.orange,
		            data: response.extras.noncallable
		          }
		        ]
		};

		var leads_by_attempt_options={
		    responsive: true,
		    maintainAspectRatio:false,
		    legend: {  
		        position: 'bottom',
		        labels: {
		            boxWidth: 12
		        } },
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

		if(window.leads_by_attempt_chart != undefined){
		  window.leads_by_attempt_chart.destroy();
		}

		window.leads_by_attempt_chart = new Chart(ctx, {
		    type: 'bar',
		    data: leads_by_attempt_data,
		    options: leads_by_attempt_options
		});

		if(window.subcampaigns_chart != undefined){
		    window.subcampaigns_chart.destroy();
		}

		var response_length = response.extras.subcampaigns.length;
		var chart_colors_array= Master.return_chart_colors(response_length, chartColors);
		
		var subcampaigns=[];
		var subcampaigns_cnt=[];
		for(var i=0; i<response.extras.subcampaigns.length;i++){
			subcampaigns_cnt.push(response.extras.subcampaigns[i].Cnt);
			subcampaigns.push(response.extras.subcampaigns[i].Subcampaign);
		}

		$('#subcampaigns').parent().find('.card_title').remove();
		$('#subcampaigns').parent().find('.no_data').remove();

		if(response_length){
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
		            fontColor:'#203047',
		            fontSize:16,
		            display: true,
		            text: 'CALLABLE LEADS BY SUBCAMPAIGN'
		        },
		        labels: subcampaigns
		    };

		    var subcampaigns_options={
		        responsive: true,
		        legend: {
		        display: false
		        },
		        tooltips: {
		            enabled:true,
		        },title: {
		            fontColor:'#203047',
		            fontSize:16,
		            display: true,
		            text: 'CALLABLE LEADS BY SUBCAMPAIGN'
		        },
		    }

		    var ctx = document.getElementById('subcampaigns').getContext('2d');

		    window.subcampaigns_chart = new Chart(ctx,{
		        type: 'doughnut',
		        data: subcampaigns_data,
		        options: subcampaigns_options
		    });
		}else{
            $('#subcampaigns').empty();                    
            $('<p class="no_data">No data yet</p>').insertBefore('#subcampaigns');
        }

		if(window.call_stats_chart != undefined){
		    window.call_stats_chart.destroy();
		}

		var response_length = response.extras.callstats.length;
		var chart_colors_array= Master.return_chart_colors(response_length, chartColors);

		var call_stats=[];
		var call_stats_cnt=[];
		for(var i=0; i<response.extras.callstats.length;i++){
			call_stats_cnt.push(response.extras.callstats[i].Cnt);
			call_stats.push(response.extras.callstats[i].CallStatus);
		}

		$('#call_stats').parent().find('.card_title').remove();
		$('#call_stats').parent().find('.no_data').remove();

		if(response_length){
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
		            fontColor:'#203047',
		            fontSize:16,
		            display: true,
		            text: 'NON-CALLABLE LEADS BY DISPOSITION'
		        },
		        labels: call_stats
		    };

		    var call_stats_options={
		        responsive: true,
		        legend: {
		        display: false
		        },
		        tooltips: {
		            enabled:true,
		        },title: {
		            fontColor:'#203047',
		            fontSize:16,
		            display: true,
		            text: 'NON-CALLABLE LEADS BY DISPOSITION'
		        },
		    }

		    var ctx = document.getElementById('call_stats').getContext('2d');

		    window.call_stats_chart = new Chart(ctx,{
		        type: 'doughnut',
		        data: call_stats_data,
		        options: call_stats_options
		    });
		}else{
            $('#call_stats').empty();                    
            $('<p class="no_data">No data yet</p>').insertBefore('#call_stats');
        }
		
	},

	campaign_call_log:function(response){
		$('.rm_rptble_class').find('table').removeClass('reports_table');
		$('.rm_rptble_class table th').find('a').remove();
		$('.hidetilloaded').show();
		var chartColors = Master.chartColors;

		var xaxis_labels=[];
		for(var i=0; i<response.extras.calldetails.length;i++){
			xaxis_labels.push(response.extras.calldetails[i].Time);	
		}

	    var handled_calls=[];
	    for(var i=0; i<response.extras.calldetails.length;i++){
			handled_calls.push(response.extras.calldetails[i].HandledCalls);	
		}

		var total_calls=[];
	    for(var i=0; i<response.extras.calldetails.length;i++){
			total_calls.push(response.extras.calldetails[i].TotCalls);	
		}

	    var call_volume_data = {

	        labels: xaxis_labels,
	        datasets: [{
	            label: 'Handled Calls ',
	            borderColor: chartColors.green,
	            backgroundColor: 'rgba(51,160,155,0.6)',
	            fill: true,
	            data:handled_calls,
	            yAxisID: 'y-axis-1'
	        },{
	            label: 'Total Calls',
	            borderColor: chartColors.orange,
	            backgroundColor: chartColors.orange,
	            fill: false,
	            data: total_calls,
	            yAxisID: 'y-axis-1'
	        }]
	    };

	    var call_volume_options={
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
	    if(window.call_volume_chart != undefined){
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

		var chart_colors_array= Master.return_chart_colors(2, chartColors);
		var agent_sys_calls=[];
    	agent_sys_calls.push(response.extras.donut.AgentCalls);
		agent_sys_calls.push(response.extras.donut.SystemCalls);

		var agent_system_calls_data = {
		    datasets: [{
		        data:agent_sys_calls,
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

		var agent_system_calls_options={
		    responsive: true,
		    legend: {
		    display: false
		    },
		    tooltips: {
		        enabled:true,
		    },title: {
		        fontColor:'#203047',
		        fontSize:16,
		        display: true,
		        text: 'Agent vs System Calls'
		    },
		}

		var ctx = document.getElementById('agent_system_calls').getContext('2d');

		if(window.agent_system_calls_chart != undefined){
            window.agent_system_calls_chart.destroy();
        }

		window.agent_system_calls_chart = new Chart(ctx,{
		    type: 'doughnut',
		    data: agent_system_calls_data,
		    options: agent_system_calls_options
		});

		/////////////////////////////////////////////////////////
		// call status count
		////////////////////////////////////////////////////////
		var callstatus=[];
		var callstatus_label=[];		
		var response_length = response.extras.stats.length
		var chart_colors_array= Master.return_chart_colors(response_length, chartColors);

		for(var i=0;i<response_length;i++){
			callstatus.push(response.extras.stats[i].Count);
			callstatus_label.push(response.extras.stats[i].CallStatus);
		}

		var callstatus_data = {
		    datasets: [{
		        data:callstatus,
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

		var callstatus_options={
		    responsive: true,
		    legend: {
		    display: false
		    },
		    tooltips: {
		        enabled:true,
		    },title: {
		        fontColor:'#203047',
		        fontSize:16,
		        display: true,
		        text: 'Call Status Count'
		    },
		}

		var ctx = document.getElementById('callstatus').getContext('2d');

		if(window.callstatus_chart != undefined){
            window.callstatus_chart.destroy();
        }

		window.callstatus_chart = new Chart(ctx,{
		    type: 'doughnut',
		    data: callstatus_data,
		    options: callstatus_options
		});
	},

	lead_inventory:function(response){
		$('.total_leads').html('<b>Available Leads: '+response.extras.AvailableLeads+'</b>');
		$('.available_leads').html('<b>Total Leads: '+response.extras.TotalLeads+'</b>');
	},

	toggle_dotmenu:function(){
		$("#card_dropdown").toggle();
	},

	set_percentages:function(){
		var val, name = $(this).attr('name');
		val = $(this).val();

		if (name === 'Rate_A'){
		    $('input[name=Rate_B]').val( 100 - val );
		}else{
		    $('input[name=Rate_A]').val( 100 - val );
		} 
	},

	/// keep alive and refresh data
	check_reload:function(){
	    
	    if (typeof Dashboard !== 'undefined') {	        

	        $(document.body).on('mousemove keypress', function(e){
	            Dashboard.time = new Date().getTime();
	        });
	        // reload if idle 60 seconds
	        function reload(){
	            if(new Date().getTime() - Dashboard.time >= 60000){
	                Dashboard.refresh(Dashboard.datefilter, Dashboard.campaign);
	                Dashboard.time = new Date().getTime();
	            }else{
	                setTimeout(reload, 5000);
	            }
	        }
	        setTimeout(reload, 5000);
	    }
	}
}

$(document).ready(function(){
	Master.init();

	$('.stop-propagation').on('click', function (e) {
	    e.stopPropagation();
	});

	$('.filter_campaign').on('click', '.stop-propagation', function (e) {
	    e.stopPropagation();
	});

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.matches('.card_dropbtn')) {
        	$('.card_dropdown-content').hide();
        }
    }

    // $('.front .card_table').each(function(){
    // 	var title = $(this).find('h1.title').text();
    // 	$(this).parent().next('.back').prepend('<h1 class="title">'+title+'</h1>');
    // });

	var hash = window.location.hash;
	hash && $('ul.nav-tabs a[href="' + hash + '"]').tab('show');

	$('.nav-tabs a').click(function (e) {
	    $(this).tab('show');
	    window.location.hash = this.hash;
		$('html,body').scrollTop($('body').scrollTop());
	});

	

});