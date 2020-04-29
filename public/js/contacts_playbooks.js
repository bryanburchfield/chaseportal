var Contacts_Playbook = {

	init:function(){
		$('#campaign_select').on('change', this.get_subcampaigns);
		$('.add_playbook').on('submit', this.add_playbook);
		$('.playbook_actions_modal, .playbook_filters_modal').on('click', this.populate_modal);
		$('.edit_playbook_modal, .remove_playbook_modal').on('click', this.pass_id_to_modal);
		$('.delete_playbook_playbook').on('click', this.delete_playbook);
		$('.edit_playbook').on('submit', this.update_playbook);
	},

	get_subcampaigns:function(e, campaign){
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
	        url: '/tools/playbook/get_subcampaigns',
	        type: 'POST',
	        dataType: 'json',
	        data: {campaign: campaign,},
	        success:function(response){

                $('.subcampaign').empty();
                var response = Object.entries(response.subcampaigns);

                var sub_camps='<option value="">Select One</option>';
                for(var i=0;i<response.length;i++){
                	sub_camps+='<option value="'+response[i][0]+'">'+response[i][1]+'</option>';
                }

                $('.subcampaign').append(sub_camps);
	        }
	    });
	},

	add_playbook:function(e){
		e.preventDefault();

		var form_data = $(this).serialize();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks' ,
	        type: 'POST',
	        dataType: 'json',
	        data: form_data,
	        success:function(response){
	            console.log(response);
	            if(response.status == 'success'){
	            	location.reload();
	            }
	        }
	    });
	},

	populate_modal:function(e){
		e.preventDefault();
		var modal = $(this).data('target'),
			playbookid = $(this).data('playbookid'),
			is_empty = $(this).data('is_empty'),
			campaign = $(this).data('campaign')
		;

		modal = modal.substring(1);

		if(modal == 'filterPlaybookModal'){
			if(!is_empty){
				return Contacts_Playbook.get_playbook_filters(playbookid, modal);
			}

			return Contacts_Playbook.get_filters(campaign, modal);
		}else{
			if(!is_empty){
				return Contacts_Playbook.get_playbook_actions(playbookid, modal);
			}
			return Contacts_Playbook.get_actions(campaign, modal);
		}
	},

	// pass id to edit and delete modals
	pass_id_to_modal:function(e){
		e.preventDefault();
		var id = $(this).data('playbookid');
		var modal = $(this).data('target');
		$(modal).find('.id').val(id);

		if($(this).data('name')){ /// pass name to delete modal
			$(modal).find('h3 span').html($(this).data('name'));
		}else{ // edit modal
			Contacts_Playbook.get_playbook(id);
		}
	},

	get_playbook_filters:function(playbookid, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks/filters/'+playbookid,
	        type: 'GET',
	        dataType: 'json',
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	delete_playbook:function(){
		var id = $('#deletePlaybookModal').find('.id').val();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks/'+id,
	        type: 'DELETE',
	        dataType: 'json',
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	update_playbook:function(e){
		e.preventDefault();

		var form_data = $(this).serialize();
		var id = $(this).find('.id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/playbook/playbooks/'+id,
			type: 'PATCH',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				if (response.status == 'success') {
					location.reload();
				}
			}, error: function (data) {
				if (data.status === 422) {
					$('.edit_playbook .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.edit_playbook .alert-danger').append('<li>' + value + '</li>');
							});
						}
						$('.add_btn_loader i').hide();
						$('.edit_playbook .alert-danger').show();
					});
				}
			}
		});
	},

	get_filters:function(campaign, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_filters',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_playbook_actions:function(playbookid, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbook/actions/'+playbookid,
	        type: 'GET',
	        dataType: 'json',
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_actions:function(campaign, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_actions',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_playbook:function(id){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks/'+id,
	        type: 'GET',
	        dataType: 'json',
	        success:function(response){
                console.log(response);

                var edit_modal = $('#editPlaybookModal');

                edit_modal.find('.name').val(response.name);
                edit_modal.find('#campaign_select option[value="'+response.campaign+'"]').prop('selected', true);
                $.when(
					Contacts_Playbook.get_subcampaigns(event, response.campaign)
				).done(function() {
					edit_modal.find('.subcampaign option[value="'+response.subcampaign+'"]').prop('selected', true);
				});
	        }
	    });
	}
}

$(document).ready(function(){
	Contacts_Playbook.init();
});