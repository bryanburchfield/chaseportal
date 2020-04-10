var Playbook_Filters = {

	init:function(){
		// $("#addFilterModal").on('show.bs.modal', this.get_fields);
		$('.filter_campaigns').on('change', this.get_fields);
		$('.add_filter').on('change', '.filter_fields', this.get_operators);
		$('.add_filter').on('submit', this.add_filter);
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
				var filter_fields = '<option value="">Select One</option>';
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
				var operators;
				for(var i=0;i<Object.entries(response).length;i++){
					operators += '<option value="'+Object.entries(response)[i][0]+'">'+Object.entries(response)[i][1]+'</option>';					
				}

				$('.filter_operators').append(operators);
			}
		});
	},

	add_filter:function(e){
		e.preventDefault();
		var form = $(this).serialize();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/filters',
			type: 'POST',
			data: {
				form:form
			},
			success: function (response) {
				console.log(response);
			}
		});
	}
}

$(document).ready(function(){
	Playbook_Filters.init();
});