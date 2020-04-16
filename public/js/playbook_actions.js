var Playbook_Actions = {

	init: function () {
		$('.add_action').on('submit', this.add_action);
		$('.action_types').on('change', this.update_action_fields);
		$('.to_campaign').on('change', this.update_call_statuses);
	},

	add_action: function (e) {
		e.preventDefault();
		var form_data = $(this).serialize();
		console.log(form_data);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/actions/',
			type: 'POST',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				console.log(response);
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.add_action .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.add_action .alert-danger').show();
					});
				}
			}
		});
	},

	update_action_fields: function (e) {

		var type = $(this).val();
		$('.action_type_fields').hide();
		$('.action_type_fields.' + type).show();

		if(type == 'lead'){console.log('ran');
			Playbook_Actions.update_call_statuses();
		}
	},

	update_call_statuses: function (e) {
		var campaign;
		if(e && e.type === 'change'){
			e.preventDefault();
			campaign = $(this).val();
			Playbook_Actions.get_subcamps(campaign);
		}

		campaign = $('.to_campaign').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_dispos',
			type: 'POST',
			dataType: 'json',
			data: { campaign: campaign },
			success: function (response) {
				$('.call_status').empty();
				var response = Object.keys(response);
				var dispos='<option value="">Select One</option>';
				for(var i=0;i<response.length;i++){
					dispos+='<option value="'+response[i]+'">'+response[i]+'</option>';
				}
				$('.call_status').append(dispos);
			},
		});
	},

	get_subcamps:function(campaign){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/get_subcampaigns',
			type: 'POST',
			dataType: 'json',
			data: { campaign: campaign },
			success: function (response) {
				$('.to_subcampaign').empty();
				var response = Object.values(response.subcampaigns);
				var sub_camps='<option value="">Select One</option>';
				for(var i=0;i<response.length;i++){
					sub_camps+='<option value="'+response[i]+'">'+response[i]+'</option>';
				}
				$('.to_subcampaign').append(sub_camps);
			},
		});
	}
}

$(document).ready(function () {
	Playbook_Actions.init();
});