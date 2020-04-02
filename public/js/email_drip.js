var EmailDrip = {

	init: function () {
		$('.add_esp').on('submit', this.add_esp);
		$('.edit_provider_modal').on('click', this.edit_provider_modal);
		$('.edit_esp').on('submit', this.update_esp);
		$('.test_connection').on('click', this.test_connection);
		$('.remove_email_service_provider_modal, .remove_campaign_modal').on('click', this.populate_delete_modal);
		$('.delete_email_service_provider').on('click', this.delete_esp);
		$('.drip_campaigns_campaign_menu').on('change', this.get_playbook_subcampaigns);
		$('.provider_type').on('change', this.get_provider_properties);
		$('.filter_fields_cnt').on('change', '.filter_fields', this.get_operators);
		$('.cancel_modal_form').on('click', this.cancel_modal_form);
	},

	add_esp: function (e) {
		e.preventDefault();

		var form_data = $(this).serialize();

		$('.alert').empty().hide();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/add_esp',
			type: 'POST',
			data: form_data,
			success: function (response) {
				location.reload();
			}, error: function (data) {
				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.add_esp .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.add_esp .alert-danger').show();
					});
				}
			}
		});
	},

	edit_provider_modal: function (e) {
		e.preventDefault();

		var id = $(this).data('serverid');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_esp',
			type: 'POST',
			data: {
				id: id,
			},
			success: function (response) {

				$('#editESPModal .name').val(response.name);
				$('#editESPModal .provider_type').val(response.provider_type);
				$('#editESPModal .id').val(response.id);
				$('#editESPModal .properties').empty();
				var property_inputs = '';

				const entries = Object.entries(response.properties)
				for (const [key, value] of entries) {
					var label = key.charAt(0).toUpperCase() + key.slice(1);
					property_inputs += '<div class="form-group"><label>' + label + '</label><input type="text" class="form-control ' + key + '" name="properties[' + key + ']" value="' + value + '" required></div>';
				}

				$('#editESPModal .properties').append(property_inputs);
			}
		});
	},

	update_esp: function (e) {
		e.preventDefault();
		var form_data = $(this).serialize();

		$('.alert').empty().hide();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/update_esp',
			type: 'POST',
			data: form_data,
			success: function (response) {
				$(this).find('i').remove();
				location.reload();
			}, error: function (data) {
				$(this).find('i').remove();
				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.edit_smtp_server .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.edit_smtp_server .alert-danger').show();
					});
				}
			}
		});
	},

	test_connection: function (e) {
		e.preventDefault();

		$('.alert').empty().hide();

		var that = $(this).parent();
		var form_data = $(that).serialize();
		$.ajax({
			url: '/tools/playbook/test_connection ',
			type: 'POST',
			data: form_data,
			success: function (response) {

				$(that).find('.test_connection').find('i').remove();
				$(that).find('.connection_msg').removeClass('alert-danger alert-success');
				$(that).find('.connection_msg').addClass('alert-success').text(response.message).show();
			}, error: function (data) {
				$('.test_connection').find('i').remove();

				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$(that).find('.connection_msg').append('<li>' + value + '</li>');
								$(that).find('.connection_msg').addClass('alert-danger').show();
							});
						}
					});
				}
			}, statusCode: {
				500: function (response) {
					$(that).find('.alert-danger').text('Connection Failed').show();
				}
			}
		});
	},

	populate_delete_modal: function (e) {
		e.preventDefault();
		var id = $(this).data('id'),
			name = $(this).data('name'),
			sel = $(this).data('target')
			;

		$(sel + ' h3').find('span').text(name);
		$(sel + ' #id').val(id);
	},

	delete_esp: function (e) {
		e.preventDefault();
		var id = $('#deleteESPModal').find('#id').val();
		$('#deleteESPModal .alert-danger').hide();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/delete_esp',
			type: 'POST',
			data: {
				id: id,
			},
			success: function (response) {
				location.reload();
			}, error: function (data) {
				$('#deleteESPModal .btn').find('i').remove();
				if (data.status === 422) {
					$('#deleteESPModal .alert-danger').empty();
					// $('#deleteESPModal .btn').find('i').remove();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {
						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('#deleteESPModal .alert-danger').append('<li>' + value + '</li>');
							});
						}
						$('#deleteESPModal .alert-danger').show();
					});
				}
			}
		});
	},

	get_playbook_subcampaigns: function (e, campaign) {

		var sel;
		if (e.type == 'click') {
			sel = $('.edit_campaign_form');
			campaign = $('.edit_campaign_form').find('.drip_campaigns_campaign_menu').val();
		} else {
			if ($(e.target).parent().parent().hasClass('edit_campaign_form')) {
				sel = $('.edit_campaign_form');
				campaign = $(this).val();
			} else {
				campaign = $(this).val();
				sel = $('.create_campaign_form');
			}
		}

		var subcamp_response = Master.get_subcampaigns(campaign, '/tools/playbook/get_subcampaigns');
		$('.drip_campaigns_subcampaign').empty();
		$(sel).find('.email').empty();
		console.log(subcamp_response);
		var subcamp_obj = subcamp_response.responseJSON.subcampaigns;
		var subcamp_obj_length = Object.keys(subcamp_obj).length;
		const subcamp_obj_keys = Object.getOwnPropertyNames(subcamp_obj);
		let subcampaigns_array = [];
		subcampaigns_array.push(Object.values(subcamp_obj));

		$('.drip_campaigns_subcampaign').empty();

		var subcampaigns = '';
		for (var i = 0; i < subcampaigns_array[0].length; i++) {
			subcampaigns += '<option value="' + subcampaigns_array[0][i] + '">' + subcampaigns_array[0][i] + '</option>';
		}

		$('.drip_campaigns_subcampaign').append(subcampaigns);
		$(".drip_campaigns_subcampaign").multiselect('rebuild');
		$(".drip_campaigns_subcampaign").multiselect('refresh');

		$('.drip_campaigns_subcampaign')
			.multiselect({ nonSelectedText: '', })
			.multiselect('selectAll', true)
			.multiselect('updateButtonText');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_table_fields',
			type: 'POST',
			dataType: 'json',
			async: false,
			data: {
				campaign: campaign,
			},

			success: function (response) {

				var emails = '<option value="">Select One</option>';
				for (var index in response) {
					emails += '<option value="' + index + '">' + index + '</option>';
				}

				$(sel).find('.email').append(emails);
			},
		});
	},

	get_provider_properties: function (e) {
		var provider_type = $(this).val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		if (provider_type != '') {
			$.ajax({
				url: '/tools/playbook/get_provider_properties',
				type: 'POST',
				data: {
					provider_type: provider_type,
				},
				success: function (response) {
					$('.properties').empty();
					var properties = '';

					response.forEach(function (item, index) {
						var label = item.charAt(0).toUpperCase() + item.slice(1);
						properties += '<div class="form-group"><label>' + label + '</label><input type="text" class="form-control ' + item + '" name="properties[' + item + ']" value="" required></div>';
					});

					$('.properties').append(properties);
				}
			});
		}
	},

	get_operators: function () {
		var that = $(this);
		var type = $(that).find('option:selected').data('type');
		$('.filter_error').hide();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_operators',
			type: 'POST',
			data: {
				type: type,
			},
			success: function (response) {
				$(that).parent().parent().next().find('.filter_operators').empty();
				var operators = '<option value="">Select One</option>';

				for (let [key, value] of Object.entries(response[type])) {
					operators += '<option value="' + key + '">' + value + '</option>';
				}
				$(that).parent().parent().next().find('.filter_operators').append(operators);

				$('.filter_fields_cnt').show();
			}
		});
	},

	cancel_modal_form: function (e) {
		e.preventDefault();
		$(this).parent().parent().find('.form')[0].reset()
	},

}

$(document).ready(function () {
	EmailDrip.init();
});