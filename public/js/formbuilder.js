var FORMBUILDER = {
	init:function(){
		$('.add_element').on('click', this.add_element);
		$('.form_preview').on('click', '.remove_form_element', this.remove_element);
		$('.theme_selector select').on('change', this.apply_theme);
	},

	add_element:function(e){
		e.preventDefault();
		var element = $(this).parent().next().find('.form-group').html();

		var appended_elem = $('<div class="user_created_form_element"><div class="col-sm-1"><a class="remove_form_element text-center" href="#"><i class="fas fa-trash-alt"></i></a></div><div class="col-sm-11">'+element+'</div></div>');

		$(appended_elem).appendTo('.form_preview');
	},

	remove_element:function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
	},

	apply_theme:function(){
		$('.form_preview').find('.form-control').addClass($(this).val());
		$('.form_element_options').find('.form-control').removeClass('default');
		$('.form_element_options').find('.form-control').addClass($(this).val());
	}
}

$(document).ready(function(){
	FORMBUILDER.init();
});