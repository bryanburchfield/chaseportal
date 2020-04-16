var Playbook_Actions = {

	init: function () {
		$('.add_action').on('submit', this.add_action);
		$('.action_types').on('change', this.update_action_fields);
		$('.to_campaign').on('change', this.update_call_statuses);
		$('.edit_playbook_action_modal').on('click', this.populate_edit_modal);
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

	update_action_fields: function(e, type='') {

		if(!type){
			var type = $(this).val();
		}

		console.log(type);
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
	},

	populate_edit_modal:function(e){
		e.preventDefault();
		var id = $(this).data('playbook_actionid');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/actions/'+id,
			type: 'GET',
			dataType: 'json',
			data: { id: id },
			success: function (response) {
				console.log(response);
				var edit_action = $('.edit_action');
				edit_action.find('.name').val(response.name);
				edit_action.find(".filter_campaigns option[value='"+response.campaign+"']").prop('selected', true);
				edit_action.find(".action_types option[value='"+response.action_type+"']").prop('selected', true);
				Playbook_Actions.update_action_fields(event, response.action_type);

				if(response.action_type == 'lead'){
					// edit_action.find(".to_campaign option[value='"+response.action_type+"']").prop('selected', true);
				}
			},
		});
	}
}

$(document).ready(function () {
	Playbook_Actions.init();
});