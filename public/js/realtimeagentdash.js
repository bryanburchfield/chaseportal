var RealTime = {
	init: function (result) {
		
		var incoming_arrays = Object.entries(result[0][1]);

		// if first iteration
		if (!ran) {
			// keep track of all the in-memory lists
			status_arrays = [];

			// keep track of all the running timers
			timers = [];

			// load each returned array
			for (var i = 0; i < incoming_arrays.length; i++) {
				let status_type = incoming_arrays[i][0];
				status_arrays[status_type] = this.load_array(status_type, incoming_arrays[i][1]);
				this.update_agent_count(status_type, status_arrays[status_type]);
			}
		} else {
			// process each returned list
			for (var i = 0; i < incoming_arrays.length; i++) {
				let status_type = incoming_arrays[i][0];
				status_arrays[status_type] = this.process_array(status_type, status_arrays[status_type], incoming_arrays[i][1]);
				this.update_agent_count(status_type, status_arrays[status_type]);
			}
		}

		$('#total_calls_que').find('h4').html(result[1][1] !== null ? result[1][1] : '0');
		$('#total_calls').find('h4').html(result[2][1] !== null ? result[2][1] : '0');
		$('#longest_hold_time').find('h4').html(result[3][1] != null ? Master.convertSecsToHrsMinsSecs(result[3][1]) : '00:00:00');
		$('#total_sales').find('h4').html(result[4][1] !== null ? result[4][1] : '0');
		ran = true;
	},

	load_array(status_type, result_data) {
		var return_array = [];

		this.delete_all_rows(status_type);

		for (var i = 0; i < result_data.length; i++) {
			return_array.push({ 'Login': result_data[i]['Login'], 'checksum': result_data[i]['checksum'] });
			this.add_row(status_type, result_data[i]);
		}

		return return_array;
	},

	delete_all_rows(status_type) {
		// stop all the timers in that list
		if (status_arrays[status_type] != null) {
			for (var i = 0; i < status_arrays[status_type].length; i++) {
				this.stop_timer(status_type, status_arrays[status_type][i].Login);
			}
		}

		// delete all <li>s
		$('#' + status_type).empty();
	},

	add_row(status_type, data) {
		// add new <li> to bottom of list
		$('#' + status_type).append(this.build_li(status_type, data));

		// Start a timer
		timers[status_type + data.Login] = this.start_timer(status_type, data.Login, data.SecondsInStatus);
	},

	update_row(status_type, data) {
		// update <li>
		$('#' + login_id(status_type, data.Login)).replaceWith(this.build_li(status_type, data));

		// Kill the running timer, start a new one
		this.stop_timer(status_type, data.Login);
		timers[status_type + data.Login] = this.start_timer(status_type, data.Login, data.SecondsInStatus);
	},

	delete_row(status_type, login) {
		// stop timer
		this.stop_timer(status_type, login);

		// Delete <li>
		$('#' + login_id(status_type, login)).remove();
	},

	process_array(status_type, array_data, result_data) {
		// first, check that there's anything in the returned list
		if (result_data.length == 0) {
			if (array_data.length > 0) {
				this.delete_all_rows(status_type);
				array_data = [];
			}
		} else {
			// loop thru in-memory list and update or delete as necc
			// have to do deletes after the loop or things get crazy
			let delete_list = [];
			for (var i = 0; i < array_data.length; i++) {
				// look for login in results
				let row_obj = result_data.find(row => row.Login === array_data[i].Login);
				if (row_obj) {
					if (row_obj.checksum != array_data[i].checksum) {
						array_data[i].checksum = row_obj.checksum;
						this.update_row(status_type, row_obj);
					}
				} else {
					delete_list.push(array_data[i].Login);
				}
			}

			// now delete the ones that need it
			for (var i = 0; i < delete_list.length; i++) {
				array_data = array_data.filter(row => row.Login != delete_list[i]);
				this.delete_row(status_type, delete_list[i]);
			}

			// now go thru the returned list and insert if necc
			for (var i = 0; i < result_data.length; i++) {
				if (!array_data.find(row => row.Login === result_data[i]['Login'])) {
					array_data.push({ 'Login': result_data[i]['Login'], 'checksum': result_data[i]['checksum'] });
					this.add_row(status_type, result_data[i]);
				}
			}
		}

		return array_data;
	},

	start_timer(status_type, login, numSecs) {
		// convert to int
		numSecs = parseInt(numSecs);

		// this runs once per second
		return setInterval(function () {

			// Format and output the result
			$('#' + login_id(status_type, login) + 'Timer').text(Master.convertSecsToHrsMinsSecs(numSecs));

			// tick the timer
			numSecs = numSecs + 1;

		}, 1000);
	},

	stop_timer(status_type, login) {
		if (timers[status_type + login] != null) {
			clearInterval(timers[status_type + login]);
		}
	},

	build_li(status_type, data) {
		let call_icon = '';
		let has_icon = '';
		if (data.StatusCode == 5) {
			call_icon = '<i class="fa fa-sign-in-alt"></i>';
			has_icon = 'has_icon';
		} else if (data.StatusCode == 3 || data.StatusCode == 4) {
			call_icon = '<i class="fa fa-sign-out-alt"></i>';
			has_icon = 'has_icon';
		}

		return '<li id="' + login_id(status_type, data.Login) + '" class="list-group-item ' + (status_type == "talking" || status_type == "wrapping" ? 'getleaddetails' : '') + '" ' + (status_type == "talking" || status_type == "wrapping" ? "data-toggle=modal data-target=#leadInspectionModal data-phone='"+data.Phone+"' data-leadid='"+data.LeadId+"' " : "") + '> ' +
			'<span class="call_type">' +
			call_icon +
			'</span>' +
			'<div class="agent_call_details ' + has_icon + '"><p class="rep_name mb0">' + data.Login + '<span id="' + login_id(status_type, data.Login) + 'Timer"class="timer">' + data.TimeInStatus + '</span></p>' +
			'<p class="campaign">' + data.Campaign + '</p>' +
			'<p class="break_code">' + data.BreakCode + '</p></div>' +
			'</li>';
	},

	update_agent_count(status_type, status_array) {
		let count = status_array.length;
		$('.' + status_type).find('.num_agents .inner').text(count);
	},

	lead_dets_modal:function(){

		var leadid = $(this).data('leadid');
		var extra_fields='';
		var extra_fields_string='';

		$('#leadInspectionModal').find('.modal-body').empty();
		$('.lead_dets_leadid ').find('span').text(leadid);
		$('.lead_dets_phone').find('span').text($(this).data('phone'));

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$.ajax({
		    url: '/dashboards/get_lead_info/'+leadid,
		    type: 'GET',
		    dataType: 'json',
		    data:{
		        leadid:leadid,
		    },
		    success:function(response){
				
				extra_fields= Object.entries(response.ExtraFields);

				for(var i=0;i<extra_fields.length;i++){
					extra_fields_string+='<p class="lead_dets_phone fz15 mb5"><b>'+extra_fields[i][0]+':</b> <span>'+extra_fields[i][1]+'</span></p>'
				}

				var lead_info =
					'<div class="bb">'+
					(response.FirstName != null &&  response.FirstName.length && response.LastName != null &&  response.LastName.length ? '<p class="lead_dets_phone fz15 mb5"><b>Full Name:</b> <span>'+response.FirstName+' '+ response.LastName+'</span></p>' : " ") +
					(response.Address != null &&  response.Address.length ? '<p class="lead_dets_phone fz15 mb5"><b>Address:</b> <span>'+response.Address+'</span></p>' : " ") +
					(response.City != null &&  response.City.length ? '<p class="lead_dets_phone fz15 mb5"><b>City:</b> <span>'+response.City+'</span></p>' : " " )+
					(response.State != null &&  response.State.length ? '<p class="lead_dets_phone fz15 mb5"><b>State:</b> <span>'+response.State+'</span></p>' : " " )+
					(response.ZipCode != null &&  response.ZipCode.length ? '<p class="lead_dets_phone fz15 mb5"><b>Zip Code:</b> <span>'+response.ZipCode+'</span></p>' : " " )+
					(response.PrimaryPhone != null &&  response.PrimaryPhone.length ? '<p class="lead_dets_phone fz15 mb5"><b>Primary Phone:</b> <span>'+response.PrimaryPhone+'</span></p>' : " " )+
					(response.SecondaryPhone != null &&  response.SecondaryPhone.length ? '<p class="lead_dets_phone fz15 mb5"><b>Secondary Phone:</b> <span>'+response.SecondaryPhone+'</span></p>' : " " )+
					'</div>'+
					'<br>'+
					(response.Attempt != null &&  response.Attempt.length ? '<p class="lead_dets_phone fz15 mb5"><b>Attempt:</b> <span>'+response.Attempt+'</span></p>' : " " )+
					(response.CallType != null &&  response.CallType.length ? '<p class="lead_dets_phone fz15 mb5"><b>Call Type:</b> <span>'+response.CallType+'</span></p>' : " " )+
					(response.Campaign != null &&  response.Campaign.length ? '<p class="lead_dets_phone fz15 mb5"><b>Campaign:</b> <span>'+response.Campaign+'</span></p>' : " " )+
					(response.Subcampaign != null &&  response.Subcampaign.length ? '<p class="lead_dets_phone fz15 mb5"><b>Subcampaign:</b> <span>'+response.Subcampaign+'</span></p>' : " " )+
					(response.ClientId != null &&  response.ClientId.length ? '<p class="lead_dets_phone fz15 mb5"><b>Client Id:</b> <span>'+response.ClientId+'</span></p>' : " " )+
					(response.Date != null &&  response.Date.length ? '<p class="lead_dets_phone fz15 mb5"><b>Date:</b> <span>'+response.Date+'</span></p>' : " " )+
					(response.DispositionId != null &&  response.DispositionId.length ? '<p class="lead_dets_phone fz15 mb5"><b>Disposition Id:</b> <span>'+response.DispositionId+'</span></p>' : " " )+
					(response.LastUpdated != null &&  response.LastUpdated.length ? '<p class="lead_dets_phone fz15 mb5"><b>Last Updated:</b> <span>'+response.LastUpdated+'</span></p>' : " " )+
					(response.ReloadAttempt != null &&  response.ReloadAttempt.length ? '<p class="lead_dets_phone fz15 mb5"><b>Reload Attempt:</b> <span>'+response.ReloadAttempt+'</span></p>' : " " )+
					(response.ReloadDate != null &&  response.ReloadDate.length ? '<p class="lead_dets_phone fz15 mb5"><b>Reload Date:</b> <span>'+response.ReloadDate+'</span></p>' : " " )+
					(response.Rep != null &&  response.Rep.length ? '<p class="lead_dets_phone fz15 mb5"><b>Rep:</b> <span>'+response.Rep+'</span></p>' : " " )+
					(response.WasDialed != null &&  response.WasDialed.length ? '<p class="lead_dets_phone fz15 mb5"><b>Was Dialed:</b> <span>'+response.WasDialed+'</span></p>' : " " )+
					(response.id != null &&  response.id.length ? '<p class="lead_dets_phone fz15 mb5"><b>ID:</b> <span>'+response.id+'</span></p>' : " " )+
					(response.Notes != null &&  response.Notes.length ? '<div class="lead_detail_notes"><h4><i class="fas fa-chevron-right"></i> Notes</h4><p class="lead_dets_phone fz15 mb5">'+response.Notes+'</p></div>' : " " )+
					'<br>'
				;

				$('#leadInspectionModal').find('.modal-body').append(lead_info);
				$('#leadInspectionModal').find('.modal-body').append(extra_fields_string);
		    }
		});
	},

	toggle_lead_notes:function(){
		$(this).find('p').slideToggle();
	}
}

function login_id(status_type, login) {
	// Build the id for the list/login - replace spaces with underscores
	if (login != undefined && login != null) {
		return status_type + '-' + login.replace(/ /g, "_");
	}
}

$('body').on('click', '.getleaddetails', RealTime.lead_dets_modal);
$('#leadInspectionModal .modal-body').on('click', '.lead_detail_notes', RealTime.toggle_lead_notes);

//add
//breakcode for paused column


// StatusCode:
// case 0: // Waiting
// case 1: // Paused
// case 2: // WrapUp
// case 3: // OnCall
// case 4: // OnManualCall
// case 5: // OnInboundCall

// State
// 0	None
// 1	Unavailable
// 2	Idle
// 3	Available