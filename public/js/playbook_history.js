var Playbook_History = {

	playbooks_history_dataTable: $('#playbooks_history_table').DataTable({
		responsive: true,
		fixedHeader: true,
		aaSorting: [[0, 'desc']],
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
		$('.reverse_action').on('click', this.reverse_action);
	},

	reverse_action:function(e){
		e.preventDefault();
		var id = $(this).data('id');

		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		    }
		});

		$('#sidebar').empty();

		var sidenav = $(this).data('path');
		$("html, body").animate({ scrollTop: 0 }, "slow");

		$.ajax({
		    url: '/playbook/history/reverse/action/'+id,
		    type: 'POST',
		    dataType: 'html',
		    data: {id:id },
		    success: function (response) {
		        console.log(response);
		        location.reload();
		    }
		});
	}
}

$(document).ready(function () {
	Playbook_History.init();
});