var RealTime = {
	init: function (result) {
		// if first iteration
		if (!ran) {
			status_arrays = [];
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
		console.log('delete all ' + list_id)

		// stop all the timers in that list
		if (status_arrays[list_id] != null) {
			for (var i = 0; i < status_arrays[list_id].length; i++) {
				this.stop_timer(status_arrays[list_id][i].Login);
			}
		}

		$('#' + list_id).empty();
	},

	add_row(list_id, data) {
		console.log('add row ' + list_id + ' ' + data.Login)

		$('#' + list_id).append(this.build_li(data));

		// Start a timer
		timers[data.Login] = this.start_timer(data.Login, data.SecondsInStatus);
	},

	update_row(list_id, data) {
		console.log('update row ' + list_id + ' ' + data.Login)

		$('#' + data.Login.replace(/ /g, "_")).replaceWith(this.build_li(data));

		// Kill the running timer, start a new one
		this.stop_timer(data.Login);
		timers[data.Login] = this.start_timer(data.Login, data.SecondsInStatus);
	},

	delete_row(list_id, login) {
		console.log('delete row ' + list_id + ' ' + login)

		// stop timer
		this.stop_timer(login);

		$('#' + login.replace(/ /g, "_")).remove();
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

	start_timer(login, numSecs) {
		// this runs once per second
		console.log('start timer ' + login + ' ' + numSecs + ' = ' + seconds_to_hms(numSecs));

		return setInterval(function () {

			// Format and output the result
			$('#' + login.replace(/ /g, "_") + 'Timer').html = seconds_to_hms(numSecs);

			// console.log('tick timer ' + login + ' ' + numSecs + ' = ' + seconds_to_hms(numSecs));

			// tick the timer
			numSecs = numSecs + 1;

		}, 1000);
	},

	stop_timer(login) {
		if (timers[login] != null) {
			clearInterval(timers[login]);
		}
	},

	build_li(data) {
		return '<li id="' + data.Login.replace(/ /g, "_") + '" class="list-group-item"> ' +
			'<p data-checksum="' + data.checksum + '" class="rep_name mb0">' + data.Login + '</p>' +
			'<p class="campaign">' + data.Campaign + '</p>' +
			'<p id="' + data.Login.replace(/ /g, "_") + 'Timer"></p>' +
			'</li>';
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
