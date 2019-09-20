$(document).ready(function(){

	$('.multiselect').multiselect({
		allSelectedText: 'All Selected',
		includeSelectAllOption: true,
		enableFiltering: true,
		enableCaseInsensitiveFiltering: true,
		buttonWidth:'100%'
	});

	// preselect all campaigns
	$('#call_details #campaign_select, #agent_summary_campaign #campaign_select, #agent_summary_subcampaign #campaign_select, #inbound_summary #campaign_select')
		.multiselect({nonSelectedText: 'Select Campaign',})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    // preselect all reps
    $('#agent_summary #rep_select, #agent_summary_campaign #rep_select')
    	.multiselect({nonSelectedText: 'Select Rep',})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    // preselect all dbs on reports
    $('#database_select')
    	.multiselect({nonSelectedText: 'Select Report',})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    	

    //// remove select all option
    $('#lead_inventory #campaign_select').next('div').find('ul li.multiselect-item.multiselect-all').remove();
	
	$('#inbound_sources_select').multiselect({nonSelectedText: 'Select Inbound Source',});
	$('#rep_select').multiselect({nonSelectedText: 'Select Rep',});
	$('#call_status_select').multiselect({nonSelectedText: 'Select Call Status',});
});