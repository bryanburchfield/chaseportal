var Master = {

    chartColors: {
        red: 'rgb(255,67,77)',
        orange: 'rgb(228,154,49)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(51,160,155)',
        blue: 'rgb(1,1,87)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(68,68,68)'
    },

    page_menuitem: $('#sidebar').find('.page_menuitem').val(),
    curpage: '',
    pagesize: '',
    pag_link: '',
    sort_direction: '',
    th_sort: '',
    totpages: '',
    pdf_dl_link: '',
    first_search: true,
    active_camp_search: '',
    tick_color: '#aaa',
    gridline_color: '#1A2738',
    pinned_columns: 0,
    // left_cols:0,
    // right_cols:0,
    report_pinned_datatable: $('.report_pinned_datatable').DataTable({
        destroy: true,
        scrollY: 500,
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        responsive: true,
        fixedColumns: {
            leftColumns: this.left_cols,
            rightColumns: this.right_cols
        }
    }),

    activeTab: localStorage.getItem('activeTab'),
    dataTable: $('#dataTable').DataTable({
        responsive: true,
    }),
    cdr_dataTable: $('#cdr_dataTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'excelHtml5',
            'csvHtml5',
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'LEGAL'
            }
        ],
    }),

    init: function () {

        Master.left_cols = 0;
        Master.right_cols = 0;

        if ($('.theme').val() == 'dark') {
            Master.tick_color = '#aaa';
            Master.gridline_color = '#1A2738';
        } else {
            Master.tick_color = '#777';
            Master.gridline_color = '#e0e0e0';
        }

        if (Master.activeTab) {
            $('.nav.nav-tabs a[href="' + Master.activeTab + '"]').tab('show');
        }

        $('.pag').clone().insertAfter('div.table-responsive');
        $('.view_report_btn').on('click', this.view_report);
        $('.users table tbody, .rules_table tbody, .demo_user_table tbody').on('click', 'a.remove_user', this.pass_user_removemodal);
        $('.users table tbody').on('click', 'a.user_links', this.pass_user_linkmodal);
        $('form.report_filter_form').on('submit', this.submit_report_filter_form);
        $('.pag').on('click', '.pagination li a', this.click_pag_btn);
        $('body').on('click', '.reports_table thead th a span, .pinned_table table thead th a span', this.sort_table);
        $('body').on('dblclick', '.pinned_table thead th', this.pin_table_column);
        $('.numb_pinned_cols').on('change', this.select_pinned_columns);
        $('.pin_direction').on('change', this.set_pinned_direction);
        $('.pag').on('change', '.curpage, .pagesize', this.change_pag_inputs);
        $('.reset_sorting_btn').on('click', this.reset_table_sorting);
        $('#campaign_usage #campaign_select, #lead_inventory_sub #campaign_select').on('change', this.get_report_subcampaigns);
        $('#call_details #campaign_select, #lead_npa #campaign_select').on('change', this.toggle_subcamps);
        $('#lead_inventory_sub #campaign_select').on('change', this.multicamps_to_single_subs);
        $('.report_download').on('click', '.report_dl_option.pdf', this.pdf_download_warning);
        $('#report_dl_warning .dl_report').on('click', this.pdf_download2);
        $('body').on('change', '.query_dates_first .datetimepicker', this.query_dates_for_camps);
        $('#uploader_camp_info').on('submit', this.uploader_details);
        $('#settingsForm').on('submit', this.update_uploader_info);
        $('#file_upload').on('submit', this.upload_file);
        $('#import').on('submit', this.import);
        $('.card_dropbtn').on('click', this.toggle_dotmenu);
        $('.percentage').on('change', this.set_percentages);
        $('.select_database').on('click', this.select_database);
        $('.reports .switch input').on('click', this.toggle_automated_reports);
        $('a.getAppToken, textarea.url').on('click', this.copy_link);
        $('.date_filters li a').on('click', this.filter_date);
        $('.submit_date_filter').on('click', this.custom_date_filter);
        $('.btn.disable').on('click', this.preventDefault);
        $('.add_btn_loader').on('click', this.add_btn_loader);

        /// tool handlers
        $('.save_leadrule_update').on('click', this.save_leadrule_update);
        $('.add_esp').on('submit', this.add_esp);
        $('.edit_server_modal').on('click', this.edit_server_modal);
        $('.edit_esp').on('submit', this.update_esp);
        $('.test_connection').on('click', this.test_connection);
        $('.remove_email_service_provider_modal, .remove_campaign_modal').on('click', this.populate_delete_modal);
        $('.delete_email_service_provider').on('click', this.delete_esp);
        $('.create_campaign_form').on('submit', this.create_email_campaign);
        $('.drip_campaigns_campaign_menu').on('change', this.get_email_drip_subcampaigns);
        $('.delete_campaign ').on('click', this.delete_campaign);
        $('.provider_type').on('change', this.get_provider_properties);

        $('.add_email_campaign_filter').on('click', this.validate_filter);
        $('.filter_fields_div .form-control').on('change', this.validate_filter);
        $('.update_filters').on('submit', this.update_filters);
        $('.switch.email_campaign_switch input').on('click', this.check_campaign_filters);
        $('.filter_fields_cnt').on('click', '.remove_camp_filter', this.delete_camp_filter);
        $('.camp_filters_link').on('click', this.goto_camp_filters);
        $('.filter_fields_cnt').on('change', '.filter_fields', this.get_operators);
        $('.cancel_modal_form').on('click', this.cancel_modal_form);

        $('.sso #group_id').on('change', this.set_group);
        $('.sso #tz').on('change', this.set_timezone);
        $('body').on('click', '.toggle_active_reps input', this.toggle_active_reps);
        $('.switch.client_input input').on('click', this.toggle_active_client);

        $('#sidebar').on('click', '.update_nav_link', this.update_sidenav);
        $('#sidebar').on('click', '.back_to_sidenav', this.update_sidenav);
        $('.toggle_instruc, .toggle_instruc + h3').on('click', this.toggle_instructions);
    },

    update_sidenav: function (e) {
        e.preventDefault();
        if ($('.page_menuitem').val() != '' && Master.page_menuitem != undefined) {
            Master.page_menuitem = $('.page_menuitem').val();
        } else {
            $('.page_menuitem').each(function () {
                $(this).val(Master.page_menuitem);
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $('#sidebar').empty();

        var sidenav = $(this).data('path');
        $("html, body").animate({ scrollTop: 0 }, "slow");

        $.ajax({
            url: '/admin/load_sidenav',
            type: 'POST',
            dataType: 'html',
            data: { sidenav: sidenav },
            success: function (response) {
                $('#sidebar').append(response);
                $('ul.list-unstyled.components').find('li').each(function () {
                    if ($(this).data('page') == Master.page_menuitem) {
                        $(this).addClass('active');
                    }
                });
            }
        });
    },

    preventDefault: function (e) {
        e.preventDefault();
    },

    return_chart_colors_hash: function (reps) {

        var chart_colors_array = [];

        // old colorhash.js - keep just in case //////////////////////////////////////
        // var chart_colors_array=[];
        // var customHash = function(str) {
        //     var hash = 0;
        //     for(var i = 0; i < str.length; i++) {
        //         hash += str.charCodeAt(i);
        //     }

        //     return hash;
        // };

        // var colorHash = new ColorHash({hue: [ {min: 200, max: 255}, {min: 90, max: 205}, {min: 70, max: 150} ]});

        // var new_hash;
        // var new_rgb;
        // for (var i=0;i<reps.length;i++) {
        //     new_hash=colorHash.rgb(reps[i]);
        //     new_rgb="rgb("+new_hash[0]+","+new_hash[1]+","+new_hash[2]+")";
        //     chart_colors_array.push(new_rgb);
        // }

        // return chart_colors_array;
        // var colorHash = new ColorHash({lightness: [0.35, 0.5, 0.65]});

        // old colorhash.js - keep just in case //////////////////////////////////////

        // new string to color hash w/o colorhash.js

        // desaturate color
        function ColorLuminance(hex, lum) {

            // validate hex string
            hex = String(hex).replace(/[^0-9a-f]/gi, '');
            if (hex.length < 6) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            lum = lum || 0;

            // convert to decimal and change luminosity
            var rgb = "#", c, i;
            for (i = 0; i < 3; i++) {
                c = parseInt(hex.substr(i * 2, 2), 16);
                c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
                rgb += ("00" + c).substr(c.length);
            }

            return rgb;
        }

        var stringToColor = function (str) {
            var hash = 0;
            for (var i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }

            var color = '#';
            for (var i = 0; i < 3; i++) {
                var value = (hash >> (i * 8)) & 0xFF;
                color += ('00' + value.toString(16)).substr(-2);
            }
            if (color == '#000000') { color = '#57D897'; }
            return color;
        }

        var new_hash;
        var new_rgb;
        for (var i = 0; i < reps.length; i++) {
            new_hash = stringToColor(reps[i]);
            new_hash = ColorLuminance(new_hash, -0.3);
            chart_colors_array.push(new_hash);
        }
        return chart_colors_array;
    },

    formatNumber: function (x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    convertSecsToHrsMinsSecs: function (secs) {
        let sec_num = parseInt(secs, 10)
        let hours = Math.floor(sec_num / 3600)
        let minutes = Math.floor(sec_num / 60) % 60
        let seconds = sec_num % 60

        return [hours, minutes, seconds]
            .map(v => v < 10 ? "0" + v : v)
            .filter((v, i) => v || i > 0)
            .join(":")
    },

    add_bg_rounded_class: function (selector, data, limit) {
        if (data == 0 || data.toString().length < limit) {
            selector.addClass('bg_rounded');
        } else {
            selector.removeClass('bg_rounded');
        }
    },

    // check if array has data, if not print no data msg
    has_data: function (array) {
        for (var i = 0; i < array.length; i++) {
            if (array[i] != 0) {
                return true;
                break;
            }
        }
    },

    ylabel_format: function (data) {
        var show_decimal = false;

        for (var i = 0; i < data.length; i++) {
            if (data[i] > 300) {
                show_decimal = false;
                break;
            } else {
                show_decimal = true;
            }
        }

        return show_decimal;
    },

    flip_card: function (len, sel) {
        if (len < 15) {
            $(sel).closest('.flipping_card').flip(true);
        } else {
            $(sel).closest('.flipping_card').flip(false);
        }
    },

    trend_percentage: function (selector, change_perc, up_or_down, higher_is_better, not_comparable) {
        // if there is data to compare
        if (!not_comparable) {
            selector.find('.trend_indicator').show();
            selector.find('.trend_indicator span').text(change_perc + '%');
            selector.find('.trend_indicator').removeClass('up down');
            selector.find('.trend_arrow').removeClass('arrow_up arrow_down');

            // if(change_perc == 0){selector.find('.trend_arrow').hide();}

            // if higher is positve
            if (higher_is_better) {
                // if percentage is up
                if (up_or_down) {
                    selector.find('.trend_indicator').addClass('positive');
                    selector.find('.trend_arrow').addClass('arrow_up positive');
                } else { // if percentage is down
                    selector.find('.trend_indicator').addClass('negative');
                    selector.find('.trend_arrow').addClass('arrow_down negative');
                }
            } else { // if higher is negative
                // if percentage is up
                if (up_or_down) {
                    selector.find('.trend_indicator').addClass('negative');
                    selector.find('.trend_arrow').addClass('arrow_up negative');
                } else { // if percentage is down
                    selector.find('.trend_indicator').addClass('positive');
                    selector.find('.trend_arrow').addClass('arrow_down positive');
                }
            }
        } else {
            selector.find('.trend_indicator').hide();
        }
    },

    add_btn_loader: function () {
        $(this).find('i').remove();
        $(this).prepend('<i class="fa fa-spinner fa-spin mr10"></i>');
    },

    filter_date: function () {
        var that = $(this);

        that.parent().siblings().removeClass('active');
        that.parent().addClass('active');
        datefilter = that.data('datefilter');
        $('#datefilter').val(datefilter);
        var campaigns = [];
        $('.filter_campaign .checkbox label input[name="campaigns"]:checked').each(function () {
            campaigns.push(that.val());
        });

        Dashboard.datefilter = datefilter;

        if (datefilter != 'custom') {
            $('.preloader').show();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: '/dashboards/update_filters',
                type: 'POST',
                dataType: 'json',
                data: { dateFilter: datefilter },
                success: function (response) {
                    Master.set_campaigns(response);
                }
            });
        }
    },

    custom_date_filter: function () {
        $('.preloader').show();
        $('#datefilter_modal').hide();
        $('.modal-backdrop').hide();

        var start_date = $('.startdate').val(),
            end_date = $('.enddate').val()
            ;
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        datefilter = start_date + ' ' + end_date;

        $('.startdate').val('');
        $('.enddate').val('');
        $('#datefilter_modal').modal('toggle');
        $('#datefilter').val(start_date + ' ' + end_date);
        Dashboard.datefilter = datefilter;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/dashboards/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { dateFilter: datefilter },
            success: function (response) {
                Master.set_campaigns(response);

            }
        });
    },

    get_report_subcampaigns:function(e, campaign=0, source=0){

        if(!campaign){
            $(this).find('option:selected').each(function() {
                campaign = $(this).val();
            });
        }

        if (!source) { var source = $(this).attr('id'); }
        var report = $('form.report_filter_form').attr('id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/dashboards/reports/get_subcampaigns',
            type: 'POST',
            dataType: 'json',
            data: {
                report: report,
                campaign: campaign,
            },

            success: function (response) {

                var subcampaigns = '<option value=""> Select One</option>';
                for (var i = 0; i < response.subcampaigns.length; i++) {
                    subcampaigns += '<option value="' + response.subcampaigns[i] + '">' + response.subcampaigns[i] + '</option>';
                }

                if (source == 'destination_campaign' || source == 'update_destination_campaign' || source == 'update_campaign_select') {
                    $('#' + source).parent().next().find('select').empty();
                    $('#' + source).parent().next().find('select').append(subcampaigns);
                } else {
                    $('#subcampaign_select').empty();
                    $('#subcampaign_select').append(subcampaigns);
                }
            }
        });
    },

    multicamps_to_single_subs:function(e){
        e.preventDefault();

        var campaign = $(this).val();
        var report = $('form.report_filter_form').attr('id');

        if(campaign){
            $('#subcampaign_select').parent().parent().show();
            ///// build subcamps menu
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: '/dashboards/reports/get_subcampaigns' ,
                type: 'POST',
                dataType: 'json',
                data: {
                    report:report,
                    campaign: campaign,
                },

                success:function(response){

                    var subcamp_obj = response.subcampaigns;
                    var subcamp_obj_length = Object.keys(subcamp_obj).length;
                    const subcamp_obj_keys = Object.getOwnPropertyNames(subcamp_obj);
                    let subcampaigns_array = [];
                    subcampaigns_array.push(Object.values(subcamp_obj));

                    $('#subcampaign_select').empty();

                    var subcampaigns='';
                    for (var i = 0; i < subcampaigns_array[0].length; i++) {
                        subcampaigns += '<option value="' + subcampaigns_array[0][i] + '">' + subcampaigns_array[0][i] + '</option>';
                    }

                    $('#subcampaign_select').append(subcampaigns);
                    $("#subcampaign_select").multiselect('rebuild');
                    $("#subcampaign_select").multiselect('refresh');

                    $('#subcampaign_select')
                        .multiselect({ nonSelectedText: '', })
                        .multiselect('selectAll', true)
                        .multiselect('updateButtonText');

                }
            });
        }else{
            $('#subcampaign_select').empty();
            $('#subcampaign_select').parent().parent().hide();
        }
    },

    toggle_subcamps:function(e){
        e.preventDefault();

        if ($(this).find('option:selected').length == 1 && $(this).find('option:selected').val() != undefined) {
            var campaign = $(this).val();
            campaign = campaign[0];
            var report = $('form.report_filter_form').attr('id');
        }

        if (campaign) {
            $('#subcampaign_select').parent().parent().show();

            ///// build subcamps menu
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            console.log(campaign);
            $.ajax({
                url: '/dashboards/reports/get_subcampaigns',
                type: 'POST',
                dataType: 'json',
                data: {
                    report: report,
                    campaign: campaign,
                },

                success: function (response) {

                    var subcamp_obj = response.subcampaigns;
                    var subcamp_obj_length = Object.keys(subcamp_obj).length;
                    const subcamp_obj_keys = Object.getOwnPropertyNames(subcamp_obj);
                    let subcampaigns_array = [];
                    subcampaigns_array.push(Object.values(subcamp_obj));

                    $('#subcampaign_select').empty();

                    var subcampaigns = '';
                    for (var i = 0; i < subcampaigns_array[0].length; i++) {
                        subcampaigns += '<option value="' + subcampaigns_array[0][i] + '">' + subcampaigns_array[0][i] + '</option>';
                    }

                    $('#subcampaign_select').append(subcampaigns);
                    $("#subcampaign_select").multiselect('rebuild');
                    $("#subcampaign_select").multiselect('refresh');

                    $('#subcampaign_select')
                        .multiselect({ nonSelectedText: '', })
                        .multiselect('selectAll', true)
                        .multiselect('updateButtonText');

                }
            });
        } else {
            $('#subcampaign_select').empty();
            $('#subcampaign_select').parent().parent().hide();
        }
    },

    get_subcampaigns: function (campaign, path = '') {

        if (!path) {
            path = '/tools/contactflow_builder/get_subcampaigns';
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        return $.ajax({
            url: path,
            type: 'POST',
            dataType: 'json',
            async: false,
            data: {
                campaign: campaign,
            },

            success: function (response) {
                return response;
            },
            error: function () { }
        });
    },

    get_email_drip_subcampaigns: function (e, campaign) {

        var sel;
        if (e.type == 'click') {
            sel = $('.edit_campaign_form');
            campaign = $('.edit_campaign_form').find('.drip_campaigns_campaign_menu').val();
        } else {
            if ($(e.target).parent().parent().hasClass('edit_campaign_form')) {
                sel = $('.edit_campaign_form');
                campaign = $(this).val();
            } else {
                campaign = $(this).val();
                sel = $('.create_campaign_form');
            }
        }

        var subcamp_response = Master.get_subcampaigns(campaign, '/tools/email_drip/get_subcampaigns');
        $('.drip_campaigns_subcampaign').empty();
        $(sel).find('.email').empty();

        var subcamp_obj = subcamp_response.responseJSON.subcampaigns;
        var subcamp_obj_length = Object.keys(subcamp_obj).length;
        const subcamp_obj_keys = Object.getOwnPropertyNames(subcamp_obj);
        let subcampaigns_array = [];
        subcampaigns_array.push(Object.values(subcamp_obj));

        $('.drip_campaigns_subcampaign').empty();

        var subcampaigns = '';
        for (var i = 0; i < subcampaigns_array[0].length; i++) {
            subcampaigns += '<option value="' + subcampaigns_array[0][i] + '">' + subcampaigns_array[0][i] + '</option>';
        }

        $('.drip_campaigns_subcampaign').append(subcampaigns);
        $(".drip_campaigns_subcampaign").multiselect('rebuild');
        $(".drip_campaigns_subcampaign").multiselect('refresh');

        $('.drip_campaigns_subcampaign')
            .multiselect({ nonSelectedText: '', })
            .multiselect('selectAll', true)
            .multiselect('updateButtonText');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/get_table_fields',
            type: 'POST',
            dataType: 'json',
            async: false,
            data: {
                campaign: campaign,
            },

            success: function (response) {

                var emails = '<option value="">Select One</option>';
                for (var index in response) {
                    emails += '<option value="' + index + '">' + index + '</option>';
                }

                $(sel).find('.email').append(emails);
            },
        });
    },

    get_leadrule_subcampaigns: function () {

        var campaign = $(this).val();
        var selector = $(this).attr('id');
        var subcamp_response = Master.get_subcampaigns(campaign);

        var subcampaigns = '<option value=""> Select One</option>';
        for (var i = 0; i < subcamp_response.responseJSON.subcampaigns.length; i++) {
            subcampaigns += '<option value="' + subcamp_response.responseJSON.subcampaigns[i] + '">' + subcamp_response.responseJSON.subcampaigns[i] + '</option>';
        }

        if (selector == 'campaign_select' || selector == 'update_campaign_select') {
            $('#subcamps').empty();
            $('#subcamps').append(subcampaigns);
        } else if (selector == 'destination_campaign' || selector == 'update_destination_campaign') {
            $('#destination_subcampaign').empty();
            $('#destination_subcampaign').append(subcampaigns);
        }
    },

    toggle_leadrule: function () {
        var checked;
        var ruleid = $(this).parent().parent().parent().data('ruleid');

        if ($(this).is(':checked')) {
            $(this).attr('Checked', 'Checked');
            checked = 1;
        } else {
            $(this).removeAttr('Checked');
            checked = 0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/contactflow_builder/toggle_rule',
            type: 'POST',
            data: {
                checked: checked,
                id: ruleid

            },
            success: function (response) {
            }
        });
    },

    toggle_email_campaign: function (e, campaign_id) {

        var checked;
        // var campaign_id = $(campaign_id).data('id');

        if ($(campaign_id).is(':checked')) {
            $(campaign_id).attr('Checked', 'Checked');
            checked = 1;
        } else {
            $(campaign_id).removeAttr('Checked');
            checked = 0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/toggle_email_campaign',
            type: 'POST',
            data: {
                checked: checked,
                id: campaign_id

            },
            success: function (response) {
            }
        });
    },

    get_leadrule_filter_menu: function () {
        var filters = [];
        $('.lead_rule_filter_type option').each(function () {
            if ($(this).val() != '') {
                filters.push($(this).val());
            }
        });
        return filters;
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

    select_database: function (e) {
        e.preventDefault();

        var type = $('.page_type').val();
        var checked = $(".database_group:checkbox:checked").length;
        $('.alert').remove();
        if (checked) {
            $(this).parent().parent().removeClass('open');
            $('.db_select .dropdown').removeClass('open');
            $('.db_select .dropdown-toggle').attr('aria-expanded', false);
            var databases = [];
            $('input[name="databases"]:checked').each(function () {
                databases.push($(this).val());
            });

            if (type != 'report') {
                Master.set_databases(databases);
            } else {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    url: 'set_database',
                    type: 'POST',
                    dataType: 'json',
                    data: { databases: databases },
                    success: function (response) {
                        $('.preloader').fadeOut('slow');
                    }
                });
            }

        } else {
            $(this).parent().parent().addClass('open');
            $('.db_select .dropdown-toggle').attr('aria-expanded', true);
            $('.db_select').append('<div class="alert alert-danger">At least one database must be selected</div>');
        }
    },

    set_databases: function (databases) {
        Dashboard.databases = databases;
        var campaign = $('.filter_campaign li').hasClass('active');
        campaign = $(campaign).find('a').text();
        var datefilter = $('#datefilter').val();
        $('.preloader').show();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/dashboards/update_filters',
            type: 'POST',
            dataType: 'json',
            data: { databases: databases },
            success: function (response) {
                Dashboard.refresh(datefilter);
            }
        });
    },

    toggle_automated_reports: function () {
        var active;
        var report = $(this).parent().parent().parent().data('report');

        if ($(this).is(':checked')) {
            $(this).attr('Checked', 'Checked');
            active = 1;
        } else {
            $(this).removeAttr('Checked');
            active = 0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'toggle_automated_report',
            type: 'POST',
            data: {
                active: active,
                report: report
            },
            success: function (response) {
            }
        });
    },

    upload_file: function (e) {
        e.preventDefault();
        $('#file_upload').parent().find('.alert').remove();

        function hasExtension(inputID, exts) {
            var fileName = document.getElementById(inputID).value;
            return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
        }

        if (!hasExtension('file', ['.csv'])) {
            $('#file_upload').parent().append('<div class="alert alert-danger col-sm-4">Invalid File Format. Please use .csv.</div>');
            return false;
        }

        var form = document.getElementById('file_upload');
        var formData = new FormData(form);

        formData.append('file', file);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.getAttribute('action'), true);
        xhr.send(formData);
        xhr.onload = function () {
            if (xhr.readyState === xhr.DONE) {

                if (xhr.status === 200) {
                    var obj = JSON.parse(xhr.responseText);

                    if (obj.errors[0]) {
                        $('#file_upload').parent().append('<div class="alert alert-danger col-sm-4">Invalid CSV contents.</div>');
                        return false;
                    }
                    $('.imported_data_field').val(xhr.responseText);
                    Master.initCSVTable(obj.data.contents);
                }
            }
        };
        return false;
    },

    initCSVTable: function (_data) {
        var data = [];
        var array_keys = [], array_values = [];

        for (i = 0; i < _data.length; i++) {
            array_keys = [];
            array_values = [i + 1];
            for (var key in _data[i]) {
                array_keys.push(key);
                array_values.push(_data[i][key]);
            }
            data.push(array_values);
        }
        Master.initTableHeader(array_keys);

        Master.dataTable.clear();
        Master.dataTable.rows.add(data);
        Master.dataTable.draw();
    },

    initSettingTable: function (_data) {
        var i, html = "";
        html += "<tr>";
        html += "<td>BT07.chasedatacorp.com</td>";
        html += "<td>" + _data['Campaign_A'] + "</td>";
        html += "<td>" + _data['Subcampaign_A'] + "</td>";
        html += "<td>" + _data['Rate_A'] + "</td>";
        html += "</tr>";

        html += "<tr>";
        html += "<td>BT15.chasedatacorp.com</td>";
        html += "<td>" + _data['Campaign_B'] + "</td>";
        html += "<td>" + _data['Subcampaign_B'] + "</td>";
        html += "<td>" + _data['Rate_B'] + "</td>";
        html += "</tr>";

        $('#settingsTable tbody').html(html);
    },

    initTableHeader: function (arr) {

        Master.dataTable.clear();
        Master.dataTable.destroy();
        var html = "<tr><th>#</th>";
        for (var i = 0; i < arr.length; i++) {
            html += "<th>" + arr[i] + "</th>";
        }
        $('#dataTable thead').html(html);

        Master.dataTable = $('#dataTable').DataTable({
            responsive: true,
        });
    },

    update_uploader_info: function (e) {
        e.preventDefault();

        var form_data = $(this).serialize();
        $('#settingModal').modal('toggle');
        var id = $(this).data('campaignid');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'uploader_action',
            type: 'POST',
            dataType: 'json',
            data: {
                form_data: form_data
            },
            success: function (response) {
                Master.set_uploader_info(response);
            }
        });
    },

    uploader_details: function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'uploader_action',
            type: 'POST',
            dataType: 'json',
            data: {
                form_data: form_data
            },
            success: function (response) {
                if (response.data.status == 'success') {
                    $('.uploader_part1').hide();
                    $('.uploader_part2').show();

                    Master.set_uploader_info(response);
                } else {
                    var errors;
                    for (var i = 0; i < response.errors; i++) {
                        errors += '<p>' + response.errors[i] + '</p>';
                    }
                    $('.errors').append('<div class="alert alert-danger">' + errors + '</div>');
                }
            }
        });
    },

    set_uploader_info: function (response) {

        $('td.uploader_details').remove();
        var server1_dets = '<td class="uploader_details">' + response.data.Campaign_A + '</td><td class="uploader_details">' + response.data.Subcampaign_A + '</td><td class="uploader_details">' + response.data.Rate_A + '%</td>';
        var server2_dets = '<td class="uploader_details">' + response.data.Campaign_B + '</td><td class="uploader_details">' + response.data.Subcampaign_B + '</td><td class="uploader_details">' + response.data.Rate_B + '%</td>';
        $('#settingsTable .server1').append(server1_dets);
        $('#settingsTable .server2').append(server2_dets);

        $('input[name="Campaign_A"]').val(response.data.Campaign_A);
        $('input[name="Campaign_B"]').val(response.data.Campaign_B);
        $('input[name="Subcampaign_A"]').val(response.data.Subcampaign_A);
        $('input[name="Subcampaign_B"]').val(response.data.Subcampaign_B);
        $('input[name="Rate_A"]').val(response.data.Rate_A);
        $('input[name="Rate_B"]').val(response.data.Rate_B);
    },

    import: function (e) {
        e.preventDefault();
        var contents = JSON.parse($('.imported_data_field').val());
        contents = contents.data.contents;
        var action = $('#import .input_action').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: 'uploader_action',
            type: 'POST',
            dataType: 'json',
            data: {
                // contents:contents,
                action: action
            },
            success: function (response) {
                $('div.uploader_msg').empty();
                if (response.data.status == 'success') {
                    $('div.uploader_msg').append('<div class="alert alert-success">Uploaded Successfully</div>');
                } else {
                    $('div.uploader_msg').append('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                }
            }
        });
    },

    // populate campaign multi-select based on dates
    query_dates_for_camps: function () {

        var todate = $('.todate').val(),
            fromdate = $('.fromdate').val()
        report = $('form.report_filter_form').attr('id')
            ;

        if (todate != '' && fromdate != '') {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url: '/dashboards/reports/get_campaigns',
                type: 'POST',
                dataType: 'json',
                async: false, /////////////////////// use async when rebuilding multi select menus
                data: {
                    report: report,
                    todate: todate,
                    fromdate: fromdate
                },

                success: function (response) {

                    $('#campaign_select').empty();
                    var camps_select;
                    for (var i = 0; i < response.campaigns.length; i++) {
                        camps_select += '<option value="' + response.campaigns[i] + '">' + response.campaigns[i] + '</option>';
                    }

                    $('#campaign_select').append(camps_select);
                    $("#campaign_select").multiselect('rebuild');
                    $("#campaign_select").multiselect('refresh');

                    $('#' + report + ' #campaign_select')
                        .multiselect({ nonSelectedText: Lang.get('js_msgs.select_campaign'), })
                        .multiselect('selectAll', true)
                        .multiselect('updateButtonText');
                }
            });
        }
    },

    pdf_download_warning: function (e) {
        e.preventDefault();
        var tot_rows = parseInt($('.totrows').val());
        $('#report_dl_warning .modal-footer .dl_report').show();

        if (tot_rows > 1000 && tot_rows < 2000) {
            $('#report_dl_warning').modal('toggle');
            $('.dl_alert.alert').removeClass('alert-danger');
            $('.dl_alert.alert').addClass('alert-warning');
            $('.dl_alert.alert p').text(Lang.get('js_msgs.dl_warning'));
            $('#report_dl_warning .modal-footer .dl_report').show();
        } else if (tot_rows >= 2000) {
            $('.dl_alert.alert').removeClass('alert-warning');
            $('.dl_alert.alert').addClass('alert-danger');
            $('.dl_alert.alert p').text(Lang.get('js_msgs.large_dl_warning'));
            $('#report_dl_warning .modal-footer .dl_report').hide();
            $('#report_dl_warning').modal('toggle');
        } else {
            pdf_dl_link = $('.report_dl_option.pdf').attr('href');
            window.open(pdf_dl_link, '_blank');
        }
    },

    pdf_download2: function () {
        pdf_dl_link = $('.report_dl_option.pdf').attr('href');
        window.open(pdf_dl_link);
        $('#report_dl_warning').modal('hide');
        $('.modal-backdrop').remove();
    },

    return_chart_colors: function (response_length, chartColors) {
        const chart_colors = Object.keys(Master.chartColors)
        var chart_colors_array = [];

        var j = 0;
        for (var i = 0; i < response_length; i++) {
            if (j == chart_colors.length) {
                j = 0;
            }
            chart_colors_array.push(eval('chartColors.' + chart_colors[j]));
            j++;
        }

        return chart_colors_array;
    },

    /// Delete user / dmeo users and delete lead rule modals
    pass_user_removemodal: function () {
        var id = $(this).data('user');
        var name = $(this).data('name');

        $('#deleteUserModal .user_id, #deleteRuleModal .lead_rule_id').val(id);
        $('#deleteUserModal .name, #deleteRuleModal .name').val(name);
        $('#deleteUserModal .username, #deleteRuleModal .rule_name').html(name);
    },

    pass_user_linkmodal: function () {
        var id = $(this).data('user'),
            name = $(this).data('name'),
            app_token = $(this).data('token')
            ;

        $('#userLinksModal .user_id').val(id);
        $('#userLinksModal .name').val(name);
        $('#userLinksModal .app_token').val(app_token);
        $('a.getAppToken span.url_token').text(app_token);
        $('#userLinksModal .username').html(id + ' ' + name);
    },

    copy_link: function (e) {
        e.preventDefault();

        $(this).tooltip({
            animated: 'fade',
            placement: 'left',
            trigger: 'click'
        });

        setTimeout(function () {
            $('.tooltip').fadeOut('slow');
        }, 3500);

        var $temp = $("<input>");
        $(this).parent().append($temp);
        $temp.val($(this).text()).select();
        document.execCommand("copy");
        $temp.remove();
    },

    // select report from modal
    view_report: function () {
        $('.alert').hide();
        var selected_report = $('input.report_option:checked').val();

        if (selected_report != '' && selected_report != undefined) {
            window.location.href = "/dashboards/reports/" + selected_report;

        } else {
            $('#reports_modal .modal-footer').append('<div class="alert alert-danger"><p>Please select a report</p></div>');
        }
    },

    // filter form submission
    submit_report_filter_form: function (e) {
        e.preventDefault();
        $('.preloader').show();

        $([document.documentElement, document.body]).animate({
            scrollTop: $(".table-responsive").offset().top - 100
        }, 1500);

        Master.update_report('', '', 1, '', '');
    },

    // click a pagination button
    click_pag_btn: function (e) {
        e.preventDefault();

        if (!$(this).parent().hasClass('disabled')) {
            this.curpage = $('.curpage').val();
            this.pagesize = $('.pagesize').val();
            this.pag_link = $(this).data('paglink');
            this.sort_direction = $('.sort_direction').text();
            this.th_sort = $('.sorted_by').text();
            Master.update_report(this.th_sort, this.pagesize, this.curpage, this.pag_link, this.sort_direction);
        }
    },

    // sort by clicking th
    sort_table: function (e) {
        e.preventDefault();
        $('.preloader').show();

        var sortedby_parent = $(this).parent().parent();
        console.log(sortedby_parent);

        this.th_sort = $(sortedby_parent).text();
        $(sortedby_parent).siblings().find('a span').show();
        $(sortedby_parent).siblings().find('a span').removeClass('active');
        $(sortedby_parent).siblings().removeClass('active_column');
        $(sortedby_parent).addClass('active_column');
        $(this).siblings().hide();
        this.curpage = 1
        this.pagesize = 50;
        this.sort_direction = $(this).attr('class');

        if ($(this).hasClass('active')) {
            $(this).siblings().show();
            $(this).removeClass('active');
            $(this).siblings().addClass('active');
            $(this).hide();
            this.sort_direction = $(this).siblings().attr('class').split(' ')[0];
        } else {
            $(this).addClass('active');
        }

        Master.update_report(this.th_sort, this.pagesize, this.curpage, '', this.sort_direction);
    },

    // check if pag input values have changed
    change_pag_inputs: function () {
        var max_pages = parseInt($('.curpage').attr('max')),
            totrows = parseInt($('.totrows').val()),
            pagesize = parseInt($('.pagesize').data('prevval')),
            new_pagesize = parseInt($('.pagesize').val())
            ;

        this.curpage = parseInt($('.curpage').val());
        this.sort_direction = $('.sort_direction').text();
        this.th_sort = $('.sorted_by').text();

        // check if page input is greater than max available pages
        if (parseInt($(this).val()) > max_pages && $(this).hasClass('curpage')) {
            var prevval = $(this).data('prevval');
            this.curpage = prevval;
            $('div.errors').text('Attempted page number greater than available pages').show(0).delay(4500).hide(0);
            return false;
        } else {
            if ($(this).hasClass('curpage')) {
                this.curpage = $(this).val();
            }

            // if users changes pagesize set curpage back to 1
            if (pagesize != new_pagesize) {
                this.curpage = 1;
            }

            if ($(this).hasClass('pagesize')) {
                this.pagesize = $(this).val();
                $('.pagesize').val(this.pagesize);
            }

            Master.update_report(this.th_sort, this.pagesize, this.curpage, '', this.sort_direction);
        }
    },

    // reset table sorting
    reset_table_sorting: function (e) {
        e.preventDefault();
        this.curpage = 1;
        this.pagesize = 50;
        // $(this).prev('h3').text('Not sorted');
        Master.update_report('', this.pagesize, this.curpage, '', '');
    },

    update_report: function (th_sort = '', pagesize = '', curpage = '', pag_link = '', sort_direction = '') {

        var form_data = $('form.report_filter_form').serialize(),
            report = $('#report').val(),
            pagesize = $('.pagesize').val()
            ;

        if (curpage == '') { curpage = $('.curpage').val(); }
        if (report == '') { report = $('#report').val(); }
        if (curpage != pag_link && pag_link != '') { curpage = pag_link; }
        if (th_sort == pag_link) { th_sort = ''; }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/dashboards/reports/update_report',
            type: 'POST',
            dataType: 'json',
            timeout: 600000,
            data: {
                curpage: curpage,
                pagesize: pagesize,
                th_sort: th_sort,
                sort_direction: sort_direction,
                form_data: form_data,
                report: report
            },

            success: function (response) {
                if ($('#sidebar').hasClass('active')) {
                    $('#sidebar').removeClass('active');
                }

                Master.draw_pinned_datatable(0);

                // hide / empty everything and run report
                $('.report_table, .pag, .report_errors').empty();
                $('.report_download, .reset_sorting, .pag, .preloader, .report_errors').hide();
                $('.total_leads, .available_leads').html('');

                // check for errors
                if (response.errors.length >= 1) {
                    for (var i = 0; i < response.errors.length; i++) {
                        $('.report_errors').show();
                        $('.report_errors').append(response.errors[i] + '<br>');
                    }
                    $('.table-responsive.report_table').hide();
                    $('alert.hidetilloaded').hide();

                    return false;
                }

                // check for result by counting total page
                if (response.params.totrows) {

                    this.totpages = response.params.totpages;
                    this.curpage = response.params.curpage;
                    this.th_sort = th_sort;
                    this.sort_direction = response.params.orderby.Campaign;

                    // append table
                    $('.table-responsive').append(response.table).show();

                    // show download options
                    $('.report_download').show();

                    // set active class to the th that was sorted
                    for (var i = 0; i < $('.reports_table thead th').length; i++) {
                        if ($('.reports_table thead th:eq(' + i + ')').text() == this.th_sort) {
                            $('.reports_table thead th:eq(' + i + ')').addClass('active_column');
                            $('.reports_table thead th:eq(' + i + ')').find('span.' + sort_direction).addClass('active');
                        }
                    }

                    // pagination - show pag if more than one page
                    if (response.params.totpages > 1) {
                        $('.pag').append(response.pag).show();
                        $('.pagination').find('li').removeClass('active');
                        $('.pagination li a[data-paglink="' + this.curpage + '"]').parent().addClass('active');
                    } else {
                        $('.pag').append('<br><br><br>' + response.pag).show();
                        $('.pag').first().find('.pag_dets').addClass('mt50');
                    }

                    // show sort order and reset button if sorting is active
                    if (this.th_sort) {
                        $('.reset_sorting h3').html(Lang.get('js_msgs.sorted_in') + ' <span class="sort_direction">' + sort_direction + '</span> ' + Lang.get('js_msgs.sorted_in') + ' <span class="sorted_by">' + this.th_sort + '</span>');
                        $('.reset_sorting').show();
                    }
                }

                // $('.reports_table').bootstrapTable('destroy').bootstrapTable({
                //     showFullscreen: false,
                //     search: false,
                //     stickyHeader: true,
                //     stickyHeaderOffsetLeft: '20px',
                //     stickyHeaderOffsetRight: '20px',
                // });

                $('.table-responsive.report_table').show();

                if (response.params.report == 'lead_inventory' || response.params.report == 'lead_inventory_sub') {
                    $('.total_leads').html('<b>' + Lang.get('js_msgs.total_leads') + ': ' + response.extras.TotalLeads + '</b>');
                    $('.available_leads').html('<b>' + Lang.get('js_msgs.available_leads') + ': ' + response.extras.AvailableLeads  + '</b>');
                }

                if (response.params.report == 'calls_per_hour') {
                    Master.calls_per_hour(response);
                }

                if (response.params.report == 'campaign_usage') {
                    Master.campaign_usage(response);
                }

                if (response.params.report == 'campaign_call_log') {
                    Master.campaign_call_log(response);
                }

                if (response.params.report == 'caller_id') {
                    Master.caller_id(response);
                }

                if (response.params.report == 'bwr_campaign_call_log') {
                    Master.campaign_call_log(response);
                }
            }, error: function (jqXHR, textStatus) {
                if (textStatus === 'timeout') {
                    alert('Failed from timeout, please refresh your browser');
                }
            },
        }); /// end ajax
    }, /// end update_report function


    calls_per_hour: function (response) {

        $('.hidetilloaded').show();
        var chartColors = Master.chartColors;

        var drop_abandon_counts = {

            labels: response.extras.Date,
            datasets: [
                {
                    label: Lang.get('js_msgs.inbound'),
                    borderColor: chartColors.orange,
                    backgroundColor: chartColors.orange,
                    fill: false,
                    data: response.extras.Inbound,
                    yAxisID: 'y-axis-1'
                },
                {
                    label: Lang.get('js_msgs.abandoned'),
                    borderColor: chartColors.green,
                    backgroundColor: chartColors.green,
                    fill: false,
                    data: response.extras.Abandoned,
                    yAxisID: 'y-axis-1',
                },
                {
                    label: Lang.get('js_msgs.outbound'),
                    borderColor: chartColors.grey,
                    backgroundColor: chartColors.grey,
                    fill: false,
                    data: response.extras.Outbound,
                    yAxisID: 'y-axis-1'
                },
                {
                    label: Lang.get('js_msgs.dropped'),
                    borderColor: chartColors.blue,
                    backgroundColor: chartColors.blue,
                    fill: false,
                    data: response.extras.Dropped,
                    yAxisID: 'y-axis-1'
                }
            ]
        };

        var drop_abandon_counts_options = {
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            scales: {
                xAxes: [{
                    ticks: {
                        fontColor: Master.tick_color,
                    },
                    gridLines: {
                        color: Master.gridline_color,
                    },
                }],
                yAxes: [{
                    gridLines: {
                        color: Master.gridline_color,
                    },
                    ticks: {
                        fontColor: Master.tick_color,
                    },
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                }, {
                    type: 'linear',
                    display: false,
                    id: 'y-axis-2',

                    gridLines: {
                        drawOnChartArea: false,
                    },
                }],
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    fontColor: Master.tick_color,
                }
            }
        }

        ///////////////// CALL VOLUME GRAPH
        var ctx = document.getElementById('drop_abandon_counts').getContext('2d');
        if (window.drop_abandon_counts_chart != undefined) {
            window.drop_abandon_counts_chart.destroy();
        }
        window.drop_abandon_counts_chart = new Chart(ctx, {
            type: 'line',
            data: drop_abandon_counts,
            options: drop_abandon_counts_options
        });
    },

    campaign_usage: function (response) {
        $('.hidetilloaded').show();
        var chartColors = Master.chartColors;

        var xaxis_labels = [];
        for (var i = 0; i < response.extras.callable.length; i++) {
            xaxis_labels.push(i);
        }

        var leads_by_attempt_data = {
            labels: xaxis_labels,
            datasets: [
                {
                    label: Lang.get('js_msgs.callable'),
                    backgroundColor: chartColors.green,
                    data: response.extras.callable
                },
                {
                    label: Lang.get('js_msgs.non_callable'),
                    backgroundColor: chartColors.orange,
                    data: response.extras.noncallable
                }
            ]
        };

        var leads_by_attempt_options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            },
            scales: {

                xAxes: [{
                    stacked: true,
                }],
                yAxes: [{
                    stacked: true,
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }

        var ctx = document.getElementById('leads_by_attempt').getContext('2d');

        if (window.leads_by_attempt_chart != undefined) {
            window.leads_by_attempt_chart.destroy();
        }

        window.leads_by_attempt_chart = new Chart(ctx, {
            type: 'bar',
            data: leads_by_attempt_data,
            options: leads_by_attempt_options
        });

        if (window.subcampaigns_chart != undefined) {
            window.subcampaigns_chart.destroy();
        }

        var response_length = response.extras.subcampaigns.length;
        var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

        var subcampaigns = [];
        var subcampaigns_cnt = [];
        for (var i = 0; i < response.extras.subcampaigns.length; i++) {
            subcampaigns_cnt.push(response.extras.subcampaigns[i].Cnt);
            subcampaigns.push(response.extras.subcampaigns[i].Subcampaign);
        }

        $('#subcampaigns').parent().find('.card_title').remove();
        $('#subcampaigns').parent().find('.no_data').remove();

        if (response_length) {
            var subcampaigns_data = {
                datasets: [{
                    data: subcampaigns_cnt,
                    backgroundColor: chart_colors_array,
                    label: 'Dataset 1'
                }],
                elements: {
                    center: {
                        color: '#203047',
                        fontStyle: 'Segoeui',
                        sidePadding: 15
                    }
                },
                title: {
                    fontColor: '#203047',
                    fontSize: 16,
                    display: true,
                    text: Lang.get('js_msgs.callable_leads_by_sub')
                },
                labels: subcampaigns
            };

            var subcampaigns_options = {
                responsive: true,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: true,
                }, title: {
                    fontColor: '#203047',
                    fontSize: 16,
                    display: true,
                    text: Lang.get('js_msgs.callable_leads_by_sub')
                },
            }

            var ctx = document.getElementById('subcampaigns').getContext('2d');

            window.subcampaigns_chart = new Chart(ctx, {
                type: 'doughnut',
                data: subcampaigns_data,
                options: subcampaigns_options
            });
        } else {
            $('#subcampaigns').empty();
            $('<p class="no_data">' + Lang.get('js_msgs.no_data') + '</p>').insertBefore('#subcampaigns');
        }

        if (window.call_stats_chart != undefined) {
            window.call_stats_chart.destroy();
        }

        var response_length = response.extras.callstats.length;
        var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

        var call_stats = [];
        var call_stats_cnt = [];
        for (var i = 0; i < response.extras.callstats.length; i++) {
            call_stats_cnt.push(response.extras.callstats[i].Cnt);
            call_stats.push(response.extras.callstats[i].CallStatus);
        }

        $('#call_stats').parent().find('.card_title').remove();
        $('#call_stats').parent().find('.no_data').remove();

        if (response_length) {
            var call_stats_data = {
                datasets: [{
                    data: call_stats_cnt,
                    backgroundColor: chart_colors_array,
                    label: 'Dataset 1'
                }],
                elements: {
                    center: {
                        color: '#203047',
                        fontStyle: 'Segoeui',
                        sidePadding: 15
                    }
                },
                title: {
                    fontColor: '#203047',
                    fontSize: 16,
                    display: true,
                    text: Lang.get('js_msgs.non_callable_by_disp')
                },
                labels: call_stats
            };

            var call_stats_options = {
                responsive: true,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: true,
                }, title: {
                    fontColor: '#203047',
                    fontSize: 16,
                    display: true,
                    text: Lang.get('js_msgs.non_callable_by_disp')
                },
            }

            var ctx = document.getElementById('call_stats').getContext('2d');

            window.call_stats_chart = new Chart(ctx, {
                type: 'doughnut',
                data: call_stats_data,
                options: call_stats_options
            });
        } else {
            $('#call_stats').empty();
            $('<p class="no_data">' + Lang.get('js_msgs.no_data') + '</p>').insertBefore('#call_stats');
        }

    },

    campaign_call_log: function (response) {

        $('.rm_rptble_class').find('table').removeClass('reports_table');
        $('.rm_rptble_class table th').find('a').remove();
        $('.hidetilloaded').show();

        $('.total_reps').find('h4').text(response.extras.summary.TotReps);
        $('.man_hours').find('h4').text(response.extras.summary.ManHours);

        var chartColors = Master.chartColors;

        var xaxis_labels = [];
        for (var i = 0; i < response.extras.calldetails.length; i++) {
            xaxis_labels.push(response.extras.calldetails[i].Time);
        }

        var handled_calls = [];
        for (var i = 0; i < response.extras.calldetails.length; i++) {
            handled_calls.push(response.extras.calldetails[i].HandledCalls);
        }

        var total_calls = [];
        for (var i = 0; i < response.extras.calldetails.length; i++) {
            total_calls.push(response.extras.calldetails[i].TotCalls);
        }

        var call_volume_data = {

            labels: xaxis_labels,
            datasets: [{
                label: Lang.get('js_msgs.handled_calls'),
                borderColor: chartColors.green,
                backgroundColor: 'rgba(51,160,155,0.6)',
                fill: true,
                data: handled_calls,
                yAxisID: 'y-axis-1'
            }, {
                label: Lang.get('js_msgs.total_calls'),
                borderColor: chartColors.orange,
                backgroundColor: chartColors.orange,
                fill: false,
                data: total_calls,
                yAxisID: 'y-axis-1'
            }]
        };

        var call_volume_options = {
            responsive: true,
            maintainAspectRatio: false,
            hoverMode: 'index',
            stacked: false,
            scales: {
                yAxes: [{
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                }, {
                    type: 'linear',
                    display: false,
                    id: 'y-axis-2',

                    // grid line settings
                    gridLines: {
                        drawOnChartArea: false, // only want the grid lines for one axis to show up
                    },
                }],
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            }
        }

        // call volume inbound line graph
        var ctx = document.getElementById('call_volume').getContext('2d');
        if (window.call_volume_chart != undefined) {
            window.call_volume_chart.destroy();
        }

        window.call_volume_chart = new Chart(ctx, {
            type: 'line',
            data: call_volume_data,
            options: call_volume_options
        });

        /////////////////////////////////////////////////////////
        // agent vs system calls
        ////////////////////////////////////////////////////////

        var chart_colors_array = Master.return_chart_colors(2, chartColors);
        var agent_sys_calls = [];
        agent_sys_calls.push(response.extras.donut.AgentCalls);
        agent_sys_calls.push(response.extras.donut.SystemCalls);

        var agent_system_calls_data = {
            datasets: [{
                data: agent_sys_calls,
                backgroundColor: chart_colors_array,
                label: 'Dataset 1'
            }],
            elements: {
                center: {
                    color: '#203047',
                    fontStyle: 'Segoeui',
                    sidePadding: 15
                }
            },

            labels: [Lang.get('js_msgs.agent_calls'), Lang.get('js_msgs.system_calls')]
        };

        var agent_system_calls_options = {
            responsive: true,
            legend: {
                display: false
            },
            tooltips: {
                enabled: true,
            }, title: {
                fontColor: '#203047',
                fontSize: 16,
                display: true,
                text: Lang.get('js_msgs.agent_system_calls')
            },
        }

        var ctx = document.getElementById('agent_system_calls').getContext('2d');

        if (window.agent_system_calls_chart != undefined) {
            window.agent_system_calls_chart.destroy();
        }

        window.agent_system_calls_chart = new Chart(ctx, {
            type: 'doughnut',
            data: agent_system_calls_data,
            options: agent_system_calls_options
        });

        /////////////////////////////////////////////////////////
        // call status count
        ////////////////////////////////////////////////////////
        var callstatus = [];
        var callstatus_label = [];
        var response_length = response.extras.stats.length
        var chart_colors_array = Master.return_chart_colors(response_length, chartColors);

        for (var i = 0; i < response_length; i++) {
            callstatus.push(response.extras.stats[i].Count);
            callstatus_label.push(response.extras.stats[i].CallStatus);
        }

        var callstatus_data = {
            datasets: [{
                data: callstatus,
                backgroundColor: chart_colors_array,
                label: 'Dataset 1'
            }],
            elements: {
                center: {
                    color: '#203047',
                    fontStyle: 'Segoeui',
                    sidePadding: 15
                }
            },

            labels: callstatus_label
        };

        var callstatus_options = {
            responsive: true,
            legend: {
                display: false
            },
            tooltips: {
                enabled: true,
            }, title: {
                fontColor: '#203047',
                fontSize: 16,
                display: true,
                text: Lang.get('js_msgs.call_status_count')
            },
        }

        var ctx = document.getElementById('callstatus').getContext('2d');

        if (window.callstatus_chart != undefined) {
            window.callstatus_chart.destroy();
        }

        window.callstatus_chart = new Chart(ctx, {
            type: 'doughnut',
            data: callstatus_data,
            options: callstatus_options
        });
    },

    caller_id: function (response) {

        var chartColors = Master.chartColors;

        var caller_id_data = {
            labels: response.extras.callerid,
            datasets: [
                {
                    label: Lang.get('js_msgs.agent_calls'),
                    backgroundColor: chartColors.green,
                    data: response.extras.agent
                },
                {
                    label: Lang.get('js_msgs.system_calls'),
                    backgroundColor: chartColors.orange,
                    fillOpacity: .5,
                    data: response.extras.system
                }
            ]
        };

        var show_decimal = Master.ylabel_format(response.extras.callerid);

        var caller_id_options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            },
            scales: {

                yAxes: [
                    {
                        stacked: true,
                        // type: 'linear',
                        position: 'left',
                        scalePositionLeft: true,
                        scaleLabel: {
                            display: true,
                            labelString: Lang.get('js_msgs.call_count')
                        },
                        ticks: {
                            // display: false
                        }
                    }
                ],
                xAxes: [{ stacked: true }],
            },
            tooltips: {
                enabled: true,
                mode: 'label',
            }
        }

        $('.hidetilloaded').show();

        var ctx = document.getElementById('caller_id_graph').getContext('2d');

        if (window.caller_id_chart != undefined) {
            window.caller_id_chart.destroy();
        }

        window.caller_id_chart = new Chart(ctx, {
            type: 'bar',
            data: caller_id_data,
            options: caller_id_options
        });
    },

    toggle_dotmenu: function () {
        $("#card_dropdown").toggle();

    },

    set_percentages: function () {
        var val, name = $(this).attr('name');
        val = $(this).val();

        if (name === 'Rate_A') {
            $('input[name=Rate_B]').val(100 - val);
        } else {
            $('input[name=Rate_A]').val(100 - val);
        }
    },

    /// keep alive and refresh data
    check_reload: function () {

        if (typeof Dashboard !== 'undefined') {

            $(document.body).on('mousemove keypress', function (e) {
                Dashboard.time = new Date().getTime();
            });
            // reload if idle 60 seconds
            function reload() {
                if (new Date().getTime() - Dashboard.time >= 60000) {
                    Dashboard.refresh(Dashboard.datefilter, Dashboard.campaign);
                    Dashboard.time = new Date().getTime();
                } else {
                    setTimeout(reload, 5000);
                }
            }
            setTimeout(reload, 5000);
        }
    },

    populate_dnc_modal: function () {
        var id = $(this).data('id');
        $('#deleteDNCModal .modal-footer').find('.btn-danger').val('delete:' + id);
    },

    populate_dnc_reversemodal: function () {
        var id = $(this).data('id');
        $('#reverseDNCModal .modal-footer').find('.btn-danger').val('reverse:' + id);
    },

    toggle_instructions: function (e) {

        if (e) {
            e.preventDefault();
        }

        that = $('a.toggle_instruc');
        if (that.hasClass('collapsed')) {
            that.removeClass('collapsed');
            that.empty().append('<i class="fas fa-angle-up"></i>');
        } else {
            that.addClass('collapsed');
            that.empty().append('<i class="fas fa-angle-down"></i>');
        }

        that.parent().find('.instuc_div').slideToggle();
    },

    add_esp: function (e) {
        e.preventDefault();

        var form_data = $(this).serialize();

        $('.alert').empty().hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/add_esp',
            type: 'POST',
            data: form_data,
            success: function (response) {
                location.reload();
            }, error: function (data) {
                if (data.status === 422) {
                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {

                        if ($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $('.add_esp .alert-danger').append('<li>' + value + '</li>');
                            });
                        }

                        $('.add_esp .alert-danger').show();
                    });
                }
            }
        });
    },

    edit_server_modal: function (e) {
        e.preventDefault();

        var id = $(this).data('serverid');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/get_esp',
            type: 'POST',
            data: {
                id: id,
            },
            success: function (response) {

                $('#editESPModal .name').val(response.name);
                $('#editESPModal .provider_type').val(response.provider_type);
                $('#editESPModal .id').val(response.id);
                $('#editESPModal .properties').empty();
                var property_inputs = '';

                const entries = Object.entries(response.properties)
                for (const [key, value] of entries) {
                    var label = key.charAt(0).toUpperCase() + key.slice(1);
                    property_inputs += '<div class="form-group"><label>' + label + '</label><input type="text" class="form-control ' + key + '" name="properties[' + key + ']" value="' + value + '" required></div>';
                }

                $('#editESPModal .properties').append(property_inputs);
            }
        });
    },

    update_esp: function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();

        $('.alert').empty().hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/update_esp',
            type: 'POST',
            data: form_data,
            success: function (response) {
                $(this).find('i').remove();
                location.reload();
            }, error: function (data) {
                $(this).find('i').remove();
                if (data.status === 422) {
                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {

                        if ($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $('.edit_smtp_server .alert-danger').append('<li>' + value + '</li>');
                            });
                        }

                        $('.edit_smtp_server .alert-danger').show();
                    });
                }
            }
        });
    },

    test_connection: function (e) {
        e.preventDefault();

        $('.alert').empty().hide();

        var that = $(this).parent();
        var form_data = $(that).serialize();
        $.ajax({
            url: '/tools/email_drip/test_connection ',
            type: 'POST',
            data: form_data,
            success: function (response) {

                $(that).find('.test_connection').find('i').remove();
                $(that).find('.connection_msg').removeClass('alert-danger alert-success');
                $(that).find('.connection_msg').addClass('alert-success').text(response.message).show();
            }, error: function (data) {
                $('.test_connection').find('i').remove();

                if (data.status === 422) {
                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {

                        if ($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $(that).find('.connection_msg').append('<li>' + value + '</li>');
                                $(that).find('.connection_msg').addClass('alert-danger').show();
                            });
                        }
                    });
                }
            }, statusCode: {
                500: function (response) {
                    $(that).find('.alert-danger').text('Connection Failed').show();
                }
            }
        });
    },

    populate_delete_modal: function (e) {
        e.preventDefault();
        var id = $(this).data('id'),
            name = $(this).data('name'),
            sel = $(this).data('target')
            ;

        $(sel + ' h3').find('span').text(name);
        $(sel + ' #id').val(id);
    },

    delete_esp: function (e) {
        e.preventDefault();
        var id = $('#deleteESPModal').find('#id').val();
        $('#deleteESPModal .alert-danger').hide();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/delete_esp',
            type: 'POST',
            data: {
                id: id,
            },
            success: function (response) {
                location.reload();
            }, error: function (data) {
                $('#deleteESPModal .btn').find('i').remove();
                if (data.status === 422) {
                    $('#deleteESPModal .alert-danger').empty();
                    // $('#deleteESPModal .btn').find('i').remove();
                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {
                        if ($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $('#deleteESPModal .alert-danger').append('<li>' + value + '</li>');
                            });
                        }
                        $('#deleteESPModal .alert-danger').show();
                    });
                }
            }
        });
    },

    create_email_campaign: function (e) {
        e.preventDefault();

        var form_data = $(this).serialize();
        ;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/add_campaign',
            type: 'POST',
            data: form_data,
            success: function (response) {
                $('.create_campaign ').find('i').remove();
                window.location.href = '/tools/email_drip/update_filters/' + response.email_drip_campaign_id;
            }, error: function (data) {
                $('.create_campaign ').find('i').remove();
                if (data.status === 422) {
                    $('.create_campaign_form .alert').empty();
                    $('.create_campaign_form .btn').find('i').remove();
                    var errors = $.parseJSON(data.responseText);
                    $.each(errors, function (key, value) {

                        if ($.isPlainObject(value)) {
                            $.each(value, function (key, value) {
                                $('.create_campaign_form .alert-danger').append('<li>' + value + '</li>');
                            });
                        }

                        $('.create_campaign_form .alert-danger').show();
                    });
                }
            }
        });
    },

    edit_campaign_modal: function (e) {
        e.preventDefault();
        var id = $(this).data('campaignid');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/get_campaign',
            type: 'POST',
            data: {
                id: id,
            },
            success: function (response) {

                $('.edit_campaign_form .id').val(response.id);
                $('.edit_campaign_form .name').val(response.name);
                $('.edit_campaign_form .from').val(response.from);
                $('.edit_campaign_form .subject').val(response.subject);
                $('.edit_campaign_form .description').val(response.description);
                $('.edit_campaign_form .drip_campaigns_campaign_menu').val(response.campaign);
                Master.get_email_drip_subcampaigns(e, response.campaign);
                $('.edit_campaign_form .drip_campaigns_subcampaign').val(response.subcampaign);
                $('.edit_campaign_form .email').val(response.email_field);
                $(".edit_campaign_form .email option[value='" + response.email_field + "']").attr("selected", true);
                $('.edit_campaign_form .template_id ').val(response.template_id);
                $('.edit_campaign_form .email_service_provider_id ').val(response.email_service_provider_id);
                $('.edit_campaign_form .emails_per_lead ').val(response.emails_per_lead);
                $('.edit_campaign_form .days_between_emails ').val(response.days_between_emails);
                return false;
            }
        });
    },

    get_filter_fields: function (id) {

        $('#campaignFilterModal .modal-body').find('.not_validated_filter').remove();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/get_filter_fields',
            type: 'POST',
            data: {
                id: id,
            },
            success: function (response) {
                var filters = '<option value="">Select One</option>';
                const filters_array = Object.keys(response)

                const entries = Object.entries(response)
                for (const [key, value] of entries) {
                    filters += '<option data-type="' + value + '" value="' + key + '">' + key + '</option>';
                }

                $('#campaignFilterModal .modal-body .filter_fields_cnt select.filter_fields').append(filters);
                $('#campaignFilterModal .modal-body').find('.filter_fields_cnt').show();
            }
        });
    },

    get_filters: function (e, that) {
        e.preventDefault();
        var email_drip_campaign_id = $(that).data('id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/tools/email_drip/get_filters',
            type: 'POST',
            data: {
                email_drip_campaign_id: email_drip_campaign_id,
            },
            success: function (response) {

                if (response.length) {
                    $('.filter_fields_cnt').empty().show();
                    var filters = '';
                    Master.get_filter_fields(email_drip_campaign_id)
                    for (var i = 0; i < response.length; i++) {

                        filters += '<div class="row filter_fields_div"><div class="col-sm-4"><label>Field</label><div class="form-group"><select class="form-control filter_fields" name="filter_fields" data-type="field"></select></div></div><div class="col-sm-3 filter_operators_div"><label>Operator</label><div class="form-group"><select class="form-control filter_operators" name="filter_operators" data-type="operator"></select></div></div><div class="col-sm-3 filter_values_div"><label>Value</label><input type="text" class="form-control filter_value" name="filter_value" data-type="value"></div><div class="col-sm-2"><a href="#" class="remove_camp_filter"><i class="fa fa-trash-alt"></i> Remove</a></div></div>';
                    }

                    $('.filter_fields_cnt').append(filters);
                    for (var i = 0; i < response.length; i++) {
                        $('.filter_fields_div:eq(' + i + ')').find('.filter_fields').css({ 'border': '1px solid red' });
                        $(".filter_fields_div:eq(" + i + ") .filter_fields").find("option[value='" + response[i]['field'] + "']").attr('selected', 'selected');
                    }
                }
            }
        });
    },

    reset_modal_form: function (modal) {
        $(modal).find('form.form').trigger("reset");
        $(modal).find('.alert').empty().hide();
    },

    pass_id_to_modal: function (that, id) {
        var modal = $(that).data('target');
        $(modal).find('.id').val(id);

        if ($(that).data('name')) {
            $(modal).find('h3 span').html($(that).data('name'));
        }
    },

    cancel_modal_form: function (e) {
        e.preventDefault();
        $(this).parent().parent().find('.form')[0].reset()
    },

    set_group: function () {
        var group_id = $(this).val();
        var report = $('#report').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/sso/set_group',
            type: 'POST',
            data: {
                group_id: group_id,
                report: report
            },
            success: function (response) {
                window.location.reload();
            }
        });
    },

    set_timezone: function () {
        var tz = $(this).val();
        var report = $('#report').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/sso/set_timezone',
            type: 'POST',
            data: {
                tz: tz,
                report: report
            },
            success: function (response) {
                window.location.reload();
            }
        });
    },

    toggle_active_reps: function () {
        var checked = 0;
        if ($(this).is(':checked')) {
            checked = 1;
        } else {
            checked = 0;
        }

        var active_cnt = 0;
        $(this).parent().prev().find('.dropdown-menu').find('li').each(function (index) {
            if (index > 1) {
                if (checked) {
                    if (!$(this).hasClass('active_rep')) {
                        $(this).hide();
                        active_cnt = $(this).parent().parent().find('.multiselect-container.dropdown-menu li.active').length - 2;
                    }
                } else {
                    active_cnt = $(this).parent().parent().find('.multiselect-container.dropdown-menu li.active.active_rep').length - 2;
                    $(this).show();
                }
            }
        });

    },

    toggle_active_client: function () {
        var checked;
        var id = $(this).parent().parent().parent().data('id');

        if ($(this).is(':checked')) {
            $(this).attr('Checked', 'Checked');
            checked = 1;
        } else {
            $(this).removeAttr('Checked');
            checked = 0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/admin/toggle_user',
            type: 'POST',
            data: {
                checked: checked,
                id: id,
            },
            success: function (response) {

            }
        });
    },

    /////////////////////////////////////////////////////////
    // PINNED TABLE - dbl click to freeze
    /////////////////////////////////////////////////////////
    pin_table_column: function (e) {

        var index = $(this).index();
        if ($(this).hasClass('sticky-col')) {
            $('tbody tr').each(function () {
                $(this).find('td:eq(' + index + ')').removeClass('sticky-col');
            });
            $(this).removeClass('sticky-col');
        } else {
            $(this).addClass('sticky-col');
            $('tbody tr').each(function () {
                $(this).find('td:eq(' + index + ')').addClass('sticky-col');
                $(this).find('td:eq(' + index + ')').css({ 'left': +index + '00px' });
                $('thead').find('th:eq(' + index + ')').css({ 'left': +index + '00px' });
            });
        }
    },

    // set direction of table to freeze
    set_pinned_direction: function () {
        var dir = $(this).val();
        var cols = $('#numb_pinned_cols').val();
        Master.draw_pinned_datatable(cols, dir);
    },

    // change # of columns to freeze from select menu
    select_pinned_columns: function (e) {
        e.preventDefault();
        Master.pinned_columns = $(this).val();
        Master.draw_pinned_datatable(Master.pinned_columns, $(".pin_direction[name='pin_direction']:checked").val());
    },

    draw_pinned_datatable: function (cols, dir = 'left') {
        ///////////////////////////////////////////////////////////
        Master.report_pinned_datatable.destroy();

        if (dir == 'left') {
            Master.left_cols = cols;
            Master.right_cols = 0;
        } else {
            Master.right_cols = cols;
            Master.left_cols = 0;
        }

        Master.report_pinned_datatable = $('.report_pinned_datatable').DataTable({
            destroy: true,
            scrollY: 500,
            scrollX: true,
            scrollCollapse: true,
            paging: true,
            responsive: true,
            fixedColumns: {
                leftColumns: Master.left_cols,
                rightColumns: Master.right_cols
            }
        })
    },

    // toggle instructions on tools/dnc_importer & admin/spam_check
    toggle_instructions: function (e) {

        if (e) {
            e.preventDefault();
        }

        that = $('a.toggle_instruc');
        if (that.hasClass('collapsed')) {
            that.removeClass('collapsed');
            that.empty().append('<i class="fas fa-angle-up"></i>');
        } else {
            that.addClass('collapsed');
            that.empty().append('<i class="fas fa-angle-down"></i>');
        }

        that.parent().find('.instuc_div').slideToggle();
    }
}

$(document).ready(function () {

    Master.init();

    $('#addServerModal, #editESPModal, #deleteESPModal').on('hidden.bs.modal', function () {
        $(this).find('.alert').hide();
    });

    $('.stop-propagation').on('click', function (e) {
        e.stopPropagation();
    });

    $('.filter_campaign').on('click', '.stop-propagation', function (e) {
        e.stopPropagation();
    });

    // Close the dropdown if the user clicks outside of it
    window.onclick = function (event) {
        if (!event.target.matches('.card_dropbtn')) {
            $('.card_dropdown-content').hide();
        }
    }

    var hash = window.location.hash;
    hash && $('ul.nav-tabs.tabs a[href="' + hash + '"]').tab('show');

    $('.nav-tabs.tabs a').click(function (e) {
        $(this).tab('show');
        console.log('clicked');
        window.location.hash = this.hash;
        $('html,body').scrollTop($('body').scrollTop());
    });

    $('[data-toggle="tooltip"]').tooltip({ trigger: "click" });

    $('.notification_msg').find('img').each(function () {
        $(this).addClass('img-responsive');
    });

    if ($('.sso #group_id').val() == '-1') {
        $('.sso #group_id').parent().addClass('has-error');
    }

    if ($('.spam_check_table tbody tr, .dnc_table tbody tr').length) {
        Master.toggle_instructions();
    }

});

