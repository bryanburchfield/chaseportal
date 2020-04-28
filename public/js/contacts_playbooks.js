var Contacts_Playbook = {

	init:function(){
		$('#campaign_select').on('change', this.campaign_changed);
		$('.add_playbook').on('submit', this.add_playbook);
	},

	campaign_changed:function(e){
		e.preventDefault();

		var campaign = $(this).val();

  		Contacts_Playbook.get_subcampaigns(campaign);
  		Contacts_Playbook.get_filters(campaign);
  		Contacts_Playbook.get_actions(campaign);
	},

	get_subcampaigns:function(campaign){

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/contactflow_builder/get_subcampaigns' ,
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

	get_filters:function(campaign){

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_filters' ,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign: campaign,
	        },

	        success:function(response){
	            console.log(response);
	            var filters='<option value=""> Select One</option>';
	            for(var i=0; i<response.length;i++){
	                filters+='<option value="'+response[i]['id']+'">'+response[i]['name']+'</option>';
	            }

                $('.filters').empty();
                $('.filters').append(filters);
	        }
	    });
	},

	get_actions:function(campaign){

		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_actions' ,
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign: campaign,
	        },

	        success:function(response){
	            console.log(response);
	            var actions='<option value=""> Select One</option>';
	            for(var i=0; i<response.length;i++){
	                actions+='<option value="'+response[i]['id']+'">'+response[i]['name']+'</option>';
	            }

                $('.actions').empty();
                $('.actions').append(actions);
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