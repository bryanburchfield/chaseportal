var RealTime = {
	init: function (result) {
		// if first iteration
		if (!ran) {
			// keep track of all the in-memory lists
			status_arrays = [];

			// keep track of all the running timers
			timers = [];

			// load each returned array
			for (var i = 0; i < result.length; i++) {
				let status_type = result[i][0];
				status_arrays[status_type] = this.load_array(status_type, result[i][1]);
			}
		} else {
			// process each returned list
			for (var i = 0; i < result.length; i++) {
				let status_type = result[i][0];
				status_arrays[status_type] = this.process_array(status_type, status_arrays[status_type], result[i][1]);
			}
		}

		console.log(status_arrays);
		ran = true;
	},

	load_array(list_id, result_data) {
		var return_array = [];

		this.delete_all_rows(list_id);

		for (var i = 0; i < result_data.length; i++) {
			return_array.push({ 'Login': result_data[i]['Login'], 'checksum': result_data[i]['checksum'] });
			this.add_row(list_id, result_data[i]);
		}

		return return_array;
	},

	delete_all_rows(list_id) {
		// stop all the timers in that list
		if (status_arrays[list_id] != null) {
			for (var i = 0; i < status_arrays[list_id].length; i++) {
				this.stop_timer(list_id, status_arrays[list_id][i].Login);
			}
		}

		// delete all <li>s
		$('#' + list_id).empty();
	},

	add_row(list_id, data) {
		// add new <li> to bottom of list
		$('#' + list_id).append(this.build_li(list_id, data));

		// Start a timer
		timers[list_id + data.Login] = this.start_timer(list_id, data.Login, data.SecondsInStatus);
	},

	update_row(list_id, data) {
		// update <li>
		$('#' + login_id(list_id, data.Login)).replaceWith(this.build_li(list_id, data));

		// Kill the running timer, start a new one
		this.stop_timer(list_id, data.Login);
		timers[list_id + data.Login] = this.start_timer(list_id, data.Login, data.SecondsInStatus);
	},

	delete_row(list_id, login) {
		// stop timer
		this.stop_timer(list_id, login);

		// Delete <li>
		$('#' + login_id(list_id, login)).remove();
	},

	process_array(list_id, array_data, result_data) {
		// first, check that there's anything in the returned list
		if (result_data.length == 0) {
			if (array_data.length > 0) {
				this.delete_all_rows(list_id);
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
						this.update_row(list_id, row_obj);
					}
				} else {
					delete_list.push(array_data[i].Login);
				}
			}

			// now delete the ones that need it
			for (var i = 0; i < delete_list.length; i++) {
				array_data = array_data.filter(row => row.Login != delete_list[i]);
				this.delete_row(list_id, delete_list[i]);
			}

			// now go thru the returned list and insert if necc
			for (var i = 0; i < result_data.length; i++) {
				if (!array_data.find(row => row.Login === result_data[i]['Login'])) {
					array_data.push({ 'Login': result_data[i]['Login'], 'checksum': result_data[i]['checksum'] });
					this.add_row(list_id, result_data[i]);
				}
			}
		}

		return array_data;
	},

	start_timer(list_id, login, numSecs) {
		// convert to int
		numSecs = parseInt(numSecs);

		// this runs once per second
		return setInterval(function () {

			// Format and output the result
			$('#' + login_id(list_id, login) + 'Timer').text(seconds_to_hms(numSecs));

			// tick the timer
			numSecs = numSecs + 1;

		}, 1000);
	},

	stop_timer(list_id, login) {
		if (timers[list_id + login] != null) {
			clearInterval(timers[list_id + login]);
		}
	},

	build_li(list_id, data) {
		return '<li id="' + login_id(list_id, data.Login) + '" class="list-group-item"> ' +
			'<p data-checksum="' + data.checksum + '" class="rep_name mb0">' + data.Login + '</p>' +
			'<p class="campaign">' + data.Campaign + '</p>' +
			'<p id="' + login_id(list_id, data.Login) + 'Timer"></p>' +
			'</li>';
	}
}

function login_id(list_id, login) {
	// Build the id for the list/login - replace spaces with underscores
	return list_id + '-' + login.replace(/ /g, "_");
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
