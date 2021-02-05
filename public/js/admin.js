var Admin = {

	init: function () {

		///// event handlers
		$('.add_user').on('submit', this.add_user);
		$('.edit_user').on('submit', this.edit_user);
		$('.add_demo_user').on('submit', this.add_demo_user);
		$('.edit_demo_user').on('submit', this.edit_demo_user);
		$('.edit_myself').on('submit', this.edit_myself);
		$('a.edit_demo_user').on('click', this.populate_demo_user_editmodal);
		$('.users').on('click', 'a.edit_user', this.populate_user_edit);
		$('.demo_user_modal_link').on('click', this.pass_user_demo_modals);
		$('#deleteUserModal .remove_recip').on('click', this.remove_user);
		$('.cdr_lookup_form').on('submit', this.cdr_lookup);

		// webhook handlers

        $('body').on('keyup', '.field .webhook_field_value', this.uncheck_macro);
		$('body').on('click', '.remove_field', this.remove_field);
		$('body').on('click', '.undo_remove_field', this.undo_remove_field);
		$('.add_custom_field').on('submit', this.add_custom_field);
		$('#webhook_generator #db').on('change', this.get_client_tables);
		$('#client_table').on('change', this.get_table_fields);
		$('body').on('click', '.use_system_macro', this.toggle_system_macro);
		$('body').on('focusin', '.field .form-group .form-control', this.highlight_custom_field);
		$('.generate_url').on('click', this.generate_url);
		$('.checkall_system_macro').on('click', this.toggleall_system_macro);
		$('body').on('dblclick', '.field_name', this.edit_field_name);
		$('.published').on('click', this.toggle_published_msg);
		$('.remove_msg_modal').on('click', this.populate_msg_delete_modal);
		$('.delete_msg').on('click', this.delete_msg);

		$('.edit_sms_modal, .delete_sms_modal').on('click', this.populate_sms_modal);
		$('.add_sms_number').on('submit', this.add_sms_number);
		$('.edit_sms_number').on('submit', this.update_sms_number);
		$('.delete_sms_number').on('submit', this.delete_sms_number);
		$('.delete_file').on('click', this.populate_delete_file_modal);
	},

	// add global user
	add_user: function (e) {
		e.preventDefault();

		var form_data = $(this).serialize();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'add_user',
			type: 'POST',
			dataType: 'json',
			data: form_data,

			success: function (response) {

				$('form.add_user .alert-success').html('User successfully added').show();
				$('form.add_user .alert-danger').empty().hide();
				setTimeout(function () {
					$('form.add_user .alert-success').hide();
					$('form.add_user').trigger("reset");
					window.location.reload();
				}, 2500);
			}, error: function (data) {
				$('form.add_user .alert-danger').empty();

				var errors = $.parseJSON(data.responseText);
				$.each(errors, function (key, value) {

					if ($.isPlainObject(value)) {
						$.each(value, function (key, value) {
							$('form.add_user .alert-danger').show().append('<li>' + value + '</li>');
						});
					} else {
						$('form.add_user .alert-danger').show().append('<li>' + value + '</li>');
					}
				});

				$('form.add_user .alert li').first().remove();

			}
		});
	},

	// edit global user
	edit_user: function (e) {
		e.preventDefault();
		var form = $('form.edit_user');
		var form_data = $(this).serialize();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'update_user',
			type: 'POST',
			dataType: 'json',
			data: form_data,

			success: function (response) {
				$('form.edit_user .alert-success').html('User successfully updated').show();
				$('form.edit_user .alert-danger').hide();

				setTimeout(function () {
					$('form.edit_user .alert-success').hide();
					$('form.edit_user').trigger("reset");
					window.location.reload();
				}, 2500);
			}, error: function (data) {
				$('form.edit_user .alert-danger').empty();

				var errors = $.parseJSON(data.responseText);
				$.each(errors, function (key, value) {

					if ($.isPlainObject(value)) {
						$.each(value, function (key, value) {
							$('form.edit_user .alert-danger').show().append('<li>' + value + '</li>');
						});
					} else {
						$('form.edit_user .alert-danger').show().append('<li>' + value + '</li>');
					}
				});

				$('form.add_user .alert li').first().remove();
			}
		});
	},

	// add demo user
	add_demo_user: function (e) {
		e.preventDefault();
		var form = $('form.add_demo_user');
		var name = form.find('.name').val(),
			email = form.find('.email').val(),
			phone = form.find('.phone').val(),
			expiration = form.find('#expiration').val()
			;

		$('.alert-danger').hide();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'add_demo_user',
			type: 'POST',
			dataType: 'json',
			data: {
				id: form.parent().find('.demouser_id').val(),
				name: name,
				email: email,
				phone: phone,
				expiration: expiration
			},

			success: function (response) {

				$('.add_demo_user .alert-success').html('User successfully updated').show();
				setTimeout(function () {
					$('.add_demo_user .alert-success').hide();
					$('form.add_demo_user').trigger("reset");
					location.reload();
				}, 2500);
			}, error: function (data) {
				$('form.add_demo_user .alert').empty();

				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('form.add_demo_user .alert-danger').show().append('<li>' + value + '</li>');
							});
						} else {
							$('form.add_demo_user .alert-danger').show().append('<li>' + value + '</li>');
						}
					});

					$('form.add_demo_user .alert-danger li').first().remove();
				}
			}
		});
	},

	// edit demo user
	edit_demo_user: function (e) {
		e.preventDefault();

		var form = $('form.edit_demo_user');
		var name = form.find('.name').val(),
			email = form.find('.email').val(),
			phone = form.find('.phone').val(),
			expiration = form.find('#expiration').val()
			;

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'update_demo_user ',
			type: 'POST',
			dataType: 'json',
			data: {
				id: form.parent().find('.demouser_id').val(),
				name: name,
				email: email,
				phone: phone,
				expiration: expiration
			},

			success: function (response) {
				$('form.edit_demo_user .alert-success').html('User successfully updated').show();
				$('.edit_demo_user .alert-danger').hide();
				setTimeout(function () {
					$('.edit_demo_user .alert-success').hide();
					$('#demoUserModal').modal('hide');
					window.location.reload();
				}, 2500);
			},
			error: function (data) {
				$('form.edit_demo_user .alert-danger').empty();

				var errors = $.parseJSON(data.responseText);
				$.each(errors, function (key, value) {

					if ($.isPlainObject(value)) {
						$.each(value, function (key, value) {
							$('form.edit_demo_user .alert-danger').show().append('<li>' + value + '</li>');
						});
					} else {
						$('form.edit_demo_user .alert-danger').show().append('<li>' + value + '</li>');
					}
				});

				$('form.edit_demo_user .alert-danger li').first().remove();
			}
		});
	},

	populate_demo_user_editmodal: function (e) {
		e.preventDefault();
		var user_id = $(this).data('user');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'get_user',
			type: 'POST',
			dataType: 'json',
			data: { id: user_id, mode: 'edit' },
			success: function (response) {

				var modal = $('.edit_demo_user');
				$('form.demo_user .alert.alert-info').remove();
				$(modal).find('.name').val(response.name);
				$(modal).find('.email').val(response.email);
				$(modal).find('.phone').val(response.phone);
				var demo_expiration = $('.edit_demo_user').find('.name').parent();
				$('<div class="alert alert-info mb20">Demo expires ' + response.expires_in + '</div>').insertBefore(demo_expiration);
			}
		});
	},

	populate_user_edit: function (e) {

		e.preventDefault();
		$('ul.nav-tabs a[href="#edit_user"]').tab('show');
		var user_id = $(this).attr('href');
		var dialer = $(this).data('dialer');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'get_user',
			type: 'POST',
			dataType: 'json',
			data: { id: user_id },
			success: function (response) {
				console.log(response);
				$('html,body').scrollTop($('body').scrollTop());

				$('#edit_dialer' + dialer).addClass('in');
				$('#edit_dialer' + dialer).attr('aria-expanded', true);
				$('#edit_heading' + dialer + ' h4 a').attr('aria-expanded', true);
				var form = $('form.edit_user');
				form.find('.group_id').val(response.group_id);
				form.find('.name').val(response.name);
				form.find('.email').val(response.email);
				form.find('.phone').val(response.phone);
				form.find('#tz').val(response.tz);
				form.find('#phone').val(response.phone);
				form.find('#user_type').val(response.user_type);
				form.find('#user_type').val(response.user_type);
				form.find('#db').val(response.dialer_id);
				form.find('#additional_dbs').val(response.additional_dbs);
				form.find('#user_id').val(response.id);
			}
		});
	},

	// pass user id to edit/delete demo user modals
	pass_user_demo_modals: function (e) {
		e.preventDefault();
		var id = $(this).data('user');
		var name = $(this).data('name');
		var modal = $(this).data('target');
		$(modal).find('.demouser_id').val(id);
		$(modal).find('.demouser_name').val(name);
		$(modal).find('span.username').html(name);
	},

	// remove global/demo users
	remove_user: function (e) {
		e.preventDefault();
		var id = $('#deleteUserModal .user_id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'delete_user',
			type: 'POST',
			dataType: 'json',
			data: {
				id: id
			},
			success: function (response) {
				// either traverse up to number of clients and update or simply redirect back
				$('.users table tbody tr#user' + id).remove();
				$('.demo_user_table tbody tr#user' + id).remove();
				$('#deleteUserModal').modal('toggle');
			}
		});
	},

	edit_myself: function (e) {
		e.preventDefault();
		var form = $('form.edit_myself');
		var group_id = form.find('.group_id').val(),
			user_id = form.find('.user_id').val(),
			dialer_id  = form.find('#dialer_id ').val(),
			tz = form.find('#tz').val()
		;

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'edit_myself',
			type: 'POST',
			dataType: 'json',
			data: {
				id: user_id,
				group_id: group_id,
				dialer_id : dialer_id ,
				tz:tz
			},

			success: function (response) {
				$('.form.edit_myself .btn.add_btn_loader').find('i').remove();
				if (response.errors) {
					$('form.edit_myself').append('<div class="alert alert-danger">' + response.errors + '</div>');
					$('.alert-danger').show();
				} else {
					$('.alert-success').remove();
					$('form.edit_myself').append('<div class="alert alert-success">User successfully updated</div>');
					$('.alert-success').show();
					setTimeout(function () {
						$('.alert-success').hide();
						window.location.reload();
					}, 2500);
				}
			}
		});
	},

	cdr_lookup: function (e) {
		e.preventDefault();
		$('.preloader').show();
		var phone = $('#phone').val(),
			fromdate = $('.fromdate').val(),
			todate = $('.todate').val(),
			search_type = $("input[name='search_type']:checked").val()
			;

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'cdr_lookup',
			type: 'POST',
			dataType: 'json',
			data: {
				phone: phone,
				fromdate: fromdate,
				todate: todate,
				search_type: search_type
			},
			success: function (response) {
				
				$('.report_filters.card').parent().find('.alert').remove();
				$('.cdr_results_table tbody').empty();

				if ($('#sidebar').hasClass('active')) {
					$('#sidebar').removeClass('active');
				}

				if (response.search_result.length) {

					$('.cdr_table').show();

					var _data = response.search_result;
					var trs = [];
					var array_keys = [], array_values = [];
					for (i = 0; i < _data.length; i++) {
						array_keys = [];
						array_values = [];
						for (var key in _data[i]) {
							array_keys.push(key);
							array_values.push(_data[i][key]);
						}
						trs.push(array_values);
					}

					var ths = "";
					for (var i = 0; i < array_keys.length; i++) {
						ths += "<th>" + array_keys[i] + "</th>";
					}
					$('#cdr_dataTable thead').html(ths);
					Master.cdr_dataTable.clear();
					Master.cdr_dataTable.rows.add(trs);
					Master.cdr_dataTable.draw();

				} else {
					$('.cdr_table').hide();
					$('<div class="alert alert-danger">No records found</div>').insertAfter('.report_filters.card')
				}

				$('.preloader').fadeOut('slow');
			}
		});
	},

	remove_field: function (e) {
		e.preventDefault();
		$(this).find('i').remove();
		$(this).append('<i class="fas fa-undo-alt"></i>');
		$(this).removeClass('remove_field');
		$(this).addClass('undo_remove_field');
		$(this).parent().parent().find('p.field_name').removeClass('active');
		$(this).parent().parent().addClass('field_removed');
		$(this).parent().parent().find('input.form-control').addClass('disabled');
		$(this).parent().parent().find('input.form-control, input.use_system_macro').attr('disabled', true);
	},

	undo_remove_field: function (e) {
		e.preventDefault();
		$(this).find('i').remove();
		$(this).append('<i class="fas fa-times-circle"></i>');
		$(this).removeClass('undo_remove_field');
		$(this).addClass('remove_field');
		$(this).parent().parent().removeClass('field_removed');
		$(this).parent().parent().find('input.form-control').removeClass('disabled');
		$(this).parent().parent().find('input.form-control, input.use_system_macro').attr('disabled', false);
	},

	add_custom_field: function (e) {
		e.preventDefault();

		var custom_field_name = $('.custom_field_name').val();
		var custom_field_value = $('.custom_field_value').val();
		console.log(custom_field_name);
		var new_field_row = '<div class="field"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-4"><p class="field_name" data-field="' + custom_field_name + '">' + custom_field_name + '</p></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" name="' + custom_field_name + '" value="' + custom_field_value + '"></div></div><div class="col-sm-2"><label class="checkbox-inline"><input class="use_system_macro" type="checkbox" value="">Use System Macro</label></div></div>';

		$(new_field_row).insertAfter('.field:last');
		$(this).trigger("reset");
	},

	get_client_tables: function () {

		$('.alert-danger').hide();
		var database = $(this).val();
		var group_id = $(this).parent().parent().find('#group_id').val();
		console.log(database +' '+group_id);
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/admin/get_client_tables',
			type: 'POST',
			dataType: 'json',
			data: { group_id: group_id, database: database },
			success: function (response) {
				$('#client_table').empty();
				if (response.tables.length) {
					var tables = '<option value="">Select One</option>';
					for (var i = 0; i < response.tables.length; i++) {
						tables += '<option value="' + response.tables[i].TableName + '">' + response.tables[i].TableName + ' - ' + response.tables[i].Description + '</option>';
					}

					$('#client_table').append(tables);
				} else {
					$('.alert-danger').text('No Tables Found').show();
				}
			}
		});
	},

	get_table_fields: function () {
		var table_name = $(this).val();
		var database = $(this).parent().parent().find('#db').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/admin/get_table_fields',
			type: 'POST',
			dataType: 'json',
			data: { table_name: table_name, database: database },
			success: function (response) {
				$('.field_from_table').remove();
				if (response.fields.length) {
					var new_field_row = '';
					for (var i = 0; i < response.fields.length; i++) {
						new_field_row += '<div class="field field_from_table"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-4"><p class="field_name" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" name="' + response.fields[i] + '" placeholder="' + response.fields[i] + '"></div></div><div class="col-sm-2"><label class="checkbox-inline"><input class="use_system_macro" type="checkbox" value="">Use System Macro</label></div></div>';
					}
					$(new_field_row).insertAfter('.field:last');
				}
			}
		});
	},

	toggle_system_macro: function (el) {
		if (el.type == 'click') { el = $(this); }

		var system_value = el.parent().parent().prev().prev().find('p').data('field');

		if (el.is(':checked')) {
			el.parent().parent().prev().find('input.form-control').val('(#' + system_value + '#)');
		} else {
			el.parent().parent().prev().find('input.form-control').val('');
		}
	},

	toggleall_system_macro: function () {
		if ($(this).prop("checked") == true) {
			$(this).parent().find('span').text('Uncheck All Macros');
			$('.use_system_macro').each(function () {
				$(this).prop("checked", true);
			});
		} else {
			$(this).parent().find('span').text('Check All Macros');
			$('.use_system_macro').each(function () {
				$(this).prop("checked", false);
			});
		}

		$('.field .use_system_macro').each(function () {
			Admin.toggle_system_macro($(this));
		});
	},

	edit_field_name: function () {

		var edit_input = $('<input class="form-control" name="temp" type="text" />');
		var elem = $(this);

		elem.hide();
		elem.after(edit_input);
		edit_input.focus();

		edit_input.blur(function () {

			if ($(this).val() != '') {
				elem.text($(this).val());
			}

			$(this).remove();
			elem.show();
		});
	},

	highlight_custom_field: function () {
		$('p').removeClass('active');
		$(this).parent().parent().parent().find('p').addClass('active');
	},

	generate_url: function () {
		var posting_url = $('#posting_url').val();
		var final_url = posting_url + "?";

		var i = 0;
		$('.field').each(function () {
			if (!$(this).hasClass('field_removed')) {
				var field_name = $(this).find('p.field_name').text();
				var field_value = $(this).find('.form-control').val();
				field_name=field_name.trim();
				field_value=field_value.trim();
				if(!$(this).find('.use_system_macro').is(':checked')){
					field_value=field_value.replace(/ /g,"%20");
				}
				field_name=field_name.replace(/ /g,"%20");

				if(!i){
					final_url+= field_name+'='+field_value;
				}else{
					final_url+='&'+field_name+'='+field_value;
				}

				i++;
			}
		});

		$('.final_url_cnt .url').text(final_url)
	},

	uncheck_macro:function(e){
		e.preventDefault();
		if($(this).parent().parent().parent().find('.use_system_macro').is(":checked")){
			$(this).parent().parent().parent().find('.use_system_macro').prop('checked', false);
		}
	},

	toggle_published_msg:function(){

        var active=0;
        var id = $(this).data('id');

        if($(this).is(':checked')){
            active=1;
        }else{
            active =0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/admin/publish_notification',
            type:'POST',
            data:{
                id:id,
                active:active
            },
            success:function(response){
                console.log(response);
            }
        });
    },

    populate_msg_delete_modal:function(e){
    	e.preventDefault();
    	var id = $(this).data('id');
    	var title = $(this).data('title');
    	$('#deleteMsgModal').find('input.id').val(id);
    	$('#deleteMsgModal').find('h3 span.title').text(title);
    },

    delete_msg:function(e){
    	e.preventDefault();
    	var id = $('#deleteMsgModal .id').val();

    	$.ajaxSetup({
    		headers: {
    			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    		}
    	});

    	$.ajax({
    		url: '/admin/delete_msg',
    		type: 'POST',
    		dataType: 'json',
    		data: {
    			id: id
    		},
    		success: function (response) {
    			window.location= "/admin/notifications";
    		}
    	});
    },

    populate_sms_modal:function(e){
    	e.preventDefault();
		var id = $(this).data('id');
		Master.pass_id_to_modal(this, id);

    	if($(this).data('target').substring(1) == 'editSMSModal'){
    		Admin.edit_sms_number(id);
    	}else{
    		var modal = $(this).data('target');
    		$(modal).find('h3 span').text($(this).data('number'));
    	}
    },

    add_sms_number:function(e){
    	e.preventDefault();
    	$('.loader_hor').show();
		var form_data = $(this).serialize();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/playbook/sms_number',
			type: 'POST',
			dataType: 'json',
			data: form_data,
			success: function (response) {

				if(response.status == 'success'){
					location.reload();
				}
				$('.loader_hor').hide();
			}, error: function (data) {
				if (data.status === 422) {
					console.log(data);
					$('.add_sms_number .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.add_sms_number .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.add_btn_loader i').remove();
						$('.add_sms_number .alert-danger').show();
					});

					$('.loader_hor').hide();
				}
			}
		});
    },

    edit_sms_number:function(id){

    	$('.loader_hor').show();

    	$.ajaxSetup({
    		headers: {
    			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    		}
    	});

    	$.ajax({
    		url: '/playbook/sms_number/'+id,
    		type: 'GET',
    		dataType: 'json',
    		success: function (response) {

    			$('#editSMSModal').find('.group_id').val(response.group_id);
    			$('#editSMSModal').find('.from_number').val(response.from_number);
    			$('.loader_hor').hide();
    		}
    	});
    },

    update_sms_number:function(e){
    	e.preventDefault();
    	$('.loader_hor').show();
		var form_data = $('.edit_sms_number').serialize();
		var id = $('.edit_sms_number').find('.id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/playbook/sms_number/'+id,
			type: 'PATCH',
			dataType: 'json',
			data: form_data,
			success: function (response) {
				if(response.status == 'success'){
					location.reload();
				}
				$('.loader_hor').hide();
			}, error: function (data) {
				if (data.status === 422) {
					$('.edit_sms_number .alert-danger').empty();
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('.edit_sms_number .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('.add_btn_loader i').remove();
						$('.edit_sms_number .alert-danger').show();
					});

					$('.loader_hor').hide();
				}
			}
		});
    },

    delete_sms_number:function(e){
    	e.preventDefault();
    	$('.loader_hor').show();
		var id = $(this).find('.id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/playbook/sms_number/'+id,
			type: 'DELETE',
			data: {
				id:id
			},
			success: function (response) {
				if(response.status == 'success'){
					location.reload();
				}
				$('.loader_hor').hide();
			}, error: function (data) {
				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('#deleteSMSModal .alert-danger').append('<li>' + value + '</li>');
							});
						}

						$('#deleteSMSModal .alert-danger').show();
					});
				}
			}
		});
    },

    populate_delete_file_modal:function(){
        var id = $(this).data('id');
        $('#deleteFileModal .modal-body').find('h3 span').text(id);
        $('#deleteFileModal .modal-footer').find('.btn-danger').val('delete:'+id);
    },

}

$(document).ready(function () {
	Admin.init();

	// lead number 236441

	$('#editSMSModal, #addSMSModal').on('hidden.bs.modal', function () {
	    $(this).find('.alert').hide();
	    $('.add_sms_number').trigger("reset");
	    $('.edit_sms_number').trigger("reset");
	});

	$( "#addSMSModal" ).on('shown.bs.modal', function(){
		if($('.from_number').val() == ''){
			$('.from_number').val('+1');
			$('.from_number').focus();
		}
	});
});

// populate file upload name in input
$(document).on('change', ':file', function() {
    var label = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).trigger('fileselect', [label]);
  });

$(':file').on('fileselect', function(event, label) {
    $('.filename').text(label);
});