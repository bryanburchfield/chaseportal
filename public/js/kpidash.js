var KPI = {

    init:function(){
        $('.opt a.kpi_trigger').on('click', this.toggle_kpi_info);
        $('.expand_dets,.add_email').on('click', this.toggle_email_opts);
        $('.switch input').on('click', this.toggle_kpi);
        $('.expanded_emails').on('click', 'a.remove_recip_glyph', this.pass_user_removemodal);
        $('#deleteRecipModal .remove_recip').on('click', this.remove_recipient);
        $('.add_recipient').on('submit', this.add_recipient);
        $('.adjust_interval').on('submit', this.adjust_interval);
        $('.run_kpi').on('click', this.fire_kpi);
        $('.search_results').on('click', 'h5', this.populate_recipient);
    },

    populate_recipient:function(){

        $('.search_results').hide();

        var form = $(this).parent().parent().parent().attr('id');       
        var kpi_id = $(this).data('kpiid');
        var name = $(this).data('name');
        var phone = $(this).data('phone');
        var email = $(this).data('email');

        $('#'+form).find('.name').val(name);
        $('#'+form).find('.email').val(email);
        $('#'+form).find('.phone').val(phone);      
    },

    fire_kpi:function(e){

        e.preventDefault();

        if(!$(this).parent().find('.switch input').is(':checked')){
            alert('KPI must be turned on to run');
            return false;
        }else{
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
        }
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
                    $('<div class="mt12 alert alert-success">Interval successfully updated.</div>').insertAfter('form.adjust_interval .btn');
                    setTimeout(function(){ 
                        $('.alert').remove();
                    }, 4500);
                }else{
                    $('<div class="mt12  alert-danger">Something went wrong. Please try again later.</div>').insertAfter('form.adjust_interval .btn');
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

    add_recipient:function(kpi_id=0, name='', phone=0, email=''){
        event.preventDefault();

        var name = $(this).find('.name').val(),
            email = $(this).find('.email').val(),
            phone = $(this).find('.phone').val(),
            kpi_id = $(this).data('kpi'),
            addtoall = $(this).find('.addtoall').is(':checked'),
            redirect_url = $(this).find('.redirect_url').val()
        ;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            url: '/kpi/add_recipient',
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
                email:email,
                phone:phone,
                kpi_id:kpi_id,
                addtoall:addtoall,
                redirect_url:redirect_url
            },

            success:function(response){
               
                var from_form,
                    append_user
                ;
                if(redirect_url == '/dashboards/'){
                    from_form=$('form#form'+kpi_id);
                    append_user=$('form#form'+kpi_id).parent('.expanded_emails');
                }else{
                    from_form=$('form.user_email_form');
                    append_user=$('.expanded_emails');                  
                }

                console.log($(from_form));

                $(from_form).append('<div class="mt20 alert alert-success">User successfully added.</div>');
                $(from_form).find('input.form-control').val("");
                $(append_user).append('<div class="user clear" id="'+response.add_recipient[3]+'"><p class="name">'+response.add_recipient[0]+'</p><p class="email">'+response.add_recipient[1]+'</p><p class="phone">'+response.add_recipient[2]+'</p> <a data-toggle="modal" data-target="#deleteRecipModal" class="remove_recip_glyph" href="#" data-recip="' +response.add_recipient[3] +'"><i class="glyphicon glyphicon-remove-sign"></i></a></div>');

                setTimeout(function(){ 
                    $('.alert').remove();
                }, 4500);
            }
        }); 
    },

    pass_user_removemodal:function(){

        var id = $(this).data('recip');
        var name = $(this).parent().find('p.name').text();
        console.log(id +' '+ name);
        $('#deleteRecipModal .user_id').val(id);
        $('#deleteRecipModal .name').val(name);
        $('#deleteRecipModal .username').html(name);
    },

    remove_recipient:function(e){

        e.preventDefault();
        var id = $('.user_id').val();
        var fromall = $('.fromall').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });


        //// if removing from recips page to remove from all kpis
        if(fromall == 1){
            $.ajax({
                url:'/kpi/remove_recipient',
                type:'POST',
                dataType:'json',
                data:{
                    id:id,
                    fromall:fromall
                },
                success:function(response){
                    console.log(response);
                    $('div#'+id).remove();
                    $('#deleteRecipModal').modal('toggle');
                }
            });
        }else{  ///// if removing from an kpi to remove from only that kpi
            $.ajax({
                url:'/kpi/remove_recipient_from_kpi',
                type:'POST',
                dataType:'json',
                data:{
                    id:id,
                    fromall:fromall
                },
                success:function(response){
                    console.log(response);
                    $('div#'+id).remove();
                    $('#deleteRecipModal').modal('toggle');
                }
            });
        }
    },
}

$(document).ready(function(){
    KPI.init();
});

function searchRecips(el, value, kpi_id){

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
                query:value,
                kpi_id:kpi_id
            },
            success:function(response){
                console.log(response);
                if(response.search_recip.length){
                    $(el).next('.search_results').css({'display' : 'block'});
                }else{
                    $(el).next('.search_results').css({'display' : 'none'});
                }

                for(var i=0; i< response.search_recip.length;i++){
                    
                    $(el).next('.search_results').append('<h5 class="search_result_item" data-kpiid="'+kpi_id+'" data-name="'+response.search_recip[i]['name']+'" data-phone="'+response.search_recip[i].phone+'" data-email="'+response.search_recip[i].email+'">'+response.search_recip[i]['name']+'</h5>');
                }
            }
        });
    }else{
        $(el).next('.search_results').css({'display' : 'none'});
    }
    
}