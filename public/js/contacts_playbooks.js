var Contacts_Playbook = {

	pb_campaign:'',

	init:function(){
		$('#campaign_select').on('change', this.get_subcampaigns);
		$('.add_playbook').on('submit', this.add_playbook);
		$('.playbook_actions_modal, .playbook_filters_modal').on('click', this.populate_modal);
		$('.edit_playbook_modal, .remove_playbook_modal').on('click', this.pass_id_to_modal);
		$('.delete_playbook_playbook').on('click', this.delete_playbook);
		$('.edit_playbook').on('submit', this.update_playbook);
		$('.playbook_action_manager').on('click', '.add_action', this.add_new_action);
		$('.playbook_filter_manager').on('click', '.add_filter', this.add_new_filter);
		$('.playbook_action_manager').on('click', '.delete_action_from_pb', this.delete_playbook_action);
		$('.playbook_filter_manager').on('click', '.delete_filter_from_pb', this.delete_playbook_filter);
		$('.update_actions').on('click', this.update_playbook_actions);
		$('.update_filters').on('click', this.update_playbook_filters);
		$('.edit_playbook').on('change', '#campaign_select', this.campaign_warning);
		$('.switch input').on('click', this.toggle_playbook);
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
	        	console.log(response);
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

		$(modal).find('#id').val(playbookid);
		modal = modal.substring(1);

		if(modal == 'filterPlaybookModal'){
			return Contacts_Playbook.get_playbook_filters(campaign, playbookid, modal, is_empty);
		}else{
			return Contacts_Playbook.get_playbook_actions(campaign, playbookid, modal, is_empty);
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

	get_playbook_filters:function(campaign, playbookid, modal, is_empty){

		var all_filters;
		Contacts_Playbook.pb_campaign=campaign;

		$.when(
			all_filters = Contacts_Playbook.get_filters(campaign, modal)
		).done(function() {
			$('#'+modal).find('.modal-body .playbook_filter_manager').empty();
			var add_filter_btn = '<a href="#" class="add_filter mt20"><i class="fas fa-plus-circle"></i> Add Filter</a>';

			if(is_empty){
            	$('#'+modal).find('.modal-body .playbook_filter_manager').append(add_filter_btn);
			}else{
				$('.loader_hor').show();
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
	    				$('#'+modal).find('.subcampaign option[value="'+response.subcampaign+'"]').prop('selected', true);
	    				var filters='',
	    					j=0
	    				;
	                    for(var i=0;i<response.length;i++){
	                    	var filter_select = '<div class="row"><div class="col-sm-10"><select class="form-control filter_menu"><option value="">Select One</option>';
	                    	for(var j=0;j<all_filters.responseJSON.length;j++){
	                    		var selected = all_filters.responseJSON[j].name == response[i].name ? 'selected' :'';
	                    		filter_select+='<option '+selected+' data-id="'+all_filters.responseJSON[j].id+'" value="'+all_filters.responseJSON[j].name+'">'+all_filters.responseJSON[j].name+'</option>';
	                    	}

	                    	filter_select+='</select></div>';
	                    	filters+='<div class="modal_manage_fil_act" data-filterid="'+response[i].playbook_filter_id+'">'+filter_select+'<div class="col-sm-2"><a class="delete_filter_from_pb" href="#"><i class="fa fa-trash-alt"></i></a></div></div></div>';
	                    }

	                    $('#'+modal).find('.modal-body .playbook_filter_manager').append(filters);
	                    $('#'+modal).find('.modal-body .playbook_filter_manager').append(add_filter_btn);
	                    $('.loader_hor').hide();
	                    Contacts_Playbook.check_numb_filters($('.add_filter'));
			        }
			    });
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
                if (response.status == 'success') {
					location.reload();
				}
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
						$('.add_btn_loader i').remove();
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

	    return $.ajax({
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

	get_actions:function(campaign, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    return $.ajax({
	        url: '/tools/playbook/get_actions',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){

	        }
	    });
	},

	get_playbook_actions:function(campaign, playbookid, modal, is_empty){
		var all_actions;
		Contacts_Playbook.pb_campaign=campaign;
		$.when(
			all_actions = Contacts_Playbook.get_actions(campaign, modal)
		).done(function() {
			$('#'+modal).find('.modal-body .playbook_action_manager').empty();
			var add_action_btn = '<a href="#" class="add_action mt20"><i class="fas fa-plus-circle"></i> Add Action</a>';

			if(is_empty){
            	$('#'+modal).find('.modal-body .playbook_action_manager').append(add_action_btn);
			}else{
				$('.loader_hor').show();

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

	    				$('#'+modal).find('.subcampaign option[value="'+response.subcampaign+'"]').prop('selected', true);
	    				var actions='';

	                    for(var i=0;i<response.length;i++){
	                    	var action_select = '<div class="row"><div class="col-sm-10"><select class="form-control action_menu"><option value="">Select One</option>';
	                    	for(var j=0;j<all_actions.responseJSON.length;j++){
	                    		var selected = all_actions.responseJSON[j].name == response[i].name ? 'selected' :'';
	                    		action_select+='<option '+selected+' data-id="'+all_actions.responseJSON[j].id+'" value="'+all_actions.responseJSON[j].name+'">'+all_actions.responseJSON[j].name+'</option>';
	                    	}

	                    	action_select+='</select></div>';
	                    	actions+='<div class="modal_manage_fil_act" data-actionid="'+response[i].playbook_action_id+'">'+action_select+'<div class="col-sm-2"><a data-actionid="'+response[i].playbook_action_id+'" class="delete_action_from_pb" href="#"><i class="fa fa-trash-alt"></i></a></div></div></div>';
	                    }

	                    $('#'+modal).find('.modal-body .playbook_action_manager').append(actions);
	                    $('#'+modal).find('.modal-body .playbook_action_manager').append(add_action_btn);
	                    $('.loader_hor').hide();
	                    Contacts_Playbook.check_numb_actions($('.add_action'));
			        }
			    });
			}
		});

	},

	add_new_action:function(e){
		e.preventDefault();

		var modal = $('div.modal.in').attr('id'),
			all_actions,
			actions='',
			id = $(this).parent().parent().find('#id').val()
		;

		$.when(
			all_actions = Contacts_Playbook.get_actions(Contacts_Playbook.pb_campaign)
		).done(function() {
			var action_select = '<div class="row"><div class="col-sm-10"><select class="form-control action_menu"><option value="">Select One</option>';
			for(var j=0;j<all_actions.responseJSON.length;j++){
				action_select+='<option data-id="'+all_actions.responseJSON[j].id+'" value="'+all_actions.responseJSON[j].name+'">'+all_actions.responseJSON[j].name+'</option>';
			}

			action_select+='</select></div>';
			actions+='<div class="modal_manage_fil_act" data-actionid="'+id+'">'+action_select+'<div class="col-sm-2"><a data-actionid="'+id+'" class="delete_action_from_pb" href="#"><i class="fa fa-trash-alt"></i></a></div></div></div>';

			$(actions).insertBefore($('#'+modal).find('.modal-body .playbook_action_manager a.add_action '));
			Contacts_Playbook.check_numb_actions($('.add_action'));
		});
	},

	add_new_filter:function(e){
		e.preventDefault();

		var modal = $('div.modal.in').attr('id'),
			all_filters,
			filters='',
			id = $(this).parent().parent().find('#id').val()
		;

		$.when(
			all_filters = Contacts_Playbook.get_filters(Contacts_Playbook.pb_campaign)
		).done(function() {
			var filter_select = '<div class="row"><div class="col-sm-10"><select class="form-control filter_menu"><option value="">Select One</option>';
			for(var j=0;j<all_filters.responseJSON.length;j++){
				filter_select+='<option data-id="'+all_filters.responseJSON[j].id+'" value="'+all_filters.responseJSON[j].name+'">'+all_filters.responseJSON[j].name+'</option>';
			}

			filter_select+='</select></div>';
			filters+='<div class="modal_manage_fil_act" data-filterid="'+id+'">'+filter_select+'<div class="col-sm-2"><a data-filterid="'+id+'" class="delete_filter_from_pb" href="#"><i class="fa fa-trash-alt"></i></a></div></div></div>';

			$(filters).insertBefore($('#'+modal).find('.modal-body .playbook_filter_manager a.add_filter '));
			Contacts_Playbook.check_numb_filters($('.add_filter'));
		});
	},

	delete_playbook_action:function(e){
		e.preventDefault();
		$(this).parent().parent().parent().remove();
		Contacts_Playbook.check_numb_actions($('.add_action'));
	},

	delete_playbook_filter:function(e){
		e.preventDefault();
		$(this).parent().parent().parent().remove();
		Contacts_Playbook.check_numb_filters($('.add_filter'));
	},

	check_numb_actions(sel){
		var pb_actions;
		$.when(
			pb_actions = Contacts_Playbook.get_actions(Contacts_Playbook.pb_campaign)
		).done(function() {
			if(pb_actions.responseJSON.length == $('.playbook_action_manager .modal_manage_fil_act').length){
				sel.hide();
			}else{
				sel.show();
			}
		});
	},

	check_numb_filters(sel){
		var pb_filters;
		$.when(
			pb_filters = Contacts_Playbook.get_filters(Contacts_Playbook.pb_campaign)
		).done(function() {
			if(pb_filters.responseJSON.length == $('.playbook_filter_manager .modal_manage_fil_act').length){
				sel.hide();
			}else{
				sel.show();
			}
		});
	},

	update_playbook_actions:function(e){
		e.preventDefault();
		$('.alert').hide();
		var actions = [];
		var playbookid = $(this).parent().prev().find('#id').val();
		var errors=0;

		$('.modal_manage_fil_act').each(function(){
			if($(this).find('.action_menu').val() !=''){
				actions.push($(this).find('.action_menu').find(':selected').data('id'));
			}else{
				errors=1;
				return false;
			}
		});

		if(errors){
			$('#actionPlaybookModal .alert').text('Select an action before saving changes').show();
			return false;
		}

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks/actions/'+playbookid,
	        type: 'PATCH',
	        dataType: 'json',
	        data:{
	        	actions:actions
	        },
	        success:function(response){
	        	console.log(response);
                if(response.status == 'success'){
	            	location.reload();
	            }
	        }
	    });
	},

	update_playbook_filters:function(e){
		e.preventDefault();
		$('.alert').hide();
		var filters = [];
		var playbookid = $(this).parent().prev().find('#id').val();
		var errors=0;

		$('.modal_manage_fil_act').each(function(){
			if($(this).find('.filter_menu').val() !=''){
				filters.push($(this).find('.filter_menu').find(':selected').data('id'));
			}else{
				errors=1;
				return false;
			}
		});

		if(errors){
			$('#filterPlaybookModal .alert').text('Select a filter before saving changes').show();
			return false;
		}

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks/filters/'+playbookid,
	        type: 'PATCH',
	        dataType: 'json',
	        data:{
	        	filters:filters
	        },
	        success:function(response){
	        	console.log(response);
                if(response.status == 'success'){
	            	location.reload();
	            }
	        }
	    });
	},

	campaign_warning:function(){
		$('.edit_playbook .modal-body .alert').remove();
		var warning = '<div class="alert alert-warning">Note: If you change the Playbook Campaign you may loose filters and actions that are associated with it.</div>';
		$('.edit_playbook .modal-body').append(warning);
		var campaign = $(this).val();
		Contacts_Playbook.get_subcampaigns(event, campaign);
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
	},

	toggle_playbook:function(e){

		if($(this).hasClass('doesnt_meet_reqs')){
			e.preventDefault();
		}

        var checked;
        var playbook_id = $(this).parent().parent().parent().data('playbook_id');

        $('.playbook_activation_errors.alert-danger').hide();

        if($(this).is(':checked')){
            $(this).attr('Checked','Checked');
            checked=1;
        }else{
            $(this).removeAttr('Checked');
            checked=0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/tools/playbook/toggle_playbook',
            type:'POST',
            data:{
                checked:checked,
                id:playbook_id,

            },
            success:function(response){
            	console.log(response)
            }, error: function (data) {

				if (data.status === 422) {
					$('.playbook_activation_errors.alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.playbook_activation_errors.alert-danger').append('<li>' + value + '</li>');
							});
						}
						$('.add_btn_loader i').remove();
						$('.playbook_activation_errors.alert-danger').show();
					});
				}
				$('html, body').animate({
	                scrollTop: $(".playbook_activation_errors.alert-danger").offset().top
	            }, 1000);
			}
        });
    },
}

$(document).ready(function(){
	Contacts_Playbook.init();

	$('#actionPlaybookModal, #filterPlaybookModal').on('hidden.bs.modal', function () {
		$('.playbook_filter_manager .modal_manage_fil_act, .playbook_action_manager .modal_manage_fil_act').remove();
	    $('.alert').hide();
	});

	$('#editPlaybookModal').on('hidden.bs.modal', function () {
	    $('.alert').hide();
	});
});