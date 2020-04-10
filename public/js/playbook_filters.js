var Playbook_Filters = {

	init:function(){
		// $("#addFilterModal").on('show.bs.modal', this.get_fields);
		$('.filter_campaigns').on('change', this.get_fields);
		$('.add_filter').on('change', '.filter_fields', this.get_operators);
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
				var filter_fields = [];
				for(var i=0;i<Object.entries(response).length;i++){
			    	filter_fields += '<option data-type="'+Object.entries(response)[i][1]+'" value="'+Object.entries(response)[i][0]+'">'+Object.entries(response)[i][0]+'</option>';
			    }

				$('.filter_fields').append(filter_fields);
			}
		});
	},

	get_operators:function(){

		var type = $('.filter_fields').find('option:selected').data('type');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_operators',
			type: 'POST',
			data: {
				type:type
			},
			success: function (response) {
				var operators = [];
				for(var i=0;i<Object.entries(response).length;i++){
					operators += '<option value="'+Object.entries(response)[i][0]+'">'+Object.entries(response)[i][1]+'</option>';					
				}

				$('.filter_operators').append(operators);
			}
		});
	}
}

$(document).ready(function(){
	Playbook_Filters.init();
});