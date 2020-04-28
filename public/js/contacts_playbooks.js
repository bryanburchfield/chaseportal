var Contacts_Playbook = {

	init:function(){
		$('#campaign_select').on('change', this.get_subcampaigns);
		$('.add_playbook').on('submit', this.add_playbook);
	},

	get_subcampaigns:function(e){
		e.preventDefault();
		var campaign = $(this).val();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_subcampaigns',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign: campaign,
	        },

	        success:function(response){
                $('.subcampaign').empty();

                var response = Object.values(response.subcampaigns);
                var sub_camps='<option value="">Select One</option>';
                for(var i=0;i<response.length;i++){
                	sub_camps+='<option value="'+response[i]+'">'+response[i]+'</option>';
                }

                $('.subcampaign').append(sub_camps);
	        }
	    });
	},

	add_playbook:function(e){
		e.preventDefault();

		var form_data = $(this).serialize();

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/playbooks' ,
	        type: 'POST',
	        dataType: 'json',
	        data: form_data,
	        success:function(response){
	            console.log(response);
	            if(response.status == 'success'){
	            	location.reload();
	            }
	        }
	    });
	}
}

$(document).ready(function(){
	Contacts_Playbook.init();
});