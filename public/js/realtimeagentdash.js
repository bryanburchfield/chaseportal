var RealTime = {
	init:function(result){
        console.log(result);

        var first_result_map = new Map();
        var first_result=[];

        for (var i=0; i < result.length; i++) {
            var column = result[i][0];
            var agents='';
            for(j=0;j<result[i][1].length;j++){
                if(!ran){
                	first_result_map.set(result[i][1][j]['Login'],  result[i][1][j]['checksum']);
                	first_result.push({[result[i][1][j]['Login']] : result[i][1][j]['checksum']});
                }else{
                	console.log('VALUE: '+first_result[i]);
                	if(result[i][1][j]['checksum'] == first_result[i]){

                	}
                }
                agents += '<p class="rep_name mb0">'+result[i][1][j]['Login']+'</p><p class="campaign">'+result[i][1][j]['Campaign']+'</p>';
            }

            $('.rep_status.'+column).append(agents);
        }

        ran=true;
        console.log(first_result_map);
        console.log(first_result);
	}
}
