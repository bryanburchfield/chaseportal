<script src="{{asset('js/app.js')}}"></script>
<script>
    var ran=false,
        trs,
        timers=[],
        time = '12:05:14'
    ;

    Echo.channel('{{ $data['channel'] }}')
        .listen('NewMessage', (e) => {
            console.log(typeof(e.message));
            console.log(e.message);
            Object.size = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            };

            var obj_length = Object.size(e.message);

            // const keys = Object.keys(e.message)
            // for (const key of keys) {
            //     console.log(key)
            // }

            var result_obj = e.message;
            var result_obj_length = Object.keys(result_obj).length;
            const result_obj_keys = Object.getOwnPropertyNames(result_obj);
            let test = [];
            test.push(Object.values(result_obj));
            console.log(result_obj_keys);
            for (var i = 0; i < test[0].length; i++) {
                console.log(test[0][i]);
            }


            // $('.realtime_table tbody').empty();
            // var start_time=0;
            // console.log(e.message); // 506

            // const status_types = Object.keys(e.message);
            // console.log(status_types);

            // function start_timer(start_time, row){
            //     const zeroPad = (num, places) => String(num).padStart(places, '0');
            //     start_time = parseInt(start_time);

            //     var x = setInterval(function () {

            //         start_time = start_time + 1;
            //         var hours = Math.floor(start_time / 3600);
            //         var minutes = Math.floor((start_time / 60) % 60);
            //         var seconds = start_time % 60;

            //         $('.realtime_table tbody tr:eq('+row+')').find('td.instatus_timer').empty();
            //         $('.realtime_table tbody tr:eq('+row+')').find('td.instatus_timer').text(zeroPad(hours, 2) + ":" + zeroPad(minutes, 2) + ":" + zeroPad(seconds, 2));
            //     }, 1000);
            // }

            // trs='';

            // for(var i=0;i<e.message.results.length;i++){

            //     if(!ran){
            //         start_timer(e.message.results[i].SecondsInStatus, i);
            //     }

            //     trs+='<tr><td>'+e.message.results[i].Login+'</td><td>'+e.message.results[i].Campaign+'</td><td>'+e.message.results[i].Subcampaign+'</td><td>'+e.message.results[i].Skill+'</td><td class="instatus_timer">'+e.message.results[i].TimeInStatus+'</td><td>'+e.message.results[i].BreakCode+'</td><td>'+e.message.results[i].State+'</td><td>'+e.message.results[i].Status+'</td></tr>';
            // }

            // ran = true;

            // $('.realtime_table tbody').append(trs);
        });


        // "talking" => array:1 [▶]
        // "wrapping" => []
        // "waiting" => []
        // "manual" => []
        // "paused"
</script>