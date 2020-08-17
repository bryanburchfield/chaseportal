var RealTime = {

	init:function(result){
        console.log(result);

        var first_result=[];
        let inmem_array = new Array();

        // if first iteration
        if(!ran){
        	/// delete all li from on screen
        	this.delete_agents();

        	/// loop through returned array
        	for (var i=0; i < result.length; i++) {
        	    var column = result[i][0];
        	    inmem_array[result[i][0]] = new Array();

        	    for(j=0;j<result[i][1].length;j++){
        	    	var agent='';
        	    	// insert into in-mem arrays
        	        inmem_array[result[i][0]].push({[result[i][1][j]['Login']] : result[i][1][j]['checksum']});
        	        // append li to status column
        	        agent+='<li class="list-group-item"><p data-checksum="'+result[i][1][j]['checksum']+'" class="rep_name mb0">'+result[i][1][j]['Login']+'</p><p class="campaign">'+result[i][1][j]['Campaign']+'</p></li>';

        	        $('.rep_status.'+column).append(agent);
        	    }
        	}
        }else{
        	for (var i=0; i < result.length; i++) {
        		if(!result[i][1].length){
        			/// if in-mem array is not empty
        			if(inmem_array[0][1].length){
        				this.delete_agents();
        				inmem_array[0][1]=[];
        			}
        		}else{
        			// search in memory array for that login
        			for(var i=0;i<inmem_array.length;i++){
        				//seach returned array for that login
        			}
        		}
        	}
        }

        console.log(inmem_array);

        //// if not ran yet
        // else{
        // 	// console.log('VALUE: '+first_result[i]);
        // 	if(result[i][1][j]['checksum'] == first_result[i]){

        // 	}
        // }

        ran=true;
	},

	delete_agents(){
		$('.rep_status').each(function(){
			$(this).find('ul.list-group').empty();
		});
	}
}
