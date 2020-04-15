var Playbook_Filters = {

	init: function () {
		// $("#addFilterModal").on('show.bs.modal', this.get_fields);
		$('.filter_campaigns').on('change', this.get_fields);
		$('.filter_fields').on('change', this.update_filter_fields);
		$('.add_filter').on('change', '.filter_fields', this.get_operators);
		$('.add_filter').on('submit', this.add_filter);
		$('.delete_playbook_filter').on('click', this.delete_filter);
		$('.remove_playbook_filter_modal, .edit_playbook_filter_modal').on('click', this.populate_filter_modal);
		$('.update_filter').on('click', this.update_filter);
	},

	get_fields: function (selected_campaign=1) {

		$('.loader_hor').show();

		if(selected_campaign != null){
			// add filter
			if(selected_campaign.type == 'change'){
				campaign = $(this).val();
			}else{ // edit filter
				campaign=selected_campaign;
			}

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				}
			});

			$('.filter_fields').empty();
			return $.ajax({
				url: '/tools/playbook/get_table_fields',
				type: 'POST',
				data: { campaign: campaign },
			}).done(function(response){
				console.log(response);
				$('.loader_hor').hide();
				var filter_fields = '<option value="">Select One</option>';
				for (var i = 0; i < Object.entries(response).length; i++) {
					filter_fields += '<option data-type="' + Object.entries(response)[i][1] + '" value="' + Object.entries(response)[i][0] + '">' + Object.entries(response)[i][0] + '</option>';
				}

				$('.filter_fields').html(filter_fields);
			});
		}
	},

	get_operators: function (field_type=1) {
		$('.loader_hor').show();

		if(field_type.type == 'change'){
			type = $('.filter_fields').find('option:selected').data('type');
		}else{
			type=field_type;
		}

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		return $.ajax({
			url: '/tools/playbook/get_operators',
			type: 'POST',
			data: {type: type},
		}).done(function(response){
			console.log(response);
			$('.loader_hor').hide();
			var operators;
			for (var i = 0; i < Object.entries(response).length; i++) {
				operators += '<option value="' + Object.entries(response)[i][0] + '">' + Object.entries(response)[i][1] + '</option>';
			}

			$('.filter_operators').html(operators);
		});
	},

	add_filter: function (e) {
		e.preventDefault();
		var form = $(this).serialize();
		$('.add_filter .alert-danger').empty().hide();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/filters',
			type: 'POST',
			data: form,
			success: function (response) {
				console.log(response);
				if(response.status == 'success'){
					location.reload();
				}
			}, error: function (data) {
				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.add_filter .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.add_filter .alert-danger').show();
					});
				}
			}
		});
	},

	populate_filter_modal:function(e){
		e.preventDefault();
		var modal = $(this).data('target');
		var id = $(this).data('id');
		$(modal).find('input.id').val(id);

		if(modal.substring(1) == 'editFilterModal'){
			Playbook_Filters.edit_filter(id);
		}
	},

	edit_filter:function(id){

		$('.loader_hor').show();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/filters/'+id,
			type: 'GET',
			data: {
				id:id
			},
		}).done(function(response){
			console.log(response);
			var type;
			$.when(
				Playbook_Filters.get_fields(response.campaign)
			).done(function() {
				$("#editFilterModal .filter_fields option[value='"+response.field+"']").prop('selected', true);
				type = $( "#editFilterModal .filter_fields option:selected").data('type');
				$.when(
					Playbook_Filters.get_operators(type)
				).done(function() {
					$('#editFilterModal').find('.name').val(response.name);
					$("#editFilterModal .filter_campaigns option[value='"+response.campaign+"']").prop('selected', true);
					$("#editFilterModal .filter_operators option[value='"+response.operator+"']").prop('selected', true);
					$('#editFilterModal').find('.filter_value').val(response.value);
					$('.loader_hor').hide();
				});
			});
		});
	},

	update_filter:function(e){
		e.preventDefault();
		var form_data = $('.edit_filter').serialize();
		var id = $('.edit_filter').find('.id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/filters/'+id,
			type: 'PATCH',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				if(response.status == 'success'){
					location.reload();
				}
			},
		});
	},

	delete_filter:function(e){
		e.preventDefault();
		var id = $(this).parent().parent().find('.id').val();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/filters/'+id,
			type: 'DELETE',
			data: {
				id:id
			},
			success: function (response) {
				console.log(response);
				if(response.status == 'success'){
					location.reload();
				}
			}
		});
	}
}

$(document).ready(function () {
	Playbook_Filters.init();

	$('#editFilterModal').on('hidden.bs.modal', function () {
	    $('.edit_filter').trigger("reset");
	});
});