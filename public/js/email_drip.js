var EmailDrip = {

	init:function(){
		$('.add_esp').on('submit', this.add_esp);
        $('.edit_server_modal').on('click', this.edit_server_modal);
        $('.edit_esp').on('submit', this.update_esp);
        $('.test_connection').on('click', this.test_connection);
        $('.remove_email_service_provider_modal, .remove_campaign_modal').on('click', this.populate_delete_modal);
        $('.delete_email_service_provider').on('click', this.delete_esp);
        $('.create_campaign_form').on('submit', this.create_email_campaign);
        $('.drip_campaigns_campaign_menu').on('change', this.get_email_drip_subcampaigns);
        $('.delete_campaign ').on('click', this.delete_campaign);
        $('.provider_type').on('change', this.get_provider_properties);
        $('.add_email_campaign_filter').on('click', this.validate_filter);
        $('.filter_fields_div .form-control').on('change', this.validate_filter);
        $('.update_filters').on('submit', this.update_filters);
        $('.switch.email_campaign_switch input').on('click', this.check_campaign_filters);
        $('.filter_fields_cnt').on('click', '.remove_camp_filter', this.delete_camp_filter);
        $('.camp_filters_link').on('click', this.goto_camp_filters);
        $('.filter_fields_cnt').on('change', '.filter_fields', this.get_operators);
        $('.cancel_modal_form').on('click', this.cancel_modal_form);
	},

	add_esp:function(e){
	    e.preventDefault();

	    var form_data = $(this).serialize();

	    $('.alert').empty().hide();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/add_esp',
	        type: 'POST',
	        data: form_data,
	        success: function (response) {
	            location.reload();
	        },error: function (data) {
	            if (data.status === 422) {
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {

	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                            $('.add_esp .alert-danger').append('<li>'+value+'</li>');
	                        });
	                    }

	                    $('.add_esp .alert-danger').show();
	                });
	            }
	        }
	    });
	},

	edit_server_modal:function(e){
	    e.preventDefault();

	    var id = $(this).data('serverid');

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/get_esp',
	        type: 'POST',
	        data: {
	            id: id,
	        },
	        success: function (response) {

	            $('#editESPModal .name').val(response.name);
	            $('#editESPModal .provider_type').val(response.provider_type);
	            $('#editESPModal .id').val(response.id);
	            $('#editESPModal .properties').empty();
	            var property_inputs='';

	            const entries = Object.entries(response.properties)
	            for (const [key, value] of entries) {
	                var label = key.charAt(0).toUpperCase() + key.slice(1);
	                property_inputs+='<div class="form-group"><label>'+label+'</label><input type="text" class="form-control '+key+'" name="properties['+key+']" value="'+value+'" required></div>';
	            }

	            $('#editESPModal .properties').append(property_inputs);
	        }
	    });
	},

	update_esp:function(e){
	    e.preventDefault();
	    var form_data = $(this).serialize();

	    $('.alert').empty().hide();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/update_esp',
	        type: 'POST',
	        data:form_data,
	        success: function (response) {
	            $(this).find('i').remove();
	            location.reload();
	        },error: function (data) {
	            $(this).find('i').remove();
	            if (data.status === 422) {
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {

	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                            $('.edit_smtp_server .alert-danger').append('<li>'+value+'</li>');
	                        });
	                    }

	                    $('.edit_smtp_server .alert-danger').show();
	                });
	            }
	        }
	    });
	},

	test_connection:function(e){
	    e.preventDefault();

	    $('.alert').empty().hide();

	    var that = $(this).parent();
	    var form_data = $(that).serialize();
	    $.ajax({
	        url: '/tools/email_drip/test_connection ',
	        type: 'POST',
	        data: form_data,
	        success: function (response) {

	            $(that).find('.test_connection').find('i').remove();
	            $(that).find('.connection_msg').removeClass('alert-danger alert-success');
	            $(that).find('.connection_msg').addClass('alert-success').text(response.message).show();
	        },error: function (data) {
	            $('.test_connection').find('i').remove();

	            if (data.status === 422) {
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {

	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                            $(that).find('.connection_msg').append('<li>'+value+'</li>');
	                            $(that).find('.connection_msg').addClass('alert-danger').show();
	                        });
	                    }
	                });
	            }
	        },statusCode: {
	            500: function(response) {
	                $(that).find('.alert-danger').text('Connection Failed').show();
	            }
	        }
	    });
	},

	populate_delete_modal:function(e){
	    e.preventDefault();
	    var id = $(this).data('id'),
	        name = $(this).data('name'),
	        sel = $(this).data('target')
	    ;

	    $(sel+' h3').find('span').text(name);
	    $(sel+' #id').val(id);
	},

	delete_esp:function(e){
	    e.preventDefault();
	    var id = $('#deleteESPModal').find('#id').val();
	    $('#deleteESPModal .alert-danger').hide();
	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/delete_esp',
	        type: 'POST',
	        data: {
	            id: id,
	        },
	        success: function (response) {
	            location.reload();
	        },error: function (data) {
	            $('#deleteESPModal .btn').find('i').remove();
	            if (data.status === 422) {
	                $('#deleteESPModal .alert-danger').empty();
	                // $('#deleteESPModal .btn').find('i').remove();
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {
	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                            $('#deleteESPModal .alert-danger').append('<li>'+value+'</li>');
	                        });
	                    }
	                    $('#deleteESPModal .alert-danger').show();
	                });
	            }
	        }
	    });
	},

	create_email_campaign:function(e){
	    e.preventDefault();

	    var form_data = $(this).serialize();
	    ;

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/add_campaign',
	        type: 'POST',
	        data:form_data,
	        success: function (response) {
	            $('.create_campaign ').find('i').remove();
	            window.location.href = '/tools/email_drip/update_filters/'+response.email_drip_campaign_id;
	        },error: function (data) {
	            $('.create_campaign ').find('i').remove();
	            if (data.status === 422) {
	                $('.create_campaign_form .alert').empty();
	                $('.create_campaign_form .btn').find('i').remove();
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {

	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                            $('.create_campaign_form .alert-danger').append('<li>'+value+'</li>');
	                        });
	                    }

	                    $('.create_campaign_form .alert-danger').show();
	                });
	            }
	        }
	    });
	},

	get_email_drip_subcampaigns:function(e, campaign){

	    var sel;
	    if(e.type=='click'){
	        sel = $('.edit_campaign_form');
	        campaign = $('.edit_campaign_form').find('.drip_campaigns_campaign_menu').val();
	    }else{
	        if($(e.target).parent().parent().hasClass('edit_campaign_form')){
	            sel = $('.edit_campaign_form');
	            campaign = $(this).val();
	        }else{
	            campaign = $(this).val();
	            sel = $('.create_campaign_form');
	        }
	    }

	    var subcamp_response = Master.get_subcampaigns(campaign, '/tools/email_drip/get_subcampaigns');
	    $('.drip_campaigns_subcampaign').empty();
	    $(sel).find('.email').empty();
	    console.log(subcamp_response);
	    var subcamp_obj = subcamp_response.responseJSON.subcampaigns;
	    var subcamp_obj_length = Object.keys(subcamp_obj).length;
	    const subcamp_obj_keys = Object.getOwnPropertyNames(subcamp_obj);
	    let subcampaigns_array = [];
	    subcampaigns_array.push(Object.values(subcamp_obj));

	    $('.drip_campaigns_subcampaign').empty();

	    var subcampaigns='';
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
	        url: '/tools/email_drip/get_table_fields' ,
	        type: 'POST',
	        dataType: 'json',
	        async:false,
	        data: {
	            campaign: campaign,
	        },

	        success: function(response) {

	            var emails='<option value="">Select One</option>';
	            for(var index in response) {
	                emails+='<option value="'+index+'">'+index+'</option>';
	            }

	            $(sel).find('.email').append(emails);
	        },
	    });
	},

	delete_campaign:function(e){
	    e.preventDefault();
	    var id = $('#deleteCampaignModal').find('#id').val();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/email_drip/delete_campaign',
	        type: 'POST',
	        data: {
	            id: id,
	        },
	        success: function (response) {
	            location.reload();
	        }
	    });
	},

	get_provider_properties:function(e){
	    var provider_type = $(this).val();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    if(provider_type !=''){
	        $.ajax({
	            url: '/tools/email_drip/get_properties',
	            type: 'POST',
	            data: {
	                provider_type: provider_type,
	            },
	            success: function (response) {
	                $('.properties').empty();
	                var properties='';

	                response.forEach(function(item, index){
	                    var label = item.charAt(0).toUpperCase() + item.slice(1);
	                    properties+='<div class="form-group"><label>'+label+'</label><input type="text" class="form-control '+item+'" name="properties['+item+']" value="" required></div>';
	                });

	                $('.properties').append(properties);
	            }
	        });
	    }
	},

	validate_filter:function(e){
	    e.preventDefault();
	    $('.filter_error').empty().hide();
	    var filters = [];
	    var email_drip_campaign_id = $('#email_drip_campaign_id').val();
	    var ready_to_validate=false;

	    // filter value changed ! in last row
	    if(!$(this).parent().hasClass('not_validated_filter') && e.type == 'change' && $('.filter_fields_div').length > 1){
	        $(this).parent().parent().parent().find('.form-control').each(function(){
	            filters.push($(this).val());
	        });

	        if(filters.length >=2){
	            ready_to_validate=true;
	        }
	    }else if( e.type == 'click'){ // add filter was clicked
	        if($('.filter_fields_div').length == 1 && $('.filter_fields_div').is(":hidden")){
	            $('.filter_fields_div').show();
	        }else{
	            $('.filter_fields_div').each(function(){
	                $(this).removeClass('not_validated_filter');
	            });

	            var new_filter_row = $(this).parent().parent().parent().find('.filter_fields_div').last().clone().addClass('not_validated_filter');

	            $('.filter_fields_div:last').find('.form-control').each(function(){
	                filters.push($(this).val());
	            });
	            ready_to_validate=true;
	        }
	    }

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    if(ready_to_validate){
	        $.ajax({
	            url: '/tools/email_drip/validate_filter',
	            type: 'POST',
	            data: {
	                email_drip_campaign_id:email_drip_campaign_id,
	                filters: filters,
	            },
	            success: function (response) {
	                $(new_filter_row).find('.form-control').each(function(){
	                    $(this).val('');
	                });
	                $(new_filter_row).find('.remove_camp_filter').show();
	                $(new_filter_row).insertAfter('.filter_fields_div:last');
	            },error: function (data) {
	                if (data.status === 422) {
	                    var errors = $.parseJSON(data.responseText);
	                    $.each(errors, function (key, value) {

	                        if ($.isPlainObject(value)) {
	                            $.each(value, function (key, value) {
	                                $('.filter_error').append('<li>'+value+'</li>');
	                            });
	                        }

	                        $('.filter_error').show();
	                    });
	                }
	            }
	        });
	    }
	},

	update_filters:function(e){
	    e.preventDefault();

	    $('.update_filters .alert.filter_error').empty().hide();
	    var email_drip_campaign_id = $(this).find('#email_drip_campaign_id').val();
	    var filters=[];
	    var filter={};

	    $('.filter_fields_div').each(function(){
	        $(this).find('.form-control').each(function(){
	            filter[$(this).data('type')] = $(this).val();
	        });

	        filters.push(filter);
	        filter={};
	    });

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url:'/tools/email_drip/update_filters',
	        type:'POST',
	        data:{
	            email_drip_campaign_id:email_drip_campaign_id,
	            filters:filters
	        },
	        success:function(response){
	            if(response.status=='success'){
	                window.location.href = '/tools/email_drip/';
	            }
	        },error: function (data) {
	            if (data.status === 422) {
	                var errors = $.parseJSON(data.responseText);
	                $.each(errors, function (key, value) {

	                    if ($.isPlainObject(value)) {
	                        $.each(value, function (key, value) {
	                           $('.update_filters .alert.filter_error').append('<li>'+value+'</li>');
	                        });
	                    }

	                    $('.update_filters .alert.filter_error').show();
	                });
	            }
	        }
	    });
	},

	check_campaign_filters:function(e){
	    var campaign_id = $(this).data('id');
	    if($(this).parent().hasClass('needs_filters')){
	        $('#errorModal').modal('show');
	        $('#errorModal .modal-body .camp_id').val(campaign_id);
	        return false;
	    }else{
	        EmailDrip.toggle_email_campaign(e, campaign_id);
	    }
	},

	delete_camp_filter:function(e){
	    e.preventDefault();

	    var id = $(this).parent().parent().data('filterid');
	    var that = $(this);

	    if(!id){
	        if($('.filter_fields_div').length == 1){
	            $(this).parent().parent().find('.form-control').each(function(){
	                $(this).val('');
	            });
	            $(this).parent().parent().hide();
	        }else{
	            $(this).parent().parent().remove();
	        }
	    }else{
	        $.ajaxSetup({
	            headers: {
	                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	            }
	        });

	        $.ajax({
	            url: '/tools/email_drip/delete_filter',
	            type: 'POST',
	            data: {
	                id:id,
	            },
	            success: function (response) {
	                $(that).parent().parent().remove();
	            }
	        });
	    }
	},

	goto_camp_filters:function(e){
	    e.preventDefault();
	    var id = $(this).next('.camp_id').val();
	    window.location.href = '/tools/email_drip/update_filters/'+id;
	},

	get_operators:function(){
	    var that = $(this);
	    var type = $(that).find('option:selected').data('type');
	    $('.filter_error').hide();

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url:'/tools/email_drip/get_operators',
	        type:'POST',
	        data:{
	            type:type,
	        },
	        success:function(response){
	            $(that).parent().parent().next().find('.filter_operators').empty();
	            var operators='<option value="">Select One</option>';

	            for (let [key, value] of Object.entries(response[type])){
	                operators+='<option value="'+key+'">'+value+'</option>';
	            }
	            $(that).parent().parent().next().find('.filter_operators').append(operators);

	            $('.filter_fields_cnt').show();
	        }
	    });
	},

	cancel_modal_form:function(e){
	    e.preventDefault();
	    $(this).parent().parent().find('.form')[0].reset()
	},

	toggle_email_campaign:function(e,campaign_id){

	    var checked;
	    // var campaign_id = $(campaign_id).data('id');

	    if($(campaign_id).is(':checked')){
	        $(campaign_id).attr('Checked','Checked');
	        checked=1;
	    }else{
	        $(campaign_id).removeAttr('Checked');
	        checked=0;
	    }

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url:'/tools/email_drip/toggle_email_campaign',
	        type:'POST',
	        data:{
	            checked:checked,
	            id:campaign_id

	        },
	        success:function(response){
	            console.log(response);
	        }
	    });
	},
}

$(document).ready(function(){
	EmailDrip.init();
});