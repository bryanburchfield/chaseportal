var Tools = {

	leadrule_filters: $('.lead_rule_filter_type').first().find('option').length -1,
    leadrule_filters_used: $('.leadfilter_row').length,
    flowchart_vline_height:$('.add_leadrule_filter').parent().parent().parent().find('.vertical-line').height,

	init:function(){
		$('#when .form-group #campaign_select, #action #destination_campaign').on('change', this.get_leadrule_subcampaigns);
        $('#when .form-group #update_campaign_select, #action #update_destination_campaign').on('change', this.get_leadrule_subcampaigns);
        $('.delete_rule').on('click', this.delete_rule);
        $('.reverse_lead_move').on('click', this.reverse_lead_move_modal);
        $('.confirm_reverse_lead_move').on('click', this.reverse_lead_move);
        $('.add_rule').on('submit', this.create_leadrule);
        $('.edit_rule').on('submit', this.updateleadrule);
        $('.switch.leadrule_switch input').on('click', this.toggle_leadrule);
        $('.lead_details').on('click', this.get_leadrule_details);
        $('#reverseLeadMoveModal').on('hidden.bs.modal', this.hide_modal_error);
        $('body').on('change', '.lead_rule_filter_type', this.change_filter_label);
        $('.edit_rule .update_filter_type').on('change', this.change_filter_label);
        $('body').on('click', '.add_leadrule_filter', this.add_leadrule_filter);
        $('body').on('click', '.remove_filter', this.remove_leadrule_filter);
        $('.delete_dnc').on('click', this.populate_dnc_modal);
        $('.reverse_dnc').on('click', this.populate_dnc_reversemodal);
        $('.toggle_instruc').on('click', this.toggle_instructions);
	},

	get_leadrule_subcampaigns:function(){

	    var campaign = $(this).val();
	    var selector = $(this).attr('id');

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/contactflow_builder/get_subcampaigns' ,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign: campaign,
	        },

	        success:function(response){

	            var subcampaigns='<option value=""> Select One</option>';
	            for(var i=0; i<response.subcampaigns.length;i++){
	                subcampaigns+='<option value="'+response.subcampaigns[i]+'">'+response.subcampaigns[i]+'</option>';
	            }

	            if(selector == 'campaign_select' || selector == 'update_campaign_select'){
	                $('#subcamps').empty();
	                $('#subcamps').append(subcampaigns);
	            }else if(selector == 'destination_campaign' || selector == 'update_destination_campaign'){
	                $('#destination_subcampaign').empty();
	                $('#destination_subcampaign').append(subcampaigns);
	            }
	        }
	    });
	},

	delete_rule: function (e) {
		e.preventDefault();

        var lead_rule_id = $('.lead_rule_id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/contactflow_builder/delete_rule',
			type: 'POST',
			dataType: 'json',
			data: {
				id: lead_rule_id
			},

			success: function (response) {
				window.location.href = '/tools/contactflow_builder';
			},
			error: function (data) {
				window.location.href = '/tools/contactflow_builder';
			}
		});
	},

	reverse_lead_move_modal: function (e) {
		e.preventDefault();
		var lead_move_id = $(this).data('leadid');
		$('#reverseLeadMoveModal').find('.lead_move_id').val(lead_move_id);
		$('#reverseLeadMoveModal').modal('show');
	},

	reverse_lead_move: function () {
		var lead_move_id = $('#reverseLeadMoveModal').find('.lead_move_id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/contactflow_builder/reverse_move',
			type: 'POST',
			dataType: 'json',
			data: { lead_move_id: lead_move_id },
			success: function (response) {

				$('#reverseLeadMoveModal').find('.modal-footer').find('.alert').remove();
				if (response.error) {

					$('#reverseLeadMoveModal').find('.modal-footer').append('<div class="alert alert-danger mt20 text-center">' + response.error + '</div>');
				} else {
					var hash = window.location.hash;
					localStorage.setItem('activeTab', hash);
					window.location = '/tools/contactflow_builder';
				}
			}
		});
	},

	create_leadrule:function(e){
        e.preventDefault();
        $('#add_rule').find('.add_rule_error').empty().hide();
        var rule_name = $('#rule_name').val(),
            source_campaign = $('#campaign_select').val(),
            source_subcampaign=$('.source_subcampaign').val(),
            destination_campaign = $('#destination_campaign').val(),
            destination_subcampaign = $('.destination_subcampaign').val(),
            description = $('#description').val()
        ;

        var filters={};
        var duplicate_filters = false;
        $('.lead_rule_filter_type').each(function(){
            if(!filters.hasOwnProperty($(this).val())){
                filters[$(this).val()] = $(this).parent().parent().find('.subfilter_group[data-subfilter="' + $(this).val() + '"]').find('.form-control ').val();
            }else{
                $('#add_rule .add_rule_error').html('<li>'+$(this).find("option:selected" ).text()+' filter was used more than once</li>').show();
                duplicate_filters=true;
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        if(!duplicate_filters){
            $.ajax({
                url: '/tools/contactflow_builder',
                type: 'POST',
                dataType: 'json',
                data: {
                    rule_name:rule_name,
                    source_campaign:source_campaign,
                    source_subcampaign:source_subcampaign,
                    destination_campaign:destination_campaign,
                    destination_subcampaign:destination_subcampaign,
                    description:description,
                    filters:filters
                },

                success:function(response){

                    window.location.href = 'contactflow_builder';
                },
                error :function( data ) {
                    $('.add_rule .alert').empty();
                    $('.add_rule .alert').hide();

                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {

                        if($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $('.add_rule .alert').show().append('<li>'+value+'</li>');
                            });
                        }else{
                            $('.add_rule .alert').show().append('<li>'+value+'</li>');
                        }
                    });

                    $('.add_rule .alert li').first().remove();
                }
            });
        }
    },

    updateleadrule:function(e){
        e.preventDefault();

        var rule_id = $('.rule_id').val(),
            rule_name = $('#rule_name').val(),
            source_campaign = $('#update_campaign_select').val(),
            source_subcampaign=$('.source_subcampaign').val();
            destination_campaign = $('#update_destination_campaign').val(),
            destination_subcampaign=$('.destination_subcampaign').val();
            description = $('#description').val()
        ;

        var filters={};
        $('.lead_rule_filter_type').each(function(){
            filters[$(this).val()] = $(this).parent().parent().find('.subfilter_group[data-subfilter="' + $(this).val() + '"]').find('.form-control ').val();
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/contactflow_builder/update_rule',
            type: 'POST',
            dataType: 'json',
            data: {
                id:rule_id,
                rule_name:rule_name,
                source_campaign:source_campaign,
                source_subcampaign:source_subcampaign,
                destination_campaign:destination_campaign,
                destination_subcampaign:destination_subcampaign,
                description:description,
                filters:filters
            },

            success:function(response){
                window.location.href = '/tools/contactflow_builder';
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

    toggle_leadrule:function(){
        var checked;
        var ruleid = $(this).parent().parent().parent().data('ruleid');

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
            url:'/tools/contactflow_builder/toggle_rule',
            type:'POST',
            data:{
                checked:checked,
                id:ruleid

            },
            success:function(response){
            }
        });
    },

    get_leadrule_details:function(e){
        e.preventDefault();
        var leadid = $(this).data('leadid');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/contactflow_builder/view_rule',
            type: 'POST',
            dataType: 'json',
            data: { id: leadid },
            success: function (response) {

                var modal = $('#leadDetailsModal');
                modal.find('.modal-body').empty();
                var leadrule_details;

                leadrule_details = '<h3 class="leaddetail_name">'+response.rule_name+'</h3>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.created')+':</span> <span class="leadrule_value">'+response.created_at+'</span></p>';

                if(response.deleted_at){
                    leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.deleted')+':</span> <span class="leadrule_value">'+response.deleted_at+'</span></p>';
                }

                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.source_campaign')+':</span> <span class="leadrule_value">'+response.source_campaign+'</span></p>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.source_subcampaign')+':</span> <span class="leadrule_value">'+response.source_subcampaign+'</span></p>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.destination_campaign')+':</span> <span class="leadrule_value">'+response.destination_campaign+'</span></p>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.destination_subcampaign')+':</span><span class="leadrule_value">'+response.destination_subcampaign+'</span></p>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.filter_type')+':</span> <span class="leadrule_value">'+response.filter_type+'</span></p>';
                leadrule_details += '<p class="lead_info"><span class="leadrule_property">'+Lang.get('js_msgs.filter_value')+':</span> <span class="leadrule_value">'+response.filter_value+'</span></p>';

                modal.find('.modal-body').append(leadrule_details);
            }
        });
    },

    hide_modal_error:function(){
        $(this).find('.modal-footer .alert').remove();
    },

    change_filter_label: function () {
        var filtertype = $(this).find('option:selected').data('filtertype');
        $(this).parent().parent().find('.subfilter_group').hide();
        var subfilter = $(this).parent().parent().find('.subfilter_group[data-subfilter="' + filtertype + '"]');
        $(subfilter).show();
    },

    add_leadrule_filter:function(e){
        e.preventDefault();

        if(Tools.leadrule_filters_used < Tools.leadrule_filters){
            $('.alert.filter_error').hide();
            var selected_filter = $(this).parent().find('.lead_rule_filter_type').val();
            var selected_value = $(this).parent().find('.subfilter_group[data-subfilter="' + selected_filter + '"] .form-control').val();

            if(selected_filter && selected_value){
                $(this).parent().parent().parent().find('.vertical-line').height(Master.flowchart_vline_height);

                if(Tools.leadrule_filters != Tools.leadrule_filters_used ){
                    // only add delete rule btn to edit form -check if only one condition is present
                    // if($(this).parent().parent().parent().parent().parent().attr('id') != 'add_rule'){
                    //     var add_delete_btn = true;
                    // }

                    Tools.leadrule_filters_used=Tools.leadrule_filters_used+1;
                    var new_filter = $(this).parent().parent().parent().clone();
                    $(new_filter).insertAfter('.leadfilter_row:last');
                    var i = Tools.leadrule_filters_used;
                    $(new_filter).find('.lead_rule_filter_value, .lead_rule_filter_type,.filter_value').val('');
                    $(new_filter).find('.flowchart_element span').text(Lang.get('js_msgs.and'));
                    $(new_filter).find('.lead_rule_filter_type').attr('id', 'filter_type'+i).attr('name', 'filter_type'+i);
                    $(new_filter).find('.lead_rule_filter_value').attr('id', 'filter_value'+i).attr('name', 'filter_value'+i);
                    /// only update filter menu for create rule form
                    // if(!$(this).hasClass('edit_addrule')){
                    //     $(new_filter).find('select.lead_rule_filter_type option[value="'+selected_filter+'"]').remove();
                    // }

                    if(Tools.leadrule_filters_used!=Tools.leadrule_filters){
                        if(!$(new_filter).find('a.remove_filter').length){
                            $(new_filter).find('.card').append('<a href="#" class="remove_filter flt_rgt"><i class="fas fa-trash-alt"></i> '+Lang.get('js_msgs.remove_filter')+'</a>');
                        }
                    }

                    if(Tools.leadrule_filters == Tools.leadrule_filters_used){
                        $(new_filter).find('a.add_leadrule_filter').remove();
                    }

                    // $(this).parent().find('select').attr('disabled', true);
                    $(this).hide();
                }
            }else{
                Master.flowchart_vline_height = $(this).parent().parent().parent().find('.vertical-line').height();
                $(this).parent().find('.alert').show();
                $(this).parent().parent().parent().find('.vertical-line').height(Master.flowchart_vline_height + 180);
            }
        }
    },

    remove_leadrule_filter:function(e){
        e.preventDefault();

        Tools.leadrule_filters_used=Tools.leadrule_filters_used-1;

        $(this).parent().parent().parent().remove();
        $('.update_filter_type').each(function(){
            $(this).attr('disabled', true);
        });
        //// disable all but last filter selects
        $('.update_filter_type').last().attr('disabled', false);

        $('.leadfilter_row').find('.card').each(function(){
            $(this).find('.add_leadrule_filter').remove();
        });
        // remove add new filter buttons from all cards, add to last one
        if(Tools.leadrule_filters_used != Tools.leadrule_filters){
            $('.leadfilter_row:last').find('.card').append('<a href="#" class="add_leadrule_filter edit_addrule"><i class="fas fa-plus-circle"></i> '+Lang.get('js_msgs.add_filter')+'</a>');
        }
    },

    populate_dnc_modal:function(){
        var id = $(this).data('id');
        $('#deleteDNCModal .modal-footer').find('.btn-danger').val('delete:'+id);
    },

    populate_dnc_reversemodal:function(){
        var id = $(this).data('id');
        $('#reverseDNCModal .modal-footer').find('.btn-danger').val('reverse:'+id);
    },

    toggle_instructions:function(e){

        if(e){
            e.preventDefault();
        }

        that = $('a.toggle_instruc');
        if(that.hasClass('collapsed')){
            that.removeClass('collapsed');
            that.empty().append('<i class="fas fa-angle-up"></i>');
        }else{
            that.addClass('collapsed');
            that.empty().append('<i class="fas fa-angle-down"></i>');
        }

        that.parent().find('.instuc_div').slideToggle();
    },
}

$(document).ready(function(){
	Tools.init();

	if($('.dnc_table tbody tr').length){
        Tools.toggle_instructions();
    }

    // remove add filter button if max filters in use
    if(Tools.leadrule_filters_used == Tools.leadrule_filters){
        $('a.add_leadrule_filter ').remove();
    }

    // increment ids for filters on edit form
    var x=2;
    $('.edit_ruleparent .leadfilter_row').each(function(i){
        if(i){
            $(this).find('.lead_rule_filter_type').attr('id', 'update_filter_type'+x).attr('name', 'filter_type'+x);
            $(this).find('#update_filter_value').attr('id', 'update_filter_value'+x).attr('name', 'filter_value'+x);
            x++;
        }
    });

    if(!$('.edit_rule #source_subcampaign').hasClass('insubcamp_menu')){
        $('.edit_rule #source_subcampaign').parent().hide();
        $('.edit_rule .new_source_subcampaign_group').show();
        $('.edit_rule .new_source_subcampaign_group').next().addClass('undo_new_subcampaign');
        $('.edit_rule .new_source_subcampaign_group').next().text('Select Existing Subcampaign');
    }

    if(!$('.edit_rule #destination_subcampaign').hasClass('insubcamp_menu')){
        $('.edit_rule #destination_subcampaign').parent().hide();
        $('.edit_rule .new_destination_subcampaign_group').show();
        $('.edit_rule .new_destination_subcampaign_group').next().addClass('undo_new_subcampaign');
        $('.edit_rule .new_destination_subcampaign_group').next().text('Select Existing Subcampaign');
    }

});

// populate dnc file upload name in input
$(document).on('change', ':file', function() {
    var label = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).trigger('fileselect', [label]);
  });

$(':file').on('fileselect', function(event, label) {
    $('.filename').text(label);
});