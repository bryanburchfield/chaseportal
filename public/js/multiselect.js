// $(document).ready(function(){

	$('.multiselect').multiselect({
		allSelectedText: Lang.get('js_msgs.all_selected'),
		includeSelectAllOption: true,
		enableFiltering: true,
		enableCaseInsensitiveFiltering: true,
		buttonWidth:'100%',
		filterPlaceholder: Lang.get('js_msgs.search')
	});

	// preselect all campaigns
	$('#call_details #campaign_select, #agent_summary_campaign #campaign_select, #agent_summary_subcampaign #campaign_select, #inbound_summary #campaign_select')
		.multiselect({nonSelectedText: Lang.get('js_msgs.select_campaign'),})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    // preselect all reps
    $('#agent_summary #rep_select, #agent_summary_campaign #rep_select')
    	.multiselect({nonSelectedText: Lang.get('js_msgs.select_rep'),})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    // preselect all dbs on reports
    $('#database_select')
    	.multiselect({nonSelectedText: Lang.get('js_msgs.select_report'),})
		.multiselect('selectAll', false)
    	.multiselect('updateButtonText');

    //// remove select all option
    $('#lead_inventory #campaign_select').next('div').find('ul li.multiselect-item.multiselect-all').remove();
	
	$('#inbound_sources_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_inbound_source'),});
	$('#rep_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_rep'),});
	$('#call_status_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_call_status'),});
// });