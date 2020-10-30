var FORMBUILDER = {

	element_type:'',

	init:function(){
		$('.add_element').on('click', this.add_element);
		$('.form_preview').on('click', '.remove_form_element', this.remove_element);
		$('.theme_selector select').on('change', this.apply_theme);
		$('body').on('click', '.edit_form_element', this.pass_id);
		$('.edit_field').on('click', this.update_field);
		$('body').on('click', '.edit_form_element', this.populate_fields_form);
		$('.form_code').on('click', this.copy_code);
		$('.display_type').on('change', this.change_checked_type);
	},

	add_element:function(e){
		e.preventDefault();
		var type = $(this).data('type');
		FORMBUILDER.element_type = type;
		FORMBUILDER.show_numb_fields();

		if(type=='radio' || type =='checkbox'){
			$('#editFieldModal').modal('show');
		}else{
			var element = $(this).parent().next().find('.form-group').html();

			var id= $('.user_created_form_element').length;
			var appended_elem = $('<div class="user_created_form_element" data-id="'+id+'"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">'+element+'</div></div></div>');

			$(appended_elem).appendTo('.form_preview');

			// remove submit btn then append to bottom
			$('.form_preview').find('.btn_div').remove();
			var btn = $('<div class="user_created_form_element btn_div"><div class="col-sm-1"></div><div class="col-sm-11 user_created_element"><input type="submit" control="submit" action="submit_and_navigate" navigate-to="confirmation_page" value="Submit and Navigate" class="control-submit btn btn-primary"></div></div>');
			$('.form_preview').find('.btn').remove();
			$(btn).appendTo('.form_preview');

			$('.form_preview').show();
			$('.form_code').parent().show();
			FORMBUILDER.update_code();
		}

		console.log(FORMBUILDER.element_type);
	},

	remove_element:function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
		FORMBUILDER.update_code();
		if(!$('.user_created_form_element').length){
			$('.form_code').parent().hide();
			$('.form_preview').hide();
		}
	},

	apply_theme:function(){

		if($(this).val()!='clean'){
			$('.form_preview, .form_element_options').find('.form-control').each(function(){
				$(this).parent().find('label').show();
			});
			$('.form_preview, .form_element_options').find('.form-control').attr('placeholder', '');
		}else{
			$('.form_preview, .form_element_options').find('.form-control').each(function(){
				var label=$(this).parent().find('label').text();
				$(this).attr('placeholder', label);
				$(this).parent().find('label').hide();
			});
		}

		$('.form_preview').find('.form-control').attr('class', 'form-control');
		$('.form_preview').find('.form-control').addClass($(this).val());

		$('.form_element_options').find('.form-control').attr('class', 'form-control');
		$('.form_element_options').find('.form-control').addClass($(this).val());

		if($('.user_created_form_element').length){
			FORMBUILDER.update_code();
		}
	},

	update_code:function(){
		$('.form_code').empty();

		$('.user_created_form_element').find('.user_created_element').each(function(){
			var html=$(this).html();
			// console.log(html);
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

	pass_id:function(){
		$('#editFieldModal .modal-body').find('.id').val($(this).parent().parent().data('id'));
	},

	update_field:function(){

		var field_label = $('.field_label').val();
		var field_name = $('.field_name').val();
		var id = $('.id').val();

		$('.form_preview').find('[data-id='+id+']').find('.form-group label').text(field_label);
		$('.form_preview').find('[data-id='+id+']').find('.form-group .form-control').attr('placeholder', field_label);
		$('.form_preview').find('[data-id='+id+']').find('.form-group .form-control').attr('name', field_name);
		$('.form_preview').find('[data-id='+id+']').find('.form-group .form-control').attr('field-name', field_name);
		$('.form_preview').find('[data-id='+id+']').addClass('edited');

		$('#editFieldModal').modal('hide');
		FORMBUILDER.update_code();
	},

	populate_fields_form:function(){

		var id = $(this).parent().parent().data('id');

		if($(this).parent().parent().hasClass('edited')){
			$('.field_label').val($(this).parent().parent().find('label').text());
			$('.field_name').val($(this).parent().parent().find('.form-control').attr('name'));
		}
	},

	show_numb_fields:function(){
		$('#editFieldModal').find('.modal-body .hidetilloaded').show();
	},

	change_checked_type:function(){
		if($(this).val()=='stacked'){
			$('.inline').hide();
			$('.stacked').show();
			// find radio with value of stacked and set that to checked
			// $('.stacked').find('input').val('')
		}else{
			$('.inline').show();
			$('.stacked').hide();
			// find radio with value of inline and set that to checked
			// $('.inline').find('input').val('')
		}
	},

	copy_code: function (e) {
        e.preventDefault();
        console.log('clicked');
        $(this).tooltip({
            animated: 'fade',
            placement: 'left',
            trigger: 'click'
        });

        setTimeout(function () {
            $('.tooltip').fadeOut('slow');
        }, 3500);

        var $temp = $("<input>");
        $(this).parent().append($temp);
        $temp.val($(this).text()).select();
        document.execCommand("copy");
        console.log($temp);
        $temp.remove();
    },
}

$(document).ready(function(){
	FORMBUILDER.init();

	//clear form when modal is closed
	$('#editFieldModal').on("hide.bs.modal", function() {
		$(this).find('.modal-body .form-control').val('');
		$(this).find('.modal-body .hidetilloaded').hide();
	})
});