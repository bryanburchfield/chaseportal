
if($('#ecoverme_lead_export').length){
	$('#call_status_select ').multiselect('select', 'Client Sold');
}


$('#group_duration #dialer').on('change', function(){
	var dialer = $(this).val();

	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	    }
	});

	$.ajax({
	    url: '/dashboards/admin/get_groups',
	    type: 'POST',
	    dataType: 'json',
	    data: {
	        dialer: dialer,
	    },
	    success: function (response) {
	        $('.group_select').empty();
        	var groups_response = Object.entries(response);

        	// build groups select menu
        	if(groups_response.length){

        		var groups_list='';
        		var selected;

        		for(var i=0; i<groups_response.length;i++){
        		    groups_list+='<div class="checkbox mb10 cb"><label><input class="groups" name="groups[]" type="checkbox" value="'+groups_response[i][0]+'"><b>'+groups_response[i][1]+'</b></label></div>';
        		}

        		$('.group_select').append(groups_list);
        	}
	    }
	});
});


