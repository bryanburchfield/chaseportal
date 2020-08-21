<script src="{{asset('js/app.js')}}"></script>
<script>

    $('.preloader').hide();

    var ran=false,
        trs,
        timers=[],
        time = '12:05:14'
    ;
    
    Echo.channel('{{ $data['channel'] }}')
        .listen('NewMessage', (e) => {
            // var result = Object.entries(e.message);
            RealTime.init(Object.entries(e.message));
        });
</script>