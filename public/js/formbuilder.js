var FORMBUILDER = {
	init:function(){
		$('.add_element').on('click', this.add_element);
		$('.form_preview').on('click', '.remove_form_element', this.remove_element);
		$('.theme_selector select').on('change', this.apply_theme);
		$('.generate_form_code').on('click', this.generate_code);
	},

	add_element:function(e){
		e.preventDefault();
		var element = $(this).parent().next().find('.form-group').html();
		console.log(element);
		var appended_elem = $('<div class="user_created_form_element"><div class="col-sm-1"><a class="remove_form_element text-center" href="#"><i class="fas fa-trash-alt"></i></a></div><div class="col-sm-11 user_created_element"><div class="form-group">'+element+'</div></div></div>');

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
			$('.form_preview, .form_element_options').find('input.form-control').attr('placeholder', '');
			// $('.form_element_options').find('input.form-control').attr('placeholder', '');
		}else{
			$('.form_preview, .form_element_options').find('input.form-control').attr('placeholder', $(this).attr('name'));
			// $('.form_element_options').find('.form-control')
		}

		$('.form_preview').find('.form-control').attr('class', 'form-control');
		$('.form_preview').find('.form-control').addClass($(this).val());

		$('.form_element_options').find('.form-control').attr('class', 'form-control');
		$('.form_element_options').find('.form-control').addClass($(this).val());

		FORMBUILDER.update_code();
	},

	update_code:function(){
		$('.codegoeshere').empty();
		var html="";
		$('.user_created_form_element').find('.user_created_element').each(function(){
			html +=$(this).html().trim();

		});

		html = html.replace(/^\s*/gm, '');
		html=html.replace(/</g, "&lt;");
		html=html.replace(/>/g, "&gt;");

		$('.form_code').parent().show();
		$('.codegoeshere').append(html);

		$('pre').each(function(i, block) {
		    hljs.highlightBlock(block);
		});
		
	}
}

$(document).ready(function(){
	FORMBUILDER.init();
	
});