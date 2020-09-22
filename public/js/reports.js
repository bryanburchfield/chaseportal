
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

        	// build groups select menu
        	if(response.length){

        		var groups_list='';
        		for(var i=0; i<response.length;i++){
        		    groups_list+='<div class="checkbox mb10 cb"><label><input class="groups" name="groups[]" type="checkbox" value="'+response[i]['GroupId']+'"><b>'+response[i]['GroupName']+'</b></label></div>';
        		}

        		$('.group_select').append(groups_list);
        	}
	    }
	});
});


