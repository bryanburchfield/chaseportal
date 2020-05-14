var Playbook_Actions = {

	actions_dataTable: $('#actions_dataTable').DataTable({
		responsive: true,
		dom: 'Bfrtip',
		buttons: [],
		fnDrawCallback: function(oSettings) {
	        if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
	          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
	        }
	    }
	}),

	init: function () {
		$('.add_action').on('submit', this.add_action);
		$('.action_types').on('change', this.update_action_fields);
		$('.to_campaign').on('change', this.update_call_statuses);
		$('#actions_dataTable').on('click', '.edit_playbook_action_modal, .remove_playbook_action_modal', this.populate_action_modal);
		$('.filter_campaigns').on('change', this.get_table_fields);
		$('.edit_action').on('submit', this.update_action);
		$('.delete_playbook_action ').on('click', this.delete_action);
	},

	add_action: function (e) {
		e.preventDefault();

		var form_data;

		var name = $(this).find('.name').val(),
			campaign = $(this).find('.filter_campaigns').val(),
			action_type = $(this).find('.action_types').val()
		;

		if(action_type == 'email'){
			var email_service_provider_id = $('.email  .email_service_provider_id').val(),
				template_id = $('.email  .template_id').val(),
				email_field = $('.email  .email_field').val(),
				subject = $('.email  .subject').val(),
				from = $('.email  .from').val(),
				days_between_emails = $('.email  .days_between_emails').val(),
				emails_per_lead = $('.email  .emails_per_lead').val()
			;

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&email_service_provider_id='+email_service_provider_id+'&template_id='+template_id+'&email_field='+email_field+'&subject='+subject+'&from='+from+'&days_between_emails='+days_between_emails+'&emails_per_lead='+emails_per_lead;
		}

		if(action_type == 'sms'){
			var from_number = $('.sms .from').val(),
				template_id = $('.sms .template_id').val(),
				sms_per_lead = $('.sms .sms_per_lead').val(),
				days_between_sms = $('.sms .days_between_sms').val()
			;

			from_number = encodeURIComponent(from_number);

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&from_number='+from_number+'&template_id='+template_id+'&sms_per_lead='+sms_per_lead+'&days_between_sms='+days_between_sms;
		}

		if(action_type == 'lead'){
			var to_campaign = $('.lead .to_campaign').val(),
				to_subcampaign = $('.lead .to_subcampaign').val(),
				to_callstatus = $('.lead .call_status').val()
			;

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&to_campaign='+to_campaign+'&to_subcampaign='+to_subcampaign+'&to_callstatus='+to_callstatus;
		}

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
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
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

	update_action_fields: function(e, type='', campaign) {

		if(!type){
			if($(this).val() !=''){var type = $(this).val();}else{return false;}
		}

		$('.alert-danger').empty().hide();
		$('.action_type_fields').hide();
		$('.action_type_fields.' + type).show();
		
		if(type == 'lead'){
			Playbook_Actions.update_call_statuses(event, campaign);
		}
	},

	update_call_statuses: function (e, campaign) {
		var campaign;

		if(e && e.type === 'change'){
			e.preventDefault();
			campaign = $(this).val();
			Playbook_Actions.get_subcamps(campaign);
		}

		campaign = !campaign ? $('.to_campaign').val() :campaign;
		
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
				var dispos='<option value="">'+Lang.get('js_msgs.select_one')+'</option>';
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
				var response = Object.entries(response.subcampaigns);
				var sub_camps='<option value="">'+Lang.get('js_msgs.select_one')+'</option>';
				for(var i=0;i<response.length;i++){
					sub_camps+='<option value="'+response[i][0]+'">'+response[i][1]+'</option>';
				}
				$('.to_subcampaign').append(sub_camps);
			},
		});
	},

	populate_action_modal:function(e){
		e.preventDefault();

		var modal = $(this).data('target');
		var id = $(this).data('id');
		var name = $(this).data('name');
		$(modal).find('input.id').val(id);

		if(modal.substring(1) == 'editActionModal'){
			Playbook_Actions.edit_action(id);
		}

		$(modal).find('.modal-body h3 span').text(name);

		var id = $(this).data('playbook_actionid');

	},

	edit_action:function(id){
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

				var edit_action = $('.edit_action');
				edit_action.find('.name').val(response.name);
				edit_action.find(".filter_campaigns option[value='"+response.campaign+"']").prop('selected', true);
				edit_action.find(".action_types option[value='"+response.action_type+"']").prop('selected', true);
				Playbook_Actions.update_action_fields(event, response.action_type, response.to_campaign);

				if(response.action_type == 'lead'){
					edit_action.find(".to_campaign option[value='"+response.to_campaign+"']").prop('selected', true);
					$.when(
						Playbook_Actions.get_subcamps(response.to_campaign),
					).done(function() {
						edit_action.find(".to_subcampaign option[value='"+response.to_subcampaign+"']").prop('selected', true);
						edit_action.find(".call_status option[value='"+response.to_callstatus+"']").prop('selected', true);
					});
				}

				if(response.action_type == 'sms'){
					edit_action.find(".from option[value='"+response.from_number +"']").prop('selected', true);
					edit_action.find('.message').val(response.message);
					edit_action.find('.sms_per_lead').val(response.sms_per_lead);
					edit_action.find('.days_between_sms').val(response.days_between_sms);
					edit_action.find(".template_id  option[value='"+response.template_id +"']").prop('selected', true);
				}

				if(response.action_type == 'email'){
					edit_action.find(".email_service_provider_id option[value='"+response.email_service_provider_id+"']").prop('selected', true);
					edit_action.find(".subject").val(response.subject);
					edit_action.find(".from").val(response.from);
					edit_action.find(".days_between_emails").val(response.days_between_emails);
					edit_action.find(".emails_per_lead").val(response.emails_per_lead);
					edit_action.find(".template_id  option[value='"+response.template_id +"']").prop('selected', true);
					$.when(
						Playbook_Actions.get_table_fields(event, response.campaign)
					).done(function() {
						edit_action.find(".email_field option[value='"+response.email_field+"']").prop('selected', true);
					});
				}
			},
		});
	},

	update_action:function(e){
		e.preventDefault();
		var name = $(this).find('.name').val(),
			id = $(this).find('.id').val(),
			action_type = $(this).find('.action_types').val(),
			campaign = $(this).find('.filter_campaigns').val()
		;

		if(action_type == 'email'){
			var email_service_provider_id = $(this).find('.email  .email_service_provider_id').val(),
				template_id = $(this).find('.email  .template_id').val(),
				email_field = $(this).find('.email  .email_field').val(),
				subject = $(this).find('.email  .subject').val(),
				from = $(this).find('.email  .from').val(),
				days_between_emails = $(this).find('.email  .days_between_emails').val(),
				emails_per_lead = $(this).find('.email  .emails_per_lead').val()
			;

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&email_service_provider_id='+email_service_provider_id+'&template_id='+template_id+'&email_field='+email_field+'&subject='+subject+'&from='+from+'&days_between_emails='+days_between_emails+'&emails_per_lead='+emails_per_lead+'&id='+id;
		}

		if(action_type == 'sms'){
			var from_number = $(this).find('.sms .from').val(),
				template_id = $(this).find('.sms .template_id').val(),
				sms_per_lead = $(this).find('.sms .sms_per_lead').val(),
				days_between_sms = $(this).find('.sms .days_between_sms').val()
			;

			from_number = encodeURIComponent(from_number);

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&from_number='+from_number+'&template_id='+template_id+'&sms_per_lead='+sms_per_lead+'&days_between_sms='+days_between_sms+'&id='+id;
		}

		if(action_type == 'lead'){
			var to_campaign = $(this).find('.lead .to_campaign').val(),
				to_subcampaign = $(this).find('.lead .to_subcampaign').val(),
				to_callstatus = $(this).find('.lead .call_status').val()
			;

			form_data='name='+name+'&campaign='+campaign+'&action_type='+action_type+'&to_campaign='+to_campaign+'&to_subcampaign='+to_subcampaign+'&to_callstatus='+to_callstatus+'&id='+id;
		}

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
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
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
		
		$('.loader_hor').show();

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
				var fields = '<option value="">'+Lang.get('js_msgs.select_one')+'</option>';
				var r = Object.entries(response);

				for(var i=0;i<r.length;i++){
					fields+='<option data-fieldtype="'+r[i][1]+'" value="'+r[i][0]+'">'+r[i][0]+'</option>';
				}

				$('.email_field').append(fields);
				$('.loader_hor').hide();
			},
		});
	},

	delete_action:function(e){
		e.preventDefault();
		var id = $(this).parent().parent().find('.id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/actions/'+id,
			type: 'DELETE',
			dataType: 'json',
			data: { id: id },
			success: function (response) {
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
				if (data.status === 422) {
					$('#deleteActionModal .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('#deleteActionModal .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('#deleteActionModal .alert-danger').show();
					});
				}
			}
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
	    $(this).find('.alert').hide();
	});

	$('#addActionModal, #deleteActionModal').on('hidden.bs.modal', function () {
	    $(this).find('.alert').hide();
	});

	
});