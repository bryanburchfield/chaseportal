var DNC_uploader = {


	init:function(){
        $('.reverse_lead_move').on('click', this.reverse_lead_move_modal);
        $('.confirm_reverse_lead_move').on('click', this.reverse_lead_move);
        $('#reverseLeadMoveModal').on('hidden.bs.modal', this.hide_modal_error);
        $('.delete_dnc').on('click', this.populate_dnc_modal);
        $('.reverse_dnc').on('click', this.populate_dnc_reversemodal);
        $('.toggle_instruc').on('click', this.toggle_instructions);
	},

	reverse_lead_move_modal: function (e) {
		e.preventDefault();
		var lead_move_id = $(this).data('leadid');
		$('#reverseLeadMoveModal').find('.lead_move_id').val(lead_move_id);
		$('#reverseLeadMoveModal').modal('show');
	},

	reverse_lead_move: function () {
		var lead_move_id = $('#reverseLeadMoveModal').find('.lead_move_id').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});

		$.ajax({
			url: '/tools/contactflow_builder/reverse_move',
			type: 'POST',
			dataType: 'json',
			data: { lead_move_id: lead_move_id },
			success: function (response) {

				$('#reverseLeadMoveModal').find('.modal-footer').find('.alert').remove();
				if (response.error) {

					$('#reverseLeadMoveModal').find('.modal-footer').append('<div class="alert alert-danger mt20 text-center">' + response.error + '</div>');
				} else {
					var hash = window.location.hash;
					localStorage.setItem('activeTab', hash);
					window.location = '/tools/contactflow_builder';
				}
			}
		});
	},

    hide_modal_error:function(){
        $(this).find('.modal-footer .alert').remove();
    },

    populate_dnc_modal:function(){
        var id = $(this).data('id');
        $('#deleteDNCModal .modal-footer').find('.btn-danger').val('delete:'+id);
    },

    populate_dnc_reversemodal:function(){
        var id = $(this).data('id');
        $('#reverseDNCModal .modal-footer').find('.btn-danger').val('reverse:'+id);
    },

    toggle_instructions:function(e){

        if(e){
            e.preventDefault();
        }

        that = $('a.toggle_instruc');
        if(that.hasClass('collapsed')){
            that.removeClass('collapsed');
            that.empty().append('<i class="fas fa-angle-up"></i>');
        }else{
            that.addClass('collapsed');
            that.empty().append('<i class="fas fa-angle-down"></i>');
        }

        that.parent().find('.instuc_div').slideToggle();
    },
}

$(document).ready(function(){
	DNC_uploader.init();

	if($('.dnc_table tbody tr').length){
        DNC_uploader.toggle_instructions();
    }

});

// populate dnc file upload name in input
$(document).on('change', ':file', function() {
    var label = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).trigger('fileselect', [label]);
  });

$(':file').on('fileselect', function(event, label) {
    $('.filename').text(label);
});