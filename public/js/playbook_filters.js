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
				// console.log(response);
				var filter_fields = [];
				// console.log(Object.entries(response) );
				for(var i=0;i<Object.entries(response).length;i++){
			    	filter_fields += '<option data-type="'+Object.entries(response)[i][1]+'" value="'+Object.entries(response)[i][0]+'">'+Object.entries(response)[i][0]+'</option>';
			    }

				$('.filter_fields').append(filter_fields);
			}
		});
	},

	get_operators:function(){
		var field = $('.filter_fields').find('option:selected').val();
		var field_type = $('.filter_fields').find('option:selected').data('type');
		console.log(field +'-'+field_type);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_operators',
			type: 'POST',
			data: {
				field:field,
				type:type
			},
			success: function (response) {
				console.log(response);
				var operators = [];

				// for(var i=0;i<response.length;i++){
				// 	if(field_type == response[i]){
				// 		console.log(Object.entries(response[i]));
				// 		// for(var j=0;j<response[i].length;j++){
				// 	    	// operators += '<option value="'+response[i][]+'">'+Object.keys(response)[i]+'</option>';
				// 	    // }
				// 	}
				// }
				

				

				// $('.filter_fields').append(filter_fields);
			}
		});
	}
}

$(document).ready(function(){
	Playbook_Filters.init();
});