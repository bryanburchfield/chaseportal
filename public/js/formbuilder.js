var FORMBUILDER = {
	init:function(){
		$('.add_element').on('click', this.add_element);
		$('.form_preview').on('click', '.remove_form_element', this.remove_element);
		$('.theme_selector select').on('change', this.apply_theme);
		$('body').on('click', '.edit_form_element', this.pass_id);
		$('.edit_field').on('click', this.update_field);
	},

	// add each element to its own to pre tag to avoid formatting barfs
	add_element:function(e){
		e.preventDefault();
		var element = $(this).parent().next().find('.form-group').html();
		console.log($(this).parent().next().find('.form-control').attr('type'));
		var id= $('.user_created_form_element').length;
		var appended_elem = $('<div class="user_created_form_element" data-id="'+id+'"><div class="col-sm-1"><a class="remove_form_element text-center mr5" href="#"><i class="fas fa-trash-alt"></i></a><a class="edit_form_element text-center" href="#" data-toggle="modal" data-target="#editFieldModal"><i class="fas fa-edit"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">'+element+'</div></div></div>');

		$(appended_elem).appendTo('.form_preview');
		$('.form_preview').show();
		FORMBUILDER.update_code();
	},

	remove_element:function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
		FORMBUILDER.update_code();
	},

	apply_theme:function(){
		
		if($(this).val()!='clean'){
			$('.form_preview, .form_element_options').find('input.form-control').each(function(){
				$(this).parent().find('label').show();
			});
			$('.form_preview, .form_element_options').find('input.form-control').attr('placeholder', '');
		}else{
			$('.form_preview, .form_element_options').find('input.form-control').each(function(){
				var label=$(this).parent().find('label').text();
				$(this).attr('placeholder', label);
				$(this).parent().find('label').hide();
			});
		}

		$('.form_preview').find('.form-control').attr('class', 'form-control');
		$('.form_preview').find('.form-control').addClass($(this).val());

		$('.form_element_options').find('.form-control').attr('class', 'form-control');
		$('.form_element_options').find('.form-control').addClass($(this).val());

		FORMBUILDER.update_code();
	},

	update_code:function(){
		$('.codegoeshere').empty();
		

		$('.user_created_form_element').find('.user_created_element').each(function(){
			var html=$(this).html().trim();
			// console.log($(this).html());
			// html = html.replace(/^\s*/gm, '');
			// console.log(html);
			html=html.replace(/</g, "&lt;");
			html=html.replace(/>/g, "&gt;");
			$('.codegoeshere').append(html);
			
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
		$('.form_preview').find('[data-id='+id+']').find('.form-group .form-control').attr('name', field_name);

		$('#editFieldModal').modal('hide');
		FORMBUILDER.update_code();
	}
}

$(document).ready(function(){
	FORMBUILDER.init();

	//clear form when modal is closed
	$('#editFieldModal').on("hide.bs.modal", function() {
		$(this).find('.modal-body .form-control').val('');
	})
});