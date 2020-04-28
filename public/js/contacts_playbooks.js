var Contacts_Playbook = {

	init:function(){
		$('#campaign_select').on('change', this.get_subcampaigns);
		$('.add_playbook').on('submit', this.add_playbook);
		$('.playbook_actions_modal, .playbook_filters_modal').on('click', this.populate_modal);
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

                var response = Object.entries(response.subcampaigns);

                var sub_camps='<option value="">Select One</option>';
                for(var i=0;i<response.length;i++){
                	sub_camps+='<option value="'+response[i][0]+'">'+response[i][1]+'</option>';
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
	},

	populate_modal:function(e){
		e.preventDefault();
		var modal = $(this).data('target'),
			playbookid = $(this).data('playbookid'),
			is_empty = $(this).data('is_empty'),
			campaign = $(this).data('campaign')
		;

		modal = modal.substring(1);

		if(modal == 'filterPlaybookModal'){
			if(!is_empty){
				return Contacts_Playbook.get_playbook_filters(playbookid, modal);
			}

			return Contacts_Playbook.get_filters(campaign, modal);
		}else{
			if(!is_empty){
				return Contacts_Playbook.get_playbook_actions(playbookid, modal);
			}
			return Contacts_Playbook.get_actions(campaign, modal);
		}
	},

	get_playbook_filters:function(playbookid, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_playbook_filters',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            id : playbookid,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_filters:function(campaign, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_filters',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_playbook_actions:function(playbookid, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_playbook_actions',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            id : playbookid,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},

	get_actions:function(campaign, modal){
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/tools/playbook/get_actions',
	        type: 'POST',
	        dataType: 'json',
	        data: {
	            campaign : campaign,
	        },
	        success:function(response){
                console.log(response);
	        }
	    });
	},
}

$(document).ready(function(){
	Contacts_Playbook.init();
});