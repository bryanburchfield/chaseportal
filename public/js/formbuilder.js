var FORMBUILDER = {

	init:function(){
		$('.checkall_inputs').on('click', this.checkall_inputs);
		$('.generate_code').on('click', this.generate_code);
		$('#db').on('change', this.get_client_tables);
		$('#client_table_menu').on('change', this.get_table_fields);
		$('.add_custom_form_field').on('submit', this.add_custom_form_field);
		$('.form_code').on('click', this.copy_code);
		$('body').on('dblclick', '.field_label_fb', this.edit_field_label);
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

				$('.field_from_table').remove();
				if (response.fields.length) {
					var new_field_row = '';
					for (var i = 0; i < response.fields.length; i++) {
						new_field_row = '<div class="field slide field_from_table draggable"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-3"><p class="field_label_fb" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-4"><div class="form-group"><input type="text" class="form-control field_name_fb" name="' + response.fields[i] + '" placeholder="' + response.fields[i] + '" value="' + response.fields[i] + '"></div></div><div class="col-sm-4"><div class="form-group"><input type="text" class="form-control field_value_fb" name="' + response.fields[i] + '" placeholder="Value" value=""></div></div></div>';

						$(new_field_row).insertAfter('.all-slides .field:last');
					}
				}
			}
		});
	},

	add_custom_form_field: function (e) {
		e.preventDefault();

		var custom_field_name_fb = $('.custom_field_name_fb').val();
		var custom_field_value_fb = $('.custom_field_value_fb').val();

		var new_field_row = '<div class="field slide"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-3"><p class="field_label_fb" data-field="' + custom_field_name_fb + '">' + custom_field_name_fb + '</p></div><div class="col-sm-4"><div class="form-group"><input type="text" class="form-control field_name_fb" name="' + custom_field_name_fb + '" value="' + custom_field_value_fb + '"></div></div><div class="col-sm-4"><div class="form-group"><input type="text" class="form-control field_value_fb" name="" placeholder="Value" value=""></div></div></div>';

		$(new_field_row).insertAfter('.field:last');
		$(this).trigger("reset");
	},

	generate_code:function(e){
		e.preventDefault();

		$('.form_code').empty();

		var html = $('.html_options .head textarea').val();
		// '<form action="#" method="post" class="form">';
		FORMBUILDER.appended_code(html);

		$('.all-slides .field').each(function(){
			if(!$(this).hasClass('field_removed')){
				var field_label_fb = $(this).find('.field_label_fb').text();
				if($(this).hasClass('default')){
					var field_name_fb = $(this).find('.field_name_fb').text();
				}else{
					var field_name_fb = $(this).find('.field_name_fb').val();
				}field_value_fb

				var field_value_fb = $(this).find('.field_value_fb').val();

				if(field_label_fb !== 'State' && field_label_fb !== 'state' && field_label_fb !== 'STATE'){

					$('.html_options').find('.form-group label').text(field_label_fb);
					$('.html_options').find('.form-group input.form-control').attr('name', field_name_fb);
					$('.html_options').find('.form-group input.form-control').attr('field-name', field_name_fb);
					$('.html_options').find('.form-group input.form-control').attr('id', field_name_fb);
					$('.html_options').find('.form-group input.form-control').attr('value', field_value_fb);

					html=$('.html_options').find('.input').html();
				}else{
					html=$('.html_options').find('.select_state').html();
				}

				FORMBUILDER.appended_code(html);
			}
		});

		var btn_type = $("input[name='submit_btn_type']:checked").val();
		if(btn_type == 'submit'){
			var submit_btn = '	<input type="submit" value="Submit" class="btn btn-primary">';
		}else{
			var submit_btn = '	<input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary">';
		}

		submit_btn = FORMBUILDER.remove_tags(submit_btn);
		var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(submit_btn);

		$('.form_code').append(new_code_block);

		html = $('.html_options .bottom textarea').val();
		FORMBUILDER.appended_code(html);

		$('pre').each(function(i, block) {
		    hljs.highlightBlock(block);
		});

		$('.form_code_preview').show();
	},

	remove_tags:function(html){
		html=html.replace(/</g, "&lt;");
		html=html.replace(/>/g, "&gt;");

		return html;
	},

	appended_code:function(html){
		html = FORMBUILDER.remove_tags(html);
		var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(html);
		$('.form_code').append(new_code_block);
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
			html=html.replace(/</g, "&lt;");
			html=html.replace(/>/g, "&gt;");

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

	edit_field_label: function () {

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
}

$(document).ready(function () {
	FORMBUILDER.init();

	$( ".all-slides" ).sortable({
		revert: true,
		cursor: "grabbing"
	});

	// $( "ul, li" ).disableSelection();
});