var KPI = {

    org_kpis : [],

    init:function(){
        $('.opt a.kpi_trigger').on('click', this.toggle_kpi_info);
        $('.switch input').on('click', this.toggle_kpi);
        $('.expanded_emails').on('click', 'a.remove_recip_glyph', this.pass_user_removemodal);
        $('#deleteRecipModal .remove_recip').on('click', this.remove_recipient);
        $('.adjust_interval').on('submit', this.adjust_interval);
        $('.run_kpi').on('click', this.fire_kpi);
        $('.expanded_emails').on('click', '.edit_recip_glyph', this.open_edit_recipient_modal);
        $('.user_email_form').on('click', '.search_result_item', this.open_edit_recipient_modal);
        $('.update_recip').on('submit', this.update_recipient);
        $('#editRecipModal').on('click', '#select_all', this.toggle_all_kpis);
        $('.kpi_list').on('click', '.undoselection_btn', this.undo_kpi_selection);
        $('.user_email_form #name').on('keyup', this.searchRecips);
    },

    searchRecips(){
        var el = $(this);
        var value = $(this).val();

        $(el).next('.search_results').css({'display' : 'block'});
        $(el).next('.search_results').empty();

        if(value.length > 1){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                'async': false,
                url: '/kpi/ajax_search',
                type: 'POST',
                dataType: 'json',
                data:{
                    query:value
                },
                success:function(response){

                    if(response.search_recip.length){
                        $(el).next('.search_results').css({'display' : 'block'});
                    }else{
                        $(el).next('.search_results').css({'display' : 'none'});
                    }

                    for(var i=0; i< response.search_recip.length;i++){
                        $(el).next('.search_results').append('<h5 class="search_result_item"  data-toggle="modal" data-target="#editRecipModal" data-recip="'+response.search_recip[i]['id']+'" data-name="'+response.search_recip[i]['name']+'" data-phone="'+response.search_recip[i].phone+'" data-email="'+response.search_recip[i].email+'">'+response.search_recip[i]['name']+'</h5>');
                    }
                }
            });
        }else{
            $(el).next('.search_results').css({'display' : 'none'});
        }
    },

    fire_kpi:function(e){

        e.preventDefault();

        var $t = $(this).find('span');
        $(this).find('span').addClass('glyphicon glyphicon-refresh');
        $(this).find('span').removeClass('glyphicon-flash');

        var kpi_id = $(this).parent().parent().data('kpi');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/kpi/run_kpi',
            type: 'POST',
            dataType: 'json',
            data: {
                kpi_id: kpi_id
            },
            success:function(response){
                $t.removeClass('glyphicon-refresh');
                $t.addClass('glyphicon-flash');
            }
        });
    },

    adjust_interval:function(e){
        e.preventDefault();
        var kpi_id = $(this).data('kpi'),
            interval = $(this).find('.interval').val()
        ;

        $('.alert').remove();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/kpi/adjust_interval',
            type: 'POST',
            dataType: 'json',
            data: {
                kpi_id: kpi_id,
                interval:interval
            },
            success:function(response){

                if(response.adjust_interval == true){
                    $('<div class="mt12 mt20 alert alert-success">'+Lang.get('js_msgs.interval_updated')+'</div>').insertAfter('form.adjust_interval .btn');
                    setTimeout(function(){
                        $('.alert').remove();
                    }, 4500);
                }else{
                    $('<div class="mt12 mt20 alert-danger">'+Lang.get('js_msgs.reload_error_msg')+'</div>').insertAfter('form.adjust_interval .btn');
                    setTimeout(function(){
                        $('.alert').remove();
                    }, 4500);
                }
            }
        });
    },

    toggle_kpi:function(){

        var checked;
        var group_id = $('#group_id').val();
        var kpi = $(this).parent().parent().parent().data('kpi');

        
        if($(this).is(':checked')){
            $(this).attr('Checked','Checked');
            checked=1;
        }else{
            $(this).removeAttr('Checked');
            checked=0;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/kpi/toggle_kpi',
            type:'POST',
            data:{
                checked:checked,
                kpi:kpi,
                group_id:group_id

            },
            success:function(response){
                $("div[data-kpi='" + response.kpi_group.kpi_id +"']").find('.kpi .interval').val(response.kpi_group.interval);
            }
        });
    },

    toggle_kpi_info:function(e){
        e.preventDefault();
        $('.search_results').empty();
        $('.search_results').hide();
        if($(this).hasClass('active_kpi')){
            $(this).removeClass('active_kpi');
            $(this).parent().find('.kpi').hide();
        }else{
            $('.opt a').removeClass('active_kpi');
            $('.kpi').hide();
            $(this).parent().find('.kpi').show();
            $(this).addClass('active_kpi');
        }
    },

    toggle_email_opts:function(e){
        e.preventDefault();
        $(this).next().toggle();
    },

    pass_user_removemodal:function(){

        var id = $(this).data('recip');
        var name = $(this).data('username');

        $('#deleteRecipModal .user_id').val(id);
        $('#deleteRecipModal .name').val(name);
        $('#deleteRecipModal .username').html(name);
    },

    open_edit_recipient_modal:function(e){

        $('.search_results').empty().hide();
        e.preventDefault();
        var id=$(this).data('recip');
        KPI.edit_recipient(id);
    },

    edit_recipient:function(id){

        $('#editRecipModal .alert').hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url:'/kpi/get_recipient',
            type:'POST',
            dataType:'json',
            data:{
                id:id
            },
            success:function(response){

                KPI.org_kpis=[];

                $('#editRecipModal').find('.kpi_recip_info').remove();
                $('#editRecipModal .modal-body form .kpi_list').empty();
                $('#editRecipModal .modal-body form .user_id').val(id);

                var kpi_list='<div class="checkbox mb20 select_all fltlft"><label><input id="select_all" name="select_all" type="checkbox"> <b>'+Lang.get('js_msgs.select_all')+'</b></label></div><a href="#" class=" undoselection_btn"> '+Lang.get('js_msgs.undo_selection')+'</a>';
                var selected;

                for(var i=0; i<response.kpi_list.length;i++){
                    selected =  response.kpi_list[i].selected ? 'checked' : '';
                    kpi_list+='<div class="checkbox mb20"><label><input name="kpi_list[]" '+selected+' type="checkbox" value="'+response.kpi_list[i].id+'"><b>'+response.kpi_list[i].name+'</b> - '+response.kpi_list[i].description+'</label></div>';
                }

                $('#editRecipModal .modal-body form .name.form-control').val(response.recipient.name);
                $('#editRecipModal .modal-body form .email.form-control').val(response.recipient.email);
                $('#editRecipModal .modal-body form .phone.form-control').val(response.recipient.phone);

                $('#editRecipModal .modal-body form .recipient_id').val(response.recipient.id);
                $('#editRecipModal .modal-body form .kpi_list').append(kpi_list);

                // build array of originally selected kpis
                $(".kpi_list div label input").each(function(i) {
                    if (this.checked) {
                        KPI.org_kpis.push(i);
                    }
                });
            }
        });
    },

    update_recipient:function(e){
        e.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        var name = $('.update_recip .name').val(),
            email = $('.update_recip .email').val(),
            phone = $('.update_recip .phone').val(),
            recipient_id = $('.recipient_id').val(),
            from_page = $('.from_page').val(),
            kpi_id = $('.active_kpi').parent().data('kpi'),
            kpi_list = [];
        ;

        $('input[type="checkbox"]:checked').each(function () {
            kpi_list.push($(this).val());
        });

        $('#editRecipModal form .alert').empty();

        $.ajax({
            url:'/kpi/update_recipient',
            type:'POST',
            dataType:'json',
            data:{
                recipient_id:recipient_id,
                name:name,
                email:email,
                phone:phone,
                kpi_id:kpi_id,
                kpi_list:kpi_list
            },
            success:function(response){
                window.location.href = from_page;
            },
            error :function( data ) {
                var errors = $.parseJSON(data.responseText);
                $.each(errors, function (key, value) {

                    if($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                        $('#editRecipModal form .alert').show().append(value+"<br/>");

                        });
                    }else{
                    $('#editRecipModal form .alert').show().append(value+"<br/>");
                    }
                });
            }
        });
    },

    /// put kpi selection back to saved list
    undo_kpi_selection:function(e){
        e.preventDefault();
        $(".kpi_list div label input").prop('checked', false);
        $(".kpi_list div label input").each(function(i) {
            for(var j=0;j<KPI.org_kpis.length;j++){
                if(KPI.org_kpis[j]==i){
                    $(this).prop( "checked", true );
                }
            }
        });
        $(".kpi_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.select_all'));
    },

    toggle_all_kpis:function(){
        if($(this).prop("checked")){
            $(".kpi_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.unselect_all'));
            $(this).parent().parent().siblings().find('label input').prop( "checked", true );
        }else{
            $(".kpi_list").find('div.checkbox.select_all b').text(Lang.get('js_msgs.select_all'));
            $(this).parent().parent().siblings().find('label input').prop( "checked", false );
        }
    },

    remove_recipient:function(e){

        e.preventDefault();
        var id = $('.user_id').val();
        var fromall = parseInt($('.fromall').val());

        if(fromall){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax({
                url:'/kpi/remove_recipient_from_all',
                type:'POST',
                dataType:'json',
                data:{
                    id:id
                },
                success:function(response){
                    $('div#'+id).remove();
                    $('#deleteRecipModal').modal('toggle');
                }
            });
        }else{
            var kpi_id = $('.kpi_id').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url:'/kpi/remove_recipient_from_kpi',
                type:'POST',
                dataType:'json',
                data:{
                    id:id,
                    kpi_id:kpi_id
                },
                success:function(response){
                    $('div#'+id).remove();
                    $('#deleteRecipModal').modal('toggle');
                }
            });
        }
    },
}

$(document).ready(function(){
    KPI.init();

    var kpi_id = $('.open_kpi_id').val();
    if(kpi_id){
        $('.opt[data-kpi="'+kpi_id+'"]').find('.kpi_trigger').addClass('active_kpi');
        $('.opt[data-kpi="'+kpi_id+'"]').find('.kpi').css({'display':'block'});
    }
});
