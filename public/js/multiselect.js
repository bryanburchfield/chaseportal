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
	// $(' #inbound_summary #campaign_select, #bwr_omni #campaign_select')
	// 	.multiselect({nonSelectedText: Lang.get('js_msgs.select_campaign'),})
	// 	.multiselect('selectAll', false)
 //    	.multiselect('updateButtonText');

	if(jQuery().selectpicker) {
	    // preselect all
	    // $('#rep_select, #database_select, #call_details #campaign_select, #agent_summary_campaign #campaign_select, #agent_summary_subcampaign #campaign_select, #bwr_omni #campaign_select, #inbound_summary #campaign_select').selectpicker('selectAll');
	}

    //// remove select all option
    $('#lead_inventory #campaign_select').next('div').find('ul li.multiselect-item.multiselect-all').remove();
	
	// $('#inbound_sources_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_inbound_source'),});
	// $('#rep_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_rep'),});
	// $('#call_status_select').multiselect({nonSelectedText: Lang.get('js_msgs.select_call_status'),});
// });