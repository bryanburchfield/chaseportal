
if($('#ecoverme_lead_export').length){
	$('#call_status_select ').multiselect('select', 'Client Sold');
}

$('#group_select').next('ul').on('click', '.select_all_groups', function(){
	if($(this).is(':checked')){
		$(this).parent().parent().siblings().find('input').prop("checked", true);
	}else{
		$(this).parent().parent().siblings().find('input').prop("checked", false);
	}
});

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

        		var groups_list='<div class="select_all checkbox mb10 cb"><label><input class="select_all_groups" name="select_all_groups" type="checkbox" value=""><b>Select All</b></label></div>';
        		for(var i=0; i<response.length;i++){
        		    groups_list+='<div class="checkbox mb10 cb"><label><input class="groups" name="groups[]" type="checkbox" value="'+response[i]['GroupId']+'"><b>'+response[i]['GroupId']+' - '+response[i]['GroupName']+'</b></label></div>';
        		}

        		$('.group_select').append(groups_list);
        	}
	    }
	});
});


