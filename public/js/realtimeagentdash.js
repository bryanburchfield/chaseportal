var RealTime = {
	init: function (result) {
		console.log(result);

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

		$('#total_calls_que').find('h4').html(result[1][1]);
		$('#total_calls').find('h4').html(result[2][1]);
		$('#longest_hold_time').find('h4').html(seconds_to_hms(result[3][1]));
		$('#total_sales').find('h4').html(result[4][1]);
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
			$('#' + login_id(status_type, login) + 'Timer').text(seconds_to_hms(numSecs));

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

		return '<li id="' + login_id(status_type, data.Login) + '" class="list-group-item"> ' +
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
	}
}

function login_id(status_type, login) {
	// Build the id for the list/login - replace spaces with underscores
	if (login != undefined && login != null) {
		return status_type + '-' + login.replace(/ /g, "_");
	}

}

function seconds_to_hms(numSecs) {
	// Time calculations for hours, minutes and seconds
	var hours = Math.floor(numSecs / 3600);
	var minutes = Math.floor((numSecs / 60) % 60);
	var seconds = numSecs % 60;

	// format leading zeros
	hours = hours.toString().padStart(2, 0);
	minutes = minutes.toString().padStart(2, 0);
	seconds = seconds.toString().padStart(2, 0);

	return hours + ":" + minutes + ":" + seconds;
}

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