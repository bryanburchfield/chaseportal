var Campaign_Search = {

	first_search: true,
    active_camp_search: '',

	init:function(){
		Campaign_Search.eventHandlers();
	},

	 eventHandlers:function(){
        $('.campaign_search').on('keyup', this.search_campaigns);
        $('.filter_campaign').on('click', '.campaign_group', this.adjust_campaign_filters);
        $('.select_campaign').on('click', this.filter_campaign);
    },

	search_campaigns: function () {
	    var query = $(this).val();
	    var campaign_search_url = $(this).next('.campaign_search_url').val();

	    if (Campaign_Search.first_search) {
	        if ($('.filter_campaign li').hasClass('active')) {
	            Campaign_Search.active_camp_search = $('.filter_campaign li.active').text();
	        }
	    }

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: campaign_search_url,
	        type: 'POST',
	        dataType: 'json',
	        data: { query: query },
	        success: function (response) {

	            var is_array = Array.isArray(response.search_result);
	            var obj = response['search_result'];
	            $('.filter_campaign .checkbox').remove();
	            var campaign_searchresults = '';

	            if (!is_array) {
	                var obj = Object.keys(obj).map(function (key) {
	                    return [obj[key]];
	                });
	            }

	            var checked;

	            for (var i = 0; i < obj.length; i++) {
	                checked = obj[i].selected;
	                if (checked) { checked = 'checked'; } else { checked = ''; }
	                campaign_searchresults += '<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" ' + checked + ' value="' + obj[i].value + '" name="campaigns"><span>' + obj[i].name + '</span></label></div>';
	            }

	            Campaign_Search.first_search = false;

	            $('.filter_campaign').append(campaign_searchresults);
	        }
	    });
	},

	adjust_campaign_filters: function () {

	    // Get amount of selected checkboxes
	    var checked = [];
	    $('.campaign_label input:checked').each(function () {
	        checked.push($(this).attr('name'));
	    });

	    /// check if target is NOT All Camps
	    if ($(this).val() != '') {
	        // See if others are checked
	        if (checked.length) {
	            // check if All Camps is checked
	            if ($('.filter_campaign .campaign_group').eq(0).is(':checked')) {
	                // uncheck all camps because others are being selected
	                $('.filter_campaign .campaign_group').eq(0).removeAttr('checked');
	            }
	        }
	    } else { /// ALL camps is being checked
	        // check if All Camps was already checked
	        if ($('.filter_campaign .campaign_group').eq(0).is(':checked')) {
	            $('.filter_campaign .campaign_group').removeAttr('checked'); /// uncheck all other camps
	            $('.filter_campaign .campaign_group').eq(0).prop('checked', true); // recheck all camps
	        }

	        if (!checked.length) { // if nothing is selected reselect All Camps because something has to be checked
	            $('.filter_campaign .campaign_group').eq(0).prop('checked', true);
	        }
	    }
	},

	// ran when submit is clicked in the interaction menu
	filter_campaign: function () {

	    $('.preloader').show();

	    datefilter = $('#datefilter').val();
	    var checked = $(".campaign_group:checkbox:checked").length;
	    $('.alert').remove();
	    $('.campaign_search').val('');

	    if (checked) {
	        $('.filter_campaign').parent().removeClass('open');
	        $('.filter_campaign').prev('.dropdown-toggle').attr('aria-expanded', false);
	        var campaigns = [];
	        $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function () {
	            campaigns.push($(this).val());
	        });
	    }

	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });

	    $.ajax({
	        url: '/agentdashboard/update_filters',
	        type: 'POST',
	        dataType: 'json',
	        data: { campaign: campaigns },
	        success: function (response) {
	            Campaign_Search.set_campaigns(response);
	        }
	    });
	},

	// ran after submit is clicked in the interaction menu, after filter_campaign()
	set_campaigns: function (response) {
	    var campaigns = [];
	    $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function () {
	        campaigns.push($(this).val());
	        //// if total is selected, uncheck all checkboxes
	        if ($(this).val() == '') {
	            $('.filter_campaign .checkbox label input[name="campaigns"]:checkbox').removeAttr('checked');
	        }
	    });

	    var is_array = Array.isArray(response.campaigns);
	    var obj = response['campaigns'];
	    $('.filter_campaign .checkbox').remove();
	    var campaign_searchresults = '';

	    if (!is_array) {
	        var obj = Object.keys(obj).map(function (key) {
	            return [obj[key]];
	        });
	    }
	    var checked;

	    for (var i = 0; i < obj.length; i++) {
	        checked = obj[i].selected;
	        if (checked) { checked = 'checked'; } else { checked = ''; }
	        campaign_searchresults += '<div class="checkbox"><label class="campaign_label stop-propagation"><input class="campaign_group" required type="checkbox" ' + checked + ' value="' + obj[i].value + '" name="campaigns"><span>' + obj[i].name + '</span></label></div>';
	    }

	    $('.filter_campaign').append(campaign_searchresults);

	    Dashboard.refresh(datefilter);
	},
}


$(document).ready(function(){
	Campaign_Search.init();
});