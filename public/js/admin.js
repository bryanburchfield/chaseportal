var Admin = {

	init:function(){
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
	},

	// add global user
	add_user: function (e) {
		e.preventDefault();

		var group_id = $('.group_id').val(),
			name = $('.name').val(),
			email = $('.email').val(),
            phone = $('#phone').val(),
			tz = $('#tz').val(),
			db = $('#db').val(),
			additional_dbs = $('#additional_dbs').val()
			;

		var dialer = db.slice(-2);

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'admin/add_user',
			type: 'POST',
			dataType: 'json',
			data: {
				group_id: group_id,
				name: name,
				email: email,
                phone:phone,
				tz: tz,
				db: db,
				additional_dbs: additional_dbs
			},

			success: function (response) {

				$('form.add_user').append('<div class="alert alert-success mt20">User successfully added</div>');
                setTimeout(function () {
                    $('.alert').remove();
                    $('form.add_user').trigger("reset");
                    window.location.href = "/dashboards/admin";
                }, 3500);
			}, error: function (data) {
                $('form.add_user .alert').empty();

                var errors = $.parseJSON(data.responseText);
                $.each(errors, function (key, value) {

                    if ($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                            $('form.add_user .alert').show().append('<li>' + value + '</li>');
                        });
                    } else {
                        $('form.add_user .alert').show().append('<li>' + value + '</li>');
                    }
                });

                $('form.add_user .alert li').first().remove();
            }
		});
	},

	// edit global user
	edit_user:function(e){
		e.preventDefault();
		var form = $('form.edit_user');
		var group_id = form.find('.group_id').val(),
			user_id = form.find('#user_id').val(),
			name = form.find('.name').val(),
			email = form.find('.email').val(),
            phone = form.find('#phone').val(),
			tz = form.find('#tz').val(),
			db = form.find('#db').val(),
			additional_dbs = form.find('#additional_dbs').val()
			;

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'admin/update_user',
			type: 'POST',
			dataType: 'json',
			data: {
				id: user_id,
				group_id: group_id,
				name: name,
				email: email,
                phone:phone,
				tz: tz,
				db: db,
				additional_dbs: additional_dbs
			},

			success: function (response) {

				$('form.edit_user').append('<div class="alert alert-success mt20">User successfully updated</div>');
                $('.alert-success').show();
                $('.alert-danger').hide();

                setTimeout(function () {
                    $('.alert-success').hide();
                    $('form.edit_user').trigger("reset");
                    window.location.reload();
                }, 3500);
			}, error: function (data) {
                $('form.edit_user .alert').empty();

                var errors = $.parseJSON(data.responseText);
                $.each(errors, function (key, value) {

                    if ($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                            $('form.edit_user .alert').show().append('<li>' + value + '</li>');
                        });
                    } else {
                        $('form.edit_user .alert').show().append('<li>' + value + '</li>');
                    }
                });
                $('form.edit_user .alert li').first().remove();
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
			url: 'admin/add_demo_user',
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

				$('<div class="alert alert-success">User successfully updated</div>').insertBefore('form.add_demo_user .btn-primary').parent();
				$('.alert-success').show();
				setTimeout(function () {
                    $('.alert-success').hide();
                    $('form.add_demo_user').trigger("reset");
					window.location.href = "/dashboards/admin#demo_user";
				}, 2500);
			}, error: function (data) {
				$('form.add_demo_user .alert').empty();

				if (data.status === 422) {
					var errors = $.parseJSON(data.responseText);
					$.each(errors, function (key, value) {

						if ($.isPlainObject(value)) {
							$.each(value, function (key, value) {
								$('form.add_demo_user .alert').show().append('<li>' + value + '</li>');
							});
						} else {
							$('form.add_demo_user .alert').show().append('<li>' + value + '</li>');
						}
					});

					$('form.add_demo_user .alert li').first().remove();
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
			url: 'admin/update_demo_user ',
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
				$('form.edit_demo_user').append('<div class="alert alert-success oauto mt20">User successfully updated</div>');
				$('.alert-success').show();
                $('.alert-danger').hide();
				setTimeout(function () {
                    $('.alert-success').remove();
                    $('#demoUserModal').modal('hide');
					window.location.href = "/dashboards/admin#demo_user";
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
			url: 'admin/get_user',
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
			url: 'admin/get_user',
			type: 'POST',
			dataType: 'json',
			data: { id: user_id },
			success: function (response) {
				$('html,body').scrollTop($('body').scrollTop());

				$('#edit_dialer' + dialer).addClass('in');
				$('#edit_dialer' + dialer).attr('aria-expanded', true);
				$('#edit_heading' + dialer + ' h4 a').attr('aria-expanded', true);
				var form = $('form.edit_user');
				form.find('.group_id').val(response.group_id);
				form.find('.name').val(response.name);
				form.find('.email').val(response.email);
				form.find('#tz').val(response.tz);
				form.find('#user_type').val(response.user_type);
				form.find('#db').val(response.db);
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
			url: 'admin/delete_user',
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
			db = form.find('#db').val()
			;

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: 'admin/edit_myself',
			type: 'POST',
			dataType: 'json',
			data: {
				id: user_id,
				group_id: group_id,
				db: db,
			},

			success: function (response) {

				if (response.errors) {
					$('form.edit_myself').append('<div class="alert alert-danger">' + response.errors + '</div>');
					$('.alert-danger').show();
				}else{
                    $('.alert-success').remove();
					$('form.edit_myself').append('<div class="alert alert-success">User successfully updated</div>');
					$('.alert-success').show();
					setTimeout(function(){
						$('.alert-success').hide();
						window.location.href = "/dashboards/admin#settings";
					}, 3500);
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
			url: 'admin/cdr_lookup',
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
}

$(document).ready(function(){
	Admin.init();
});