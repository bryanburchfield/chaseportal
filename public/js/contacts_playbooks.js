var Contacts_Playbook = {
	playbooks_datatable: $('#playbooks_datatable').DataTable({
		responsive: true,
		fixedHeader: true,
		dom: 'Bfrtip',
		buttons: [],
		fnDrawCallback: function(oSettings) {
	        if (oSettings._iDisplayLength >= oSettings.fnRecordsDisplay()) {
	          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
	        }
	    }
	}),
	playbook_id:'',
	pb_campaign:'',
    actions: $('select.action_type').first().find('option').length -1,
    actions_used:$('.action_row').length,
    leadrule_filters: $('.filter_type').first().find('option').length -1,
    leadrule_filters_used: $('.leadfilter_row').length,
    flowchart_vline_height:$('.add_filter').parent().parent().parent().find('.vertical-line').height,
    subcampaigns_count:0,
    current_modal:'',
    subcampaigns : [],
    org_subcampaigns : [],

	init:function(){
		$('.campaign_select, #destination_campaign').on('change', this.get_extracampaigns);
		$('body').on('change', '.extra_campaigns', this.check_extra_camp_selection);
		$('.add_playbook').on('submit', this.add_playbook);
		$('body').on('click', '.delete_playbook_modal', this.delete_playbook_modal);
		$('.edit_playbook').on('submit', this.update_playbook);
		$('.update_actions').on('click', this.update_playbook_actions);
		$('.update_filters').on('click', this.update_playbook_filters);
		$('.edit_playbook').on('change', '.campaign_select', this.campaign_warning);
		$('a.activate_all_playbooks').on('click', this.activate_all_playbooks);
		$('a.deactivate_all_playbooks').on('click', this.deactivate_all_playbooks);
		$('.playbook').on('click', '.switch input', this.toggle_playbook);
		$('.touch .switch input').on('click', this.toggle_touch);
		$('.add_touch').on('submit', this.create_touch);
		$('body').on('click', '.edit_playbook_modal, .delete_touch_modal', this.pass_id_to_modal);
		$('.edit_touch').on('submit', this.update_touch);
		$('body').on('click', 'a.add_filter', this.add_filter);
		$('body').on('click', 'a.add_action', this.add_action);
		$('body').on('click', '.remove_filter', this.remove_leadrule_filter);
		$('body').on('click', '.remove_action', this.remove_action);
		$('body').on('change', '.filter_type', this.change_filter_label);
        $('.edit_rule .update_filter_type').on('change', this.change_filter_label);
        $('.delete_playbook').on('click', this.delete_playbook);
        $('a.delete_touch').on('click', this.delete_touch);
        $('.menu').on('click', this.preventDefault);
        $('body').on('click', '.add_subcampaign ', this.add_subcampaign_select);
        $('body').on('click', '.remove_subcamp', this.remove_subcamp_select);
        $('.extra_campaigns, .subcampaigns').on('click', '#select_all', this.toggle_all_subcamps);
        $('.subcampaign_list').on('click', '.undoselection_btn', this.undo_subcamp_selection);
	},

	preventDefault:function(e){
		e.preventDefault();
	},

	toggle_playbook:function(e){

	    var checked,
	    	that = $(this),
	    	id = $(this).parent().parent().data('playbook'),
	    	campaign = that.data('campaign')
	    ;

	    checked = Contacts_Playbook.toggle_checked(that, checked, 0);

	    $('#contact_playbooks .row .alert-danger').empty().hide();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/toggle_playbook',
	        dataType: 'json',
	        type:'POST',
	        data:{
	            id:id,
	            checked:checked,
	        },
	        success:function(response){
	        	Contacts_Playbook.toggle_checked(that, checked, 0);
	        }, error: function (data) {
	        	Contacts_Playbook.toggle_checked(that, checked, 1);
	        	e.preventDefault();
				if (data.status === 422) {
					$('#contact_playbooks .row .alert-danger').empty().hide();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('#contact_playbooks .row .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('#contact_playbooks .row .alert-danger').show();
					});
				}
			}
	    });
	},

	toggle_touch:function(e){

		var checked,
	    	that = $(this),
	    	id = $(this).data('id')
	    ;

	    checked = Contacts_Playbook.toggle_checked(that, checked, 0);

	    $('.touches .row .alert-danger').empty().hide();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/toggle_playbook_touch',
	        type:'POST',
	        data:{
	            id:id,
	            checked:checked,

	        },
	        success:function(response){
	        	Contacts_Playbook.toggle_checked(that, checked, 0);
	        }, error: function (data) {
	        	Contacts_Playbook.toggle_checked(that, checked, 1);
	        	e.preventDefault();
				if (data.status === 422) {
					$('.touches .row .alert-danger').empty().hide();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.touches .row .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.touches .row .alert-danger').show();
					});
				}
			}
	    });
	},

	toggle_checked:function(that, checked, error){

    	if(that.is(':checked') && !error){
    		that.addClass('checked');
    	    that.attr('Checked','Checked');
    	    that.prop('checked',true);
    	    checked=1;
    	}else{
    		that.removeClass('checked');
    	    that.removeAttr('Checked');
    	    that.prop('checked',false);
    	    checked=0;
    	}

    	return checked;
    },

	get_subcampaigns:function(e, campaign){
		e.preventDefault();

		$('.loader_hor').show();
		$('#'+Contacts_Playbook.current_modal).find('.subcampaigns').parent().show();

		if(!campaign){
			var campaign = $(this).val();
		}

		var that = $(this);
		$('div.modal').each(function(){
			if($(this).hasClass('in')){
				Contacts_Playbook.current_modal = $(this).attr('id');
			}
		});

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    return $.ajax({
	        url: '/playbook/get_subcampaigns',
	        type: 'POST',
	        dataType: 'json',
	        data: {campaign: campaign,},
	        success:function(response){
	        	console.log(response)
	        }
	    });
	},

	get_extracampaigns:function(e, campaign){

		if(!campaign){
			var campaign = $(this).val();
		}

		$('div.modal').each(function(){
			if($(this).hasClass('in')){
				Contacts_Playbook.current_modal = $(this).attr('id');
			}
		});

		$('#addPlaybookModal').on('shown.bs.modal', function () {
			Contacts_Playbook.current_modal = $(this).attr('id');
		});

		$('#editPlaybookModal').on('shown.bs.modal', function () {
			Contacts_Playbook.current_modal = $(this).attr('id');
		});

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

        return $.ajax({
            url: '/playbook/get_extra_campaigns',
            type: 'POST',
            dataType: 'json',
            data: {campaign: campaign,},
            success:function(response){
            	console.log(response);
            	$('.loader_hor').hide();
            	$('#'+Contacts_Playbook.current_modal).find('.subcampaigns').empty();
	        	var subcamps_response = Object.keys(response.subcampaigns);

	        	if(subcamps_response.length){
		        	Contacts_Playbook.subcampaigns=[];

	        		var subcampaign_list='';
	        		var selected;

	        		for(var i=0; i<subcamps_response.length;i++){
	        		    selected =  subcamps_response[i].selected ? 'checked' : '';
	        		    subcampaign_list+='<div class="checkbox mb10 cb"><label><input class="subcamps" name="subcampaign_list[]" '+selected+' type="checkbox" value="'+subcamps_response[i]+'"><b>'+subcamps_response[i]+'</b></label></div>';
	        		}

	        		$('#'+Contacts_Playbook.current_modal).find('.subcampaigns').append(subcampaign_list);
	        	}

            	$('#'+Contacts_Playbook.current_modal).find('.extra_campaigns').empty();

            	var extra_camps_response = Object.keys(response.extra_campaigns);

	        	if(extra_camps_response.length){
		        	Contacts_Playbook.extra_camps=[];

	        		var extra_camps_list='';
	        		var selected;

	        		for(var i=0; i<extra_camps_response.length;i++){
	        		    selected =  extra_camps_response[i].selected ? 'checked' : '';
	        		    extra_camps_list+='<div class="checkbox mb10 cb"><label><input class="extra_camps" name="extra_camps_list[]" '+selected+' type="checkbox" value="'+extra_camps_response[i]+'"><b>'+extra_camps_response[i]+'</b></label></div>';
	        		}

	        		$('#'+Contacts_Playbook.current_modal).find('.extra_campaigns').append(extra_camps_list);
	        	}
            }
        });
	},

	check_extra_camp_selection:function(){
		var extras_checked=0;
		$('#'+Contacts_Playbook.current_modal).find('.extra_campaigns').find('.checkbox input[type="checkbox"]:checked').each(function () {
		    extras_checked++;
		});

		if(extras_checked){
			$('#'+Contacts_Playbook.current_modal).find('#subcampaigns_menu').parent().hide();
			return false;
		}else{
			$('#'+Contacts_Playbook.current_modal).find('#subcampaigns_menu').parent().show();
		}
	},

	toggle_all_subcamps:function(){
	    if($(this).prop("checked")){
	        $(".subcampaign_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.unselect_all'));
	        $(this).parent().parent().siblings().find('label input').prop( "checked", true );
	    }else{
	        $(".subcampaign_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.select_all'));
	        $(this).parent().parent().siblings().find('label input').prop( "checked", false );
	    }
	},

	/// put subcampaigns selection back to saved list
    undo_subcamp_selection:function(e){
        e.preventDefault();
        $(".subcampaign_list input.subcamps").prop('checked', false);
        $(".subcampaign_list input.subcamps").each(function(i) {
            for(var j=0;j<Contacts_Playbook.org_subcampaigns.length;j++){
                if(Contacts_Playbook.org_subcampaigns[j]==i){
                    $(this).prop( "checked", true );
                }
            }
        });

        $(".subcampaign_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.select_all'));
    },

	add_subcampaign_select:function(e){
		e.preventDefault();
		$('#'+Contacts_Playbook.current_modal).find('.alert-danger').hide();

		if($(this).prev().find('.subcampaigns').val() !=''){
			var new_subcamp = $('.subcampaigns').last().parent().clone();
			$(new_subcamp).find('a').removeClass('hidetilloaded');
			$(new_subcamp).insertBefore('.modal-body .add_subcampaign');
			
		}else{
			$('#'+Contacts_Playbook.current_modal).find('.alert-danger').text(Lang.get('js_msgs.select_subcamp')).show();
			return false;
		}

		if(Master.subcampaigns_count > $('#'+Contacts_Playbook.current_modal).find('.subcampaigns').length){
			$('.add_subcampaign').show();
		}else{
			$('.add_subcampaign').hide();
		}

		$('.subcampaigns').last().parent().find('a.remove_subcamp').removeClass('hidetilloaded');
	},

	remove_subcamp_select:function(e){
		e.preventDefault();
		$(this).parent().remove();
		if(Master.subcampaigns_count > $('#addPlaybookModal').find('.subcampaigns').length){
			$('.add_subcampaign').show();
		}else{
			$('.add_subcampaign').hide();
		}
	},

	add_playbook:function(e){
		e.preventDefault();

		var name = $(this).find('.name').val(),
			campaign = $(this).find('.campaign_select').val(),
			campaigns=[],
			subcampaigns = []
		;

		$('#'+Contacts_Playbook.current_modal).find('.extra_campaigns').find('.checkbox input[type="checkbox"]:checked').each(function () {
		    campaigns.push($(this).val());
		});

		if($('#'+Contacts_Playbook.current_modal).find("#subcampaigns_menu").is(":visible")){
			$('#'+Contacts_Playbook.current_modal).find('.subcampaigns').find('.checkbox input[type="checkbox"]:checked').each(function () {
			    subcampaigns.push($(this).val());
			});
		}else{
			subcampaigns=[];
		}

		$('.loader_hor').show();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });


	    $.ajax({
	        url: '/playbook/playbooks' ,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	        	name:name,
	        	campaign:campaign,
	        	campaigns:campaigns,
	        	subcampaigns:subcampaigns
	        },
	        success:function(response){

	            if(response.status == 'success'){
	            	location.reload();
	            	$('.loader_hor').hide();
	            }
	        }, error: function (data) {
				if (data.status === 422) {
					$('.loader_hor').hide();
					$('.add_playbook .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.add_playbook .alert-danger').append('<li>' + value + '</li>');
							});
						}
						$('.add_btn_loader i').remove();
						$('.add_playbook .alert-danger').show();
					});
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

	delete_playbook_modal:function(){
		$('#editPlaybookModal').modal('hide');
		var id = $(this).data('id');
		$('#deletePlaybookModal').find('h3 span').html($(this).data('name'));
		$('#deletePlaybookModal').find('.id').val($(this).data('id'));
	},

	delete_playbook:function(){
		var id = $('#deletePlaybookModal').find('.id').val();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/playbooks/'+id,
	        type: 'DELETE',
	        dataType: 'json',
	        success:function(response){
                if (response.status == 'success') {
					window.location.href = '/playbook';
				}
	        }
	    });
	},

	// pass id to edit and delete modals
	pass_id_to_modal:function(e){
		e.preventDefault();

		var id = $(this).data('id');
		Master.pass_id_to_modal(this, id);
		Contacts_Playbook.get_playbook(id);
		Contacts_Playbook.playbook_id=$(this).data('id') ? $(this).data('id'): '' ;

		// $('div.modal').each(function(){
		// 	if($(this).hasClass('in')){
		// 		console.log(Contacts_Playbook.current_modal +' - '+ $(this).attr('id'));
		// 		Contacts_Playbook.current_modal = $(this).attr('id');
		// 	}
		// });
	},

	get_playbook_filters:function(campaign, playbookid, modal, is_empty){

		var all_filters;
		Contacts_Playbook.pb_campaign=campaign;

		$.when(
			all_filters = Contacts_Playbook.get_filters(campaign, modal)
		).done(function() {
			$('#'+modal).find('.modal-body .playbook_filter_manager').empty();
			var add_filter_btn = '<a href="#" class="add_filter mt20"><i class="fas fa-plus-circle"></i> '+Lang.get('js_msgs.add_filter')+'</a>';

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
			        url: '/playbook/playbooks/filters/'+playbookid,
			        type: 'GET',
			        dataType: 'json',
			        success:function(response){

	    				$('#'+modal).find('.subcampaign option[value="'+response.subcampaign+'"]').prop('selected', true);
	    				var filters='',
	    					j=0
	    				;
	                    for(var i=0;i<response.length;i++){
	                    	var filter_select = '<div class="row"><div class="col-sm-10"><select class="form-control filter_menu"><option value="">'+Lang.get('js_msgs.select_one')+'</option>';
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

	update_playbook:function(e){
		e.preventDefault();

		var id = $('#'+Contacts_Playbook.current_modal).find(".id").val(),
			name = $(this).find('.name').val(),
			campaign = $(this).find('.campaign_select').val(),
			campaigns=[],
			subcampaigns = []
		;

		$('#'+Contacts_Playbook.current_modal).find('.extra_campaigns').find('.checkbox input[type="checkbox"]:checked').each(function () {
		    campaigns.push($(this).val());
		});

		console.log(campaigns);

		if($('#'+Contacts_Playbook.current_modal).find("#subcampaigns_menu").is(":visible")){
			$('#'+Contacts_Playbook.current_modal).find('.subcampaigns').find('.checkbox input[type="checkbox"]:checked').each(function () {
			    subcampaigns.push($(this).val());
			});
		}else{
			subcampaigns=[];
		}

		$('.loader_hor').show();

		console.log(Contacts_Playbook.current_modal + '-' + $('#'+Contacts_Playbook.current_modal).find(".id").val() +' ID: '+ id);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/playbook/playbooks/'+id,
			type: 'PATCH',
			dataType: 'json',
			data: {
				name:name,
	        	campaign:campaign,
	        	campaigns:campaigns,
	        	subcampaigns:subcampaigns
			},
			success: function (response) {
				if (response.status == 'success') {
					location.reload();
					$('.loader_hor').hide();
				}
			}, error: function (data) {
				if (data.status === 422) {
					$('.loader_hor').hide();
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
	        url: '/playbook/get_filters',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){
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
	        url: '/playbook/get_actions',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){

	        }
	    });
	},

	get_playbook_actions_count:function(campaign, playbookid){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    return $.ajax({
	        url: '/playbook/playbook/actions/'+playbookid,
	        type: 'GET',
	        async:false,
	        dataType: 'json',
	        success:function(response){
	        }
	    });
	},

	get_playbook_filters_count:function(campaign, playbookid){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    return $.ajax({
	        url: '/playbook/playbooks/filters/'+playbookid,
	        type: 'GET',
	        async:false,
	        dataType: 'json',
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
			var add_action_btn = '<a href="#" class="add_action mt20"><i class="fas fa-plus-circle"></i> '+Lang.get('js_msgs.add_action')+'</a>';

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
			        url: '/playbook/playbook/actions/'+playbookid,
			        type: 'GET',
			        dataType: 'json',
			        success:function(response){

	    				$('#'+modal).find('.subcampaign option[value="'+response.subcampaign+'"]').prop('selected', true);
	    				var actions='';

	                    for(var i=0;i<response.length;i++){
	                    	var action_select = '<div class="row"><div class="col-sm-10"><select class="form-control action_menu"><option value="">'+Lang.get('js_msgs.select_one')+'</option>';
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
	        url: '/playbook/actions/'+playbookid,
	        type: 'PATCH',
	        dataType: 'json',
	        data:{
	        	actions:actions
	        },
	        success:function(response){

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
			$('#filterPlaybookModal .alert').text(Lang.get('js_msgs.save_warning')).show();
			return false;
		}

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/playbooks/filters/'+playbookid,
	        type: 'PATCH',
	        dataType: 'json',
	        data:{
	        	filters:filters
	        },
	        success:function(response){

                if(response.status == 'success'){
	            	location.reload();
	            }
	        }
	    });
	},

	campaign_warning:function(){
		$('.edit_playbook .modal-body .alert').remove();
		var warning = '<div class="alert alert-warning">'+Lang.get('js_msgs.campaign_warning')+'</div>';
		$('.edit_playbook .modal-body').append(warning);
		var campaign = $(this).val();
		Contacts_Playbook.get_subcampaigns(event, campaign);
	},

	get_playbook:function(id){
		$('.loader_hor').show();
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/playbooks/'+id,
	        type: 'GET',
	        dataType: 'json',
	        success:function(response){
	        	console.log(response);
                var edit_modal = $('#editPlaybookModal');

                edit_modal.find('.name').val(response.name);
                edit_modal.find('.campaign_select option[value="'+response.campaign+'"]').prop('selected', true);
                edit_modal.find('.subcampaigns').empty();

                var campaigns = response.extra_campaigns.length;

                if(campaigns){
                	edit_modal.find('#subcampaigns_menu').parent().hide();
                }else{
                	edit_modal.find('#subcampaigns_menu').parent().show();
                }

                $.when(
					Contacts_Playbook.get_extracampaigns(event, response.campaign)
				).done(function() {

					edit_modal.find('.extra_campaigns .checkbox input').each(function(){
						for(var i=0;i<response.extra_campaigns .length;i++){
							if($(this).val() == response.extra_campaigns [i]){
								$(this).prop('checked', true);
							}
						}
					});

					edit_modal.find('.subcampaigns .checkbox input').each(function(){
						for(var i=0;i<response.subcampaigns.length;i++){
							if($(this).val() == response.subcampaigns[i]){
								$(this).prop('checked', true);
							}
						}
					});
				});
	        }
	    });
	},

    activate_all_playbooks:function(e){

    	e.preventDefault();
    	var checked,
    		playbook_ids = [],
    		that = $(this)
    	;

    	$('.playbooks table tbody tr').each(function(){
    		playbook_ids.push($(this).data('playbook_id'));
    		$(this).find('td:first').find('input.toggle_playbook').addClass('checked');
    		$(this).find('td:first').find('input.toggle_playbook').attr('Checked','Checked');
    	});

    	playbook_ids.sort((a,b)=>a-b);
    	$('.playbook_activation_errors.alert-danger, .playbook_activation_warning').empty().hide();

    	$.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/playbook/activate_all_playbooks',
            type:'POST',
            data:{
                checked:checked,
                ids:playbook_ids,
            },
            success:function(response){
            	var that;
            	if(response.status=='error'){
            		for(var i=0;i<response.failed.ids.length;i++){
            			that = $('input.toggle_playbook[data-playbook_id="' + response.failed.ids[i] + '"]');
            			Contacts_Playbook.toggle_checked(that, 0, 1);
            		}

            		var warning_msg='<h4 class="mb20"><b>' + Lang.get('js_msgs.playbook_warning') + '</b></h4>';
            		for(var i=0;i<response.failed.names.length;i++){
            			warning_msg+='<li>'+ response.failed.names[i] +'</li>';
            		}

            		$('.playbook_activation_warning').append(warning_msg).show();
					$('html, body').animate({
		                scrollTop: $(".playbook_activation_warning").offset().top -80+'px'
		            }, 500);
            	}
            }
        });
    },

    deactivate_all_playbooks:function(e){

    	e.preventDefault();
    	var checked,
    		playbook_ids = [],
    		that = $(this)
    	;

    	$('.playbook_activation_errors.alert-danger, .playbook_activation_warning').empty().hide();

    	$('.playbooks table tbody tr').each(function(){
    		playbook_ids.push($(this).data('playbook_id'));
    		$(this).find('td:first').find('input.toggle_playbook').removeClass('checked');
    		$(this).find('td:first').find('input.toggle_playbook').removeAttr('Checked','Checked');
    	});

    	playbook_ids.sort((a,b)=>a-b);

    	$.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/playbook/deactivate_all_playbooks',
            type:'POST',
            data:{
                checked:checked,
                ids:playbook_ids,

            },
            success:function(response){
            }
        });
    },

    create_touch:function(e){
        e.preventDefault();
        $('#add_rule').find('.add_rule_error').empty().hide();
        var name = $('#name').val(),
            playbook_id = $('.playbook_id').val()
        ;

        var filters = [];
        $('.filter_type').each(function(){
        	filters.push($(this).val());
        });

        var actions = [];
        $('.action_type').each(function(){
        	actions.push($(this).val());
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/playbook/touches/'+playbook_id,
            type: 'POST',
            dataType: 'json',
            data: {
                name:name,
              	actions:actions,
            	filters:filters
            },

            success:function(response){
                window.location.href = '/playbook/touches/'+playbook_id;
            },
            error :function( data ) {
                $('.add_rule_error.alert').empty();
                $('.add_rule_error.alert').hide();

                var errors = $.parseJSON(data.responseText);
                $.each(errors, function (key, value) {

                    if($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                            $('.add_rule_error.alert').show().append('<li>'+value+'</li>');
                        });
                    }else{
                        $('.add_rule_error.alert').show().append('<li>'+value+'</li>');
                    }
                });

                $('.add_btn_loader i').remove();
                $('.add_rule_error.alert li').first().remove();
            }
        });
    },

    update_touch:function(e){
    	e.preventDefault();
    	$('.edit_rule_error.alert').hide();

    	var name = $('#name').val(),
    	    playbook_touch_id = $('.playbook_touch').val(),
    	    playbook_id = $('.playbook_id').val()
    	;

    	var filters = [];
    	$('.filter_type').each(function(){
    		filters.push($(this).val());
    	});

    	var actions = [];
    	$('.action_type').each(function(){
    		actions.push($(this).val());
    	});

    	$.ajaxSetup({
    	    headers: {
    	        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    	    }
    	});

    	$.ajax({
    	    url: '/playbook/touches/touch/'+playbook_touch_id,
    	    type: 'PATCH',
    	    dataType: 'json',
    	    data: {
    	        name:name,
    	      	actions:actions,
    	    	filters:filters
    	    },

    	    success:function(response){
    	        window.location.href = '/playbook/touches/'+playbook_id;
    	    },
    	    error :function( data ) {
    	        $('.edit_rule_error.alert').empty();
    	        $('.edit_rule_error.alert').hide();

    	        var errors = $.parseJSON(data.responseText);
    	        $.each(errors, function (key, value) {

    	            if($.isPlainObject(value)) {
    	                $.each(value, function (key, value) {
    	                    $('.edit_rule_error.alert').show().append('<li>'+value+'</li>');
    	                });
    	            }else{
    	                $('.edit_rule_error.alert').show().append('<li>'+value+'</li>');
    	            }
    	        });

    	        $('.edit_rule_error.alert li').first().remove();
    	    }
    	});
    },

	delete_touch:function(e){
		e.preventDefault();
		var playbook_touch_id = $('form.delete_touch').find('.id').val();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/playbook/touches/touch/'+playbook_touch_id,
	        type: 'DELETE',
	        dataType: 'json',
	        success:function(response){
                if (response.status == 'success') {
					window.location.href = '/playbook/touches/'+Contacts_Playbook.playbook_id;
				}
	        }
	    });
	},

    add_filter:function(e){
        e.preventDefault();

        if(Contacts_Playbook.leadrule_filters_used < Contacts_Playbook.leadrule_filters){
            $('.alert.filter_error').hide();
            var selected_filter = $(this).parent().find('.filter_type').val();

            if(selected_filter){
                $(this).parent().parent().parent().find('.vertical-line').height(Contacts_Playbook.flowchart_vline_height);

                if(Contacts_Playbook.leadrule_filters != Contacts_Playbook.leadrule_filters_used ){

                    var new_filter = $(this).parent().parent().parent().clone();

                    $(new_filter).insertAfter('.leadfilter_row:last');
                    var i = Contacts_Playbook.leadrule_filters_used;
                    $(new_filter).find('.filter_type').val('');
                    $(new_filter).find('.flowchart_element span').text(Lang.get('js_msgs.and'));
                    $(new_filter).find('.flowchart_element').removeClass('when');
                    $(new_filter).find('.flowchart_element').addClass('and');
                    $(new_filter).find('.filter_type').attr('id', 'filter_type'+i).attr('name', 'filter_type'+i);

                    if(Contacts_Playbook.leadrule_filters_used!=Contacts_Playbook.leadrule_filters){
                        if(!$(new_filter).find('a.remove_filter').length){
                            $(new_filter).find('.card').append('<a href="#" class="remove_filter"><i class="fas fa-trash-alt"></i> '+Lang.get('js_msgs.remove_filter')+'</a>');
                        }
                    }

                    Contacts_Playbook.leadrule_filters_used=Contacts_Playbook.leadrule_filters_used+1;

                    if(Contacts_Playbook.leadrule_filters == Contacts_Playbook.leadrule_filters_used){
                        $(new_filter).find('a.add_filter').remove();
                    }

                    $(this).hide();
                }
            }else{
                Contacts_Playbook.flowchart_vline_height = $(this).parent().parent().parent().find('.vertical-line').height();
                $(this).parent().find('.alert').show();
                $(this).parent().parent().parent().find('.vertical-line').height(Contacts_Playbook.flowchart_vline_height + 180);
            }
        }
    },

    add_action:function(e){
    	e.preventDefault();

    	if(Contacts_Playbook.actions_used < Contacts_Playbook.actions){
    		$('.alert.action_error').hide();
    		var selected_action = $(this).parent().find('.action_type').val();
    		if(selected_action){
    			var new_action = $(this).parent().parent().parent().clone();
    			$(new_action).insertAfter('.action_row:last');
    			$(new_action).find('.action_type').val('');

    			if(Contacts_Playbook.actions_used!=Contacts_Playbook.actions){
    			    if(!$(new_action).find('a.remove_action').length){
    			        $(new_action).find('.card').append('<a href="#" class="remove_action"><i class="fas fa-trash-alt"></i> '+Lang.get('js_msgs.remove_action')+'</a>');
    			    }
    			}
    			Contacts_Playbook.actions_used=Contacts_Playbook.actions_used+1;

    			if(Contacts_Playbook.actions == Contacts_Playbook.actions_used){
    			    $(new_action).find('a.add_action').remove();
    			}

    			

    			$(this).parent().parent().prev().find('.vertical-line').removeClass('hidetilloaded').show();
    			$(this).hide();
    		}else{
    			$(this).parent().find('.alert').show();
    		}
	    }
    },

    remove_leadrule_filter:function(e){
        e.preventDefault();

        Contacts_Playbook.leadrule_filters_used=Contacts_Playbook.leadrule_filters_used-1;

        $(this).parent().parent().parent().remove();
        $('.update_filter_type').each(function(){
            $(this).attr('disabled', true);
        });

        $('.leadfilter_row').find('.card').each(function(){
            $(this).find('.add_filter').remove();
        });
        // remove add new filter buttons from all cards, add to last one
        if(Contacts_Playbook.leadrule_filters_used != Contacts_Playbook.leadrule_filters){
            $('.leadfilter_row:last').find('.card').append('<a href="#" class="add_filter edit_addrule"><i class="fas fa-plus-circle"></i> '+Lang.get('js_msgs.add_filter')+'</a>');
        }
    },

    remove_action:function(e){
    	e.preventDefault();

        Contacts_Playbook.actions_used=Contacts_Playbook.actions_used-1;
        $(this).parent().parent().parent().find('.vertical-line').hide();
        $(this).parent().parent().parent().prev().find('.vertical-line').hide();
        $(this).parent().parent().parent().remove();

        $('.action_row').find('.card').each(function(){
            $(this).find('.add_action').remove();
        });
        // remove add new filter buttons from all cards, add to last one
        if(Contacts_Playbook.actions_used != Contacts_Playbook.actions){
            $('.action_row:last').find('.card').append('<a href="#" class="add_action edit_addrule"><i class="fas fa-plus-circle"></i> '+Lang.get('js_msgs.add_action')+'</a>');
        }
    },

    change_filter_label: function () {
        var filtertype = $(this).find('option:selected').data('filtertype');
        $(this).parent().parent().find('.subfilter_group').hide();
        var subfilter = $(this).parent().parent().find('.subfilter_group[data-subfilter="' + filtertype + '"]');
        $(subfilter).show();
    },

}

$(document).ready(function(){
	Contacts_Playbook.init();

	$('#actionPlaybookModal, #filterPlaybookModal').on('hidden.bs.modal', function () {
		$('.playbook_filter_manager .modal_manage_fil_act, .playbook_action_manager .modal_manage_fil_act').remove();
	    $('.alert').hide();
	});

	$('#editPlaybookModal, #addPlaybookModal').on('hidden.bs.modal', function () {
		Master.reset_modal_form('#'+$(this).attr('id'));
	});

	$('.subcampaigns').on('click', '.stop-propagation', function (e) {
		e.stopPropagation();
	});

	$('.menu').popover({
	    html : true
	});

});