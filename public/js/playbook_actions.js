var Playbook_Actions = {

	init:function(){
		$('.add_action').on('submit', this.add_action);
		$('.action_types').on('change', this.update_action_fields);
	},

	add_action:function(e){
		e.preventDefault();
	},

	update_action_fields:function(e){
		e.preventDefault();
		var type = $(this).val();
		$('.action_type_fields').hide();
		$('.action_type_fields.'+ type).show();
	}
}

$(document).ready(function(){
	Playbook_Actions.init();
});