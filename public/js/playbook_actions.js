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
			},
		});
	},

	update_action_fields: function (e) {
		e.preventDefault();
		var type = $(this).val();
		$('.action_type_fields').hide();
		$('.action_type_fields.' + type).show();
	},

	update_call_statuses: function (e) {
		e.preventDefault();
		var campaign = $(this).val();
		console.log(campaign);

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
				var response = Object.keys(response);
				var dispos='<option value="">Select One</option>';
				for(var i=0;i<response.length;i++){
					dispos+='<option value="'+response[i]+'">'+response[i]+'</option>';
				}
				$('.call_status').append(dispos);
			},
		});
	}
}

$(document).ready(function () {
	Playbook_Actions.init();
});