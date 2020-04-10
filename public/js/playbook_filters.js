var Playbook_Filters = {

	init:function(){
		// $("#addFilterModal").on('show.bs.modal', this.get_fields);
		$('.filter_campaigns').on('change', this.get_fields);
	},

	get_fields: function () {

		var campaign = $(this).val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_table_fields',
			type: 'POST',
			data: {campaign:campaign},
			success: function (response) {
				console.log(response);
			}
		});
	},
}

$(document).ready(function(){
	Playbook_Filters.init();
});