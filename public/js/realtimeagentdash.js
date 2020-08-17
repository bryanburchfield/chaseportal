var RealTime = {
	init:function(result){
        console.log(result);

        // var first_result_map = new Map();
        var first_result=[];
        let inmem_array = new Array();

        // if first iteration
        if(!ran){
        	/// delete all li from on screen
        	this.delete_agents();
        	///////////////////////////////

        	/// loop through returned array
        	console.log('Result Length' + result.length);
        	for (var i=0; i < result.length; i++) {
        	    var column = result[i][0];

        	    for(j=0;j<result[i][1].length;j++){
        	    	console.log(result[i][0] + result[i][1].length);
        	    	var agent='';
        	    	inmem_array[result[i][0]] = new Array();
        	        inmem_array[result[i][0]].push({[result[i][1][j]['Login']] : result[i][1][j]['checksum']});
        	        agent+='<li class="list-group-item"><p data-checksum="'+result[i][1][j]['checksum']+'" class="rep_name mb0">'+result[i][1][j]['Login']+'</p><p class="campaign">'+result[i][1][j]['Campaign']+'</p></li>';

        	        console.log(inmem_array);
        	        $('.rep_status.'+column).append(agent);
        	    }
        	}
        }else{

        	this.delete_agents();
        	// for (var i=0; i < result.length; i++) {
        	// 	if(!result[i][1].length){
        	// 		if(inmem_array[0][1]){

        	// 		}
        	// 		// alert(result[i][0]+ ' is empty');
        	// 	}
        	// }
        }


        //// if not ran yet
        // else{
        // 	// console.log('VALUE: '+first_result[i]);
        // 	if(result[i][1][j]['checksum'] == first_result[i]){

        // 	}
        // }

        ran=true;
        // console.log(first_result_map);
	},

	delete_agents(){
		$('.rep_status').each(function(){
			$(this).find('ul.list-group').empty();
		});
	}
}
