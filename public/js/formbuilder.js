var FORMBUILDER = {

	init:function(){
		$('.checkall_inputs').on('click', this.checkall_inputs);
		$('.generate_code').on('click', this.generate_code);
	},

	generate_code:function(e){
		e.preventDefault();
		var input='';


		var textarea = '<textarea class="form-control" name="" rows="3"></textarea>';

		$('.field_name').each(function(){
			input+= '<div class="form-group">'+
						'<input type="text" class="form-control" name="'+$(this).text()+'" field-name="'+$(this).text()+'" placeholder="">'+
					'</div>';
			console.log($(this).text());
		});
		console.log(input);
		if ($(this).prop("checked") == true) {

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