var Playbook_Actions = {

	init: function () {
		$('.add_action').on('submit', this.add_action);
		$('.action_types').on('change', this.update_action_fields);
		$('.to_campaign').on('change', this.update_call_statuses);
		$('.edit_playbook_action_modal, .remove_playbook_action_modal').on('click', this.populate_filter_modal);
		$('.filter_campaigns').on('change', this.get_table_fields);
		$('.edit_action').on('submit', this.update_action);
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
			url: '/tools/playbook/actions',
			type: 'POST',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				console.log(response);
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
				console.log(data);
				if (data.status === 422) {
					$('.add_action .alert-danger').empty();
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

		$('.alert-danger').empty().hide();
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

		return $.ajax({
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

		return $.ajax({
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

	populate_filter_modal:function(e){
		e.preventDefault();

		var modal = $(this).data('target');
		var id = $(this).data('id');
		var name = $(this).data('name');
		$(modal).find('input.id').val(id);

		if(modal.substring(1) == 'editActionModal'){
			Playbook_Actions.edit_filter(id);
		}

		$(modal).find('.modal-body h3 span').text(name);

		var id = $(this).data('playbook_actionid');

	},

	edit_filter:function(id){
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
					edit_action.find(".to_campaign option[value='"+response.to_campaign+"']").prop('selected', true);

					$.when(
						Playbook_Actions.get_subcamps(response.to_campaign)
					).done(function() {
						edit_action.find(".to_subcampaign option[value='"+response.to_subcampaign+"']").prop('selected', true);
						edit_action.find(".call_status option[value='"+response.to_call_status+"']").prop('selected', true);
					});
				}

				if(response.action_type == 'email'){
					edit_action.find(".email_service_provider_id option[value='"+response.email_service_provider_id+"']").prop('selected', true);
					edit_action.find(".template_id  option[value='"+response.template_id+"']").prop('selected', true);
					edit_action.find(".subject").val(response.subject);
					edit_action.find(".from").val(response.from);
					edit_action.find(".days_between_emails").val(response.days_between_emails);
					edit_action.find(".emails_per_lead").val(response.emails_per_lead);
					$.when(
						Playbook_Actions.get_table_fields(event, response.campaign)
					).done(function() {
						edit_action.find(".email_field option[value='"+response.email_field+"']").prop('selected', true);
					});
				}

				if(response.action_type == 'sms'){

				}
			},
		});
	},

	update_action:function(e){
		e.preventDefault();
		var form_data = $(this).serialize();
		var id = $(this).find('.id').val();
		console.log(id);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/actions/'+id,
			type: 'PATCH',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				console.log(response);
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
				console.log(data);
				if (data.status === 422) {
					$('.edit_action .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.edit_action .alert-danger').append('<li>' + value + '</li>');
							});
						}
						$('.add_btn_loader i').hide();
						$('.edit_action .alert-danger').show();
					});
				}
			}
		});
	},

	get_table_fields:function(e, campaign){
		e.preventDefault();

		if(!campaign){
			var campaign = $(this).val();
		}

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		return $.ajax({
			url: '/tools/playbook/get_table_fields',
			type: 'POST',
			dataType: 'json',
			data: { campaign: campaign },
			success: function (response) {

				$('.email_field').empty();
				var fields = '<option value="">Select One</option>';
				var r = Object.entries(response);

				for(var i=0;i<r.length;i++){
					fields+='<option data-fieldtype="'+r[i][1]+'" value="'+r[i][0]+'">'+r[i][0]+'</option>';
				}

				$('.email_field').append(fields);
			},
		});
	}
}

$(document).ready(function () {
	Playbook_Actions.init();

	$('#addActionModal').on('hidden.bs.modal', function () {
	    $('.add_action').trigger("reset");
	});

	$('#editActionModal').on('hidden.bs.modal', function () {
	    $('.edit_action').trigger("reset");
	    $('.action_type_fields').hide();
	});
});