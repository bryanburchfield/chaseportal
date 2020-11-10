var FORMBUILDER = {

	init:function(){
		$('.checkall_inputs').on('click', this.checkall_inputs);
		$('.generate_code').on('click', this.generate_code);
		$('#db').on('change', this.get_client_tables);
		$('#client_table_menu').on('change', this.get_table_fields);
		$('.add_custom_form_field').on('submit', this.add_custom_form_field);
		$('.form_code').on('click', this.copy_code);
	},

	get_client_tables: function () {

		$('.alert-danger').hide();
		var database = $(this).val();
		var group_id = $(this).parent().parent().find('#group_id').val();

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
				console.log(response);
				$('#client_table_menu').empty();
				if (response.tables.length) {
					var tables = '<option value="">Select One</option>';
					for (var i = 0; i < response.tables.length; i++) {
						tables += '<option value="' + response.tables[i].TableName + '">' + response.tables[i].TableName + ' - ' + response.tables[i].Description + '</option>';
					}

					$('#client_table_menu').append(tables);
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
				console.log(response);
				$('.field_from_table').remove();
				if (response.fields.length) {
					var new_field_row = '';
					for (var i = 0; i < response.fields.length; i++) {
						new_field_row += '<div class="field field_from_table"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-4"><p class="field_label" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control field_name" name="' + response.fields[i] + '" placeholder="' + response.fields[i] + '"></div></div><div class="col-sm-2"></div></div>';
					}
					$(new_field_row).insertAfter('.field:last');
				}
			}
		});
	},

	add_custom_form_field: function (e) {
		e.preventDefault();

		var custom_field_name = $('.custom_field_name').val();
		var custom_field_value = $('.custom_field_value').val();
		// console.log(custom_field_name +' '+ custom_field_value);
		var new_field_row = '<div class="field"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-4"><p class="field_label" data-field="' + custom_field_name + '">' + custom_field_name + '</p></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control field_name" name="' + custom_field_name + '" value="' + custom_field_value + '"></div></div><div class="col-sm-2"></div></div>';

		$(new_field_row).insertAfter('.field:last');
		$(this).trigger("reset");
	},

	generate_code:function(e){
		e.preventDefault();

		$('.form_code').empty();

		$('.field').each(function(){
			if(!$(this).hasClass('field_removed')){
				var field_label = $(this).find('.field_label').text();
				var field_name = $(this).find('.field_name').val();

				$('.html_options').find('.form-group label').text(field_label);
				$('.html_options').find('.form-group input.form-control').attr('name', field_name);
				$('.html_options').find('.form-group input.form-control').attr('field-name', field_name);
				$('.html_options').find('.form-group input.form-control').attr('id', field_name);

				var html=$('.html_options').html();
				html = FORMBUILDER.remove_tags(html);

				console.log(html);

				var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(html);
				$('.form_code').append(new_code_block);
			}

		});

		var submit_btn = '<input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary">';

		submit_btn = FORMBUILDER.remove_tags(submit_btn);
		var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(submit_btn);

		$('.form_code').append(new_code_block);

		$('pre').each(function(i, block) {
		    hljs.highlightBlock(block);
		});


		$('.form_code_preview').show();
		// console.log(input);
		if ($(this).prop("checked") == true) {

		}
	},

	remove_tags:function(html){
		html=html.replace(/</g, "&lt;");
		html=html.replace(/>/g, "&gt;");

		return html;
	},

	copy_code: function (e) {
        e.preventDefault();
        $(this).tooltip({
            animated: 'fade',
            placement: 'bottom',
            trigger: 'click'
        });

        setTimeout(function () {
            $('.tooltip').fadeOut('slow');
        }, 3500);

        var $temp = $("<input>");
        $(this).parent().append($temp);
        $temp.val($(this).text()).select();
        document.execCommand("copy");
        $temp.remove();
    },

	update_code:function(){
		$('.form_code').empty();

		$('.user_created_form_element').find('.user_created_element').each(function(){
			var html=$('.html_options').html();
			console.log(html);
			// html = html.replace(/^\s*/gm, '');
			html=html.replace(/</g, "&lt;");
			html=html.replace(/>/g, "&gt;");

			// $('.copy_code pre code').append(html);

			var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(html);
			$('.form_code').append(new_code_block);
		});

		$('.form_code').parent().show();

		$('pre').each(function(i, block) {
		    hljs.highlightBlock(block);
		});
	},

	checkall_inputs: function () {
		if ($(this).prop("checked") == true) {
			$(this).parent().find('span').text('Uncheck All as Inputs');

			$('.field_type').each(function(){
				$(this).find('option:eq(1)').attr('selected', 'selected')
			});
		} else {
			$(this).parent().find('span').text('Select All Inputs');
			$('.field_type').each(function(){
				$(this).find('option:eq(0)').attr('selected', 'selected')
			});
		}

		$('.field .use_system_macro').each(function () {
			Admin.toggle_system_macro($(this));
		});
	},
}

$(document).ready(function () {
	FORMBUILDER.init();
});