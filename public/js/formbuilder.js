var FORMBUILDER = {

	current_row:0,
	element_type:'',

	init:function(){
		$('.checkall_inputs').on('click', this.checkall_inputs);
		$('.generate_code').on('click', this.generate_code);
		$('#db').on('change', this.get_client_tables);
		$('#client_table_menu').on('change', this.get_table_fields);
		$('.add_custom_form_field').on('submit', this.add_custom_form_field);
		$('body').on('dblclick', '.field_label_fb', this.edit_field_label);
		$('body').on('dblclick', '.field_name_fb', this.edit_field_label);
		$('.download_file').on('click', this.download_file);
		$('body').on('change', '.field_type', this.open_field_modal);
		$('#filter_type_modal').on('click', '.add_option', this.add_option);
		$('#filter_type_modal').on('click', '.remove_option', this.remove_option);
		$('#filter_type_modal').on('click', '.create_form_element', this.create_form_element);
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
					var fields_len = $('.field.slide').length;

					for (var i = 0; i < response.fields.length; i++) {
						fields_len++;
						new_field_row = '<div class="field slide field_from_table draggable" data-id="'+fields_len+'"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-2"><p class="field_label_fb" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-3"><p class="field_name_fb" data-field="' + response.fields[i] + '">' + response.fields[i] + '</p></div><div class="col-sm-3"><div class="form-group"><input type="text" control="input" class="form-control control-input field_value_fb" name="' + response.fields[i] + '" placeholder="Value" value=""></div></div><div class="col-sm-3"><div class="form-group"><select name="field_type" class="form-control field_type"><option value="input" selected>Input</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkboxes">Checkbox</option></select></div><div class="custom_element" data-elementtype=""></div></div></div>';

						$(new_field_row).insertAfter('.all-slides .field:last');
					}
				}

				FORMBUILDER.move_notes_row();
			}
		});
	},

	add_custom_form_field: function (e) {
		e.preventDefault();

		var custom_field_label_fb = $('.custom_field_label_fb').val();
		var custom_field_name_fb = $('.custom_field_name_fb').val();
		var custom_field_value_fb = $('.custom_field_value_fb').val();
		var fields_len = $('.field.slide').length;
		fields_len++;
		var new_field_row = '<div class="field slide" data-id="'+fields_len+'"><div class="col-sm-1"><a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a></div><div class="col-sm-2"><p class="field_label_fb" data-field="' + custom_field_label_fb + '">' + custom_field_label_fb + '</p></div><div class="col-sm-3"><p class="field_name_fb" data-field="' + custom_field_name_fb + '">' + custom_field_name_fb + '</p></div><div class="col-sm-3"><div class="form-group"><input type="text" class="form-control control-input field_value_fb" control="input" name="" placeholder="Value" value="'+custom_field_value_fb+'"></div></div><div class="col-sm-3"><div class="form-group"><select name="field_type" class="form-control field_type"><option value="input" selected>Input</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkboxes">Checkbox</option></select></div><div class="custom_element" data-elementtype=""></div></div></div>';

		$(new_field_row).insertAfter('.field:last');
		$(this).trigger("reset");
		FORMBUILDER.move_notes_row();
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

			let field_type = $(this).find('.field_type').val();

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

				// add value attribute to inputs
				if(field_type == 'input'){
					$('.html_options').find('.form-group .form-control').attr('value', field_value_fb);
				}

				if(field_label_fb == 'City' || field_label_fb == 'ZipCode'){ /// wrap in 4 column div
					html=$('.html_options').find('.input-4').html();
				}else if($(this).find('.field_type').val() == 'select'){ /// wrap in a 4 column w/ select menu
					var new_element_obj=$(this).find('.custom_element').data('new_element_data');
					var options='<option value="">Select One</option>';
					console.log(typeof(new_element_obj));
					for (var key of Object.keys(new_element_obj)) {
						options += '<option value="'+new_element_obj[key]+'">'+key+'</option>';
					}

					$('.html_options').find('.select-6').find('select.form-control').append(options);
					html=$('.html_options').find('.select-6').html();
					$('.html_options').find('.select-6 select').empty();

				}else if($(this).find('.field_type').val() == 'radio'){ /// wrap in a 4 column w/ radio menu
					var new_element_obj=$(this).find('.custom_element').data('new_element_data');
					var group_name = $(this).find('.custom_element').data('groupname');
					var radio_inputs='';

					for (var key of Object.keys(new_element_obj)) {
						radio_inputs += '<label class="radio-inline"><input type="radio" name="'+group_name+'" value="'+new_element_obj[key]+'">'+key+'</label>';
					}

					$('.html_options').find('.radio-6').find('.col-sm-6').append(radio_inputs);
					html=$('.html_options').find('.radio-6').html();
					$('.html_options').find('.radio-6 .col-sm-6').empty();

				}else if($(this).find('.field_type').val() == 'checkbox'){ /// wrap in a 4 column w/ checkbox menu
					var new_element_obj=$(this).find('.custom_element').data('new_element_data');
					var group_name = $(this).find('.custom_element').data('groupname');
					var checkbox_inputs='';

					for (var key of Object.keys(new_element_obj)) {
						checkbox_inputs += '<label class="checkbox-inline"><input type="checkbox" name="'+group_name+'" value="'+new_element_obj[key]+'">'+key+'</label>';
					}

					$('.html_options').find('.checkbox-6').find('.col-sm-6').append(checkbox_inputs);
					html=$('.html_options').find('.checkbox-6').html();
					$('.html_options').find('.checkbox-6 .col-sm-6').empty();

				}else if($(this).find('.field_type').val() == 'textarea'){ /// wrap in a 4 column w/ textarea menu
					html=$('.html_options').find('.textarea-6').html();
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
	},

	open_field_modal:function(e){
		e.preventDefault();

		var id = $(this).parent().parent().parent().data('id');
		FORMBUILDER.current_row = id;

		var new_element_data = $(this).parent().parent().find('div.custom_element').attr('data-new_element_data');
		console.log(FORMBUILDER.element_type);
		$('#filter_type_modal').find('.field_type_options').hide();
		var field_type = $(this).val();
		if(field_type !== 'input' && field_type !== 'textarea'){
			FORMBUILDER.element_type=field_type;
			$('#filter_type_modal').modal('show');
			$('#filter_type_modal .modal-body').find('div[data-type="' + field_type + '"]').show();
			$('#filter_type_modal .modal-body').find('div[data-type="' + field_type + '"]').addClass('active');
		}

		console.log(field_type);

		if(new_element_data){
			var new_element_data_obj = JSON.parse(new_element_data);
			var i=1;

			for (var key of Object.keys(new_element_data_obj)) {
				if(i<=Object.keys(new_element_data_obj).length){
					$('#filter_type_modal .field_type_options.active').find('.input-group').last().find("input:text").first().val(key);
					$('#filter_type_modal .field_type_options.active').find('.input-group').last().find("input:text").last().val(new_element_data_obj[key]);
					if(i<Object.keys(new_element_data_obj).length){
						$('#filter_type_modal .field_type_options.active').find('.input-group').last().clone().find("input:text").val("").end().insertBefore('#filter_type_modal .modal-body .field_type_options.active a.btn');
					}
				}
				i++;
			}
		}
	},

	add_option:function(){

		$('#filter_type_modal').find('.alert').hide();

		let valid = FORMBUILDER.validate_options();

		if(valid){
			if($(this).is(':last-child')){
			   $('#filter_type_modal .field_type_options.active').find('.input-group').last().clone().find("input:text").val("").end().insertBefore('#filter_type_modal .modal-body .field_type_options.active a.btn');
			}else{
				return false;
			}
		}else{
			$('#filter_type_modal').find('.alert').html('Add a name and value before adding another field group').show();
		}
	},

	remove_option:function(){

		$('#filter_type_modal').find('.alert').hide();

		var options_len = $('#filter_type_modal .field_type_options.active').find('.input-group').length;
		if(options_len < 2){
			return false;
		}else{
			$(this).parent().parent().remove();
		}
	},

	validate_options:function(){
		let valid=true;

		$('.field_type_options.active .input-group input').each(function(){
			if($(this).val() == ''){
				valid=false;
				return false;
			}
		});

		return valid;
	},

	create_form_element:function(e){
		e.preventDefault();
		$('#filter_type_modal').find('.alert').hide();
		let valid = FORMBUILDER.validate_options();

		if(valid){

			if($(this).parent().find('div.group_name').length){
				var group_name = $(this).parent().find('input.group_name').val();
			}

			var opts = {};
			$('div[data-type="' + FORMBUILDER.element_type + '"]').find('.input-group').each(function(){
				opts[$(this).find('.option_name').val()] = $(this).find('.option_value').val();
			})

			$('div[data-id="' + FORMBUILDER.current_row + '"]').find('.custom_element').attr('data-elementtype', FORMBUILDER.element_type);
			$('div[data-id="' + FORMBUILDER.current_row + '"]').find('.custom_element').attr("data-new_element_data", JSON.stringify(opts));
			$('div[data-id="' + FORMBUILDER.current_row + '"]').find('.custom_element').attr('data-groupname', group_name);

			$('#filter_type_modal').modal('hide');
		}else{
			$('#filter_type_modal').find('.alert').html('Fill out all fields before creating a menu').show();
		}

		var new_element_data = JSON.parse($('div[data-id="' + FORMBUILDER.current_row + '"]').find('.custom_element').attr("data-new_element_data"));
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

	$("#filter_type_modal").on("hidden.bs.modal", function () {
	    $('#filter_type_modal').find('.input-group').find("input:text").val("").end();
	    $('.field_type_options.active .input-group').not('.input-group:first').remove();
	    $('#filter_type_modal .modal-body').find('div').removeClass('active');

	    if($('.field_type').val() !== 'input'){
	    	$(this).addClass('selected');
	    }
	});
});