
if($('#ecoverme_lead_export').length){
	$('#call_status_select ').multiselect('select', 'Client Sold');
}


$('#group_duration #dialer').on('change', function(){
	var dialer = $(this).val();
	console.log(dialer);

	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	    }
	});

	$.ajax({
	    url: '/dashboards/reports/get_groups/',
	    type: 'POST',
	    dataType: 'json',
	    // async: false, /////////////////////// use async when rebuilding multi select menus
	    data: {
	        dialer: dialer,
	    },

	    success: function (response) {
	    	console.log(response);
	        // $('#campaign_select').empty();
	        // var camps_select;
	        // for (var i = 0; i < response.campaigns.length; i++) {
	        //     camps_select += '<option value="' + response.campaigns[i] + '">' + response.campaigns[i] + '</option>';
	        // }

	        // $('#campaign_select').append(camps_select);
	        // $("#campaign_select").multiselect('rebuild');
	        // $("#campaign_select").multiselect('refresh');

	        // $('#' + report + ' #campaign_select')
	        //     .multiselect({ nonSelectedText: Lang.get('js_msgs.select_campaign'), })
	        //     .multiselect('selectAll', true)
	        //     .multiselect('updateButtonText');
	    }
	});
});


