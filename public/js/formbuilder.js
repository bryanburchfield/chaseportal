var FORMBUILDER = {

	init:function(){
		$('.checkall_inputs').on('click', this.checkall_inputs);
		$('.generate_code').on('click', this.generate_code);
		$('#db').on('change', this.get_client_tables);
		$('#client_table_menu').on('change', this.get_table_fields);
		$('.add_custom_form_field').on('submit', this.add_custom_form_field);
		$('body').on('dblclick', '.field_label_fb', this.edit_field_label);
		$('body').on('dblclick', '.field_name_fb', this.edit_field_label);
		$('.download_file').on('click', this.download_file);
		this.move_notes_row(this.move_notes_row);
	},

	move_notes_row:function(){
		var notes_row;
		$('.field').each(function(){
			if($(this).find('.col-sm-3 p').data('field') == 'Notes'){
				notes_row=$(this).remove();
			}
		});
		$('.all-slides').append(notes_row);
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
						new_field_row = '<div class="field slide field_from_table draggable"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-3"><p class="field_label_fb" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-4"><p class="field_name_fb" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-4"><div class="form-group"><input type="text" control="input" class="form-control control-input field_value_fb" name="' + response.fields[i] + '" placeholder="Value" value=""></div></div></div>';

						$(new_field_row).insertAfter('.all-slides .field:last');
					}
				}
			}
		});
	},

	add_custom_form_field: function (e) {
		e.preventDefault();

		var custom_field_label_fb = $('.custom_field_label_fb').val();
		var custom_field_name_fb = $('.custom_field_name_fb').val();
		var custom_field_value_fb = $('.custom_field_value_fb').val();

		var new_field_row = '<div class="field slide"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-3"><p class="field_label_fb" data-field="' + custom_field_label_fb + '">' + custom_field_label_fb + '</p></div><div class="col-sm-4"><p class="field_name_fb" data-field="' + custom_field_name_fb + '">' + custom_field_name_fb + '</p></div><div class="col-sm-4"><div class="form-group"><input type="text" class="form-control control-input field_value_fb" control="input" name="" placeholder="Value" value="'+custom_field_value_fb+'"></div></div></div>';

		$(new_field_row).insertAfter('.field:last');
		$(this).trigger("reset");
	},

	generate_code:function(e){
		e.preventDefault();

		$('.form_code').empty();

		var html = $('.html_options .head textarea').val();

		FORMBUILDER.appended_code(html);
		var adv_fields = 0;
		if($('.field_from_table').length){
			adv_fields = 1;
		}

		$('.all-slides .field').each(function(index){

			if(!$(this).hasClass('field_removed')){
				var field_label_fb = $(this).find('.field_label_fb').text();
				var field_name_fb = $(this).find('.field_name_fb').text();
				if($(this).hasClass('default')){
					var field_name_fb = $(this).find('.field_name_fb').text();
				}

				if($(this).hasClass('field_from_table')){
					if(adv_fields){
						let html = '<div class="col-sm-12"><h4 class="subheading">Additional Information</h4></div>'
						FORMBUILDER.appended_code(html);
					}
					adv_fields=0;
				}

				var field_value_fb = $(this).find('.field_value_fb').val();

				$('.html_options').find('.form-group label').text(field_label_fb);
				$('.html_options').find('.form-group .form-control').attr('name', field_name_fb);
				$('.html_options').find('.form-group .form-control').attr('field-name', field_name_fb);
				$('.html_options').find('.form-group .form-control').attr('id', field_name_fb);
				$('.html_options').find('.form-group .form-control').attr('value', field_value_fb);

				if(field_label_fb == 'City' || field_label_fb == 'ZipCode'){ /// wrap in 4 column div
					html=$('.html_options').find('.input-4').html();
				}else if(field_label_fb == 'Address'){	/// wrap in 12 column div
					html=$('.html_options').find('.input-12').html();
				}else if(field_label_fb == 'State'){	/// grab state select
					html=$('.html_options').find('.select_state').html();
				}else if(field_label_fb == 'Notes'){	/// wrap in 12 col textarea for notes
					html=$('.html_options').find('.textarea-12').html();
				}else{	/// default to 6 column div
					html=$('.html_options').find('.input').html();
				}

				FORMBUILDER.appended_code(html);
			}
		});

		var btn_type = $("input[name='submit_btn_type']:checked").val();
		if(btn_type == 'submit'){
			var submit_btn = '		<div class="col-sm-12"><input type="submit" value="Submit" class="btn btn-primary btn-lg"></div>';
		}else{
			var submit_btn = '		<div class="col-sm-12"><input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary btn-lg"></div>';
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

	appended_code:function(html){
		html = FORMBUILDER.remove_tags(html);
		var new_code_block = $('<pre class="p10 appended_code sh_html xml"></pre>').wrapInner(html);
		$('.form_code').append(new_code_block);
	},

	remove_tags:function(html){
		html=html.replace(/</g, "&lt;");
		html=html.replace(/>/g, "&gt;");
		return html;
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

	download_file:function (e){
		e.preventDefault();
		var elem = document.createElement('a');
		elem.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(FORMBUILDER.get_html()));
		elem.setAttribute('download', 'index.html');

		elem.style.display = 'none';
		document.body.appendChild(elem);
		elem.click();
		document.body.removeChild(elem);
	},

	get_html:function(){
		return $('.form_code').text();
	}
}

$(document).ready(function () {
	FORMBUILDER.init();

	$( ".all-slides" ).sortable({
		revert: true,
		cursor: "grabbing"
	});

	$('pre').each(function(i, block) {
	    hljs.highlightBlock(block);
	});
});