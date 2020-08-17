var RealTime = {
	init: function (result) {
		// if first iteration
		if (!ran) {
			status_arrays = new Array();

			console.log('length ' + result.length);

			// load each returned array
			for (var i = 0; i < result.length; i++) {
				let status_type = result[i][0];
				status_arrays[status_type] = [];
				this.load_array(status_type, status_arrays[status_type], result[i][1]);
			}
		} else {
			// process each returned list
			for (var i = 0; i < result.length; i++) {
				let status_type = result[i][0];
				this.process_array(status_type, status_arrays[status_type], result[i][1]);
			}
		}

		console.log(status_arrays);
		ran = true;
	},

	load_array(list_id, array_data, result_data) {
		this.delete_all_rows(list_id);

		for (var i = 0; i < result_data.length; i++) {
			array_data.push({ 'Login': result_data[i]['Login'], 'checksum': result_data[i]['checksum'] });
			this.add_row(list_id, result_data[i]);
		}
	},

	delete_all_rows(list_id) {
		console.log('delete all ' + list_id)
		// TODO: delete all the <li>s from <ul> with id of list_id
	},

	add_row(list_id, data) {
		console.log('add row ' + list_id)
		// TODO: add <li> of data to <ul> with id of list_id
	},

	update_row(list_id, data) {
		console.log('update row ' + list_id)
		// TODO: update <li> where data.Login list_id
	},

	delete_row(list_id, login) {
		console.log('delete row ' + list_id)
		//  TODO: delete <li> of list_id where Login = login
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
	}
}
