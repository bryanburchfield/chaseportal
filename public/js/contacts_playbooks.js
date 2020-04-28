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
	        	console.log(response);
	            var subcampaigns='<option value=""> Select One</option>';
	            for(var i=0; i<response.subcampaigns.length;i++){
	                subcampaigns+='<option value="'+response.subcampaigns[i]+'">'+response.subcampaigns[i]+'</option>';
	            }

                $('.subcampaign').empty();
                $('.subcampaign').append(subcampaigns);
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