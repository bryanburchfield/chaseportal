var Playbook_History = {

	playbooks_history_dataTable: $('#playbooks_history_table').DataTable({
		responsive: true,
		fixedHeader: true,
		aaSorting: [[0, 'asc']],
		dom: 'Bfrtip',
		buttons: [],
		fnDrawCallback: function(oSettings) {
	        if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
	          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
	        }
	    }
	}),

	run_playbooks_history_table: $('#run_playbooks_history_table').DataTable({
		responsive: true,
		fixedHeader: true,
		dom: 'Bfrtip',
		buttons: [],
		fnDrawCallback: function(oSettings) {
	        if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
	          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
	        }
	    }
	}),

	run_action_playbooks_history_table: $('#run_action_playbooks_history_table').DataTable({
		responsive: true,
		fixedHeader: true,
		dom: 'Bfrtip',
		buttons: [],
		fnDrawCallback: function(oSettings) {
	        if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
	          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
	        }
	    }
	}),

	init: function () {
		
	},

}

$(document).ready(function () {
	Playbook_History.init();

});