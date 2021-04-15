
var PORTAL_FORM_BUILDER = {

	active_tab:'#inputs',

	init:function(){
		$('#target').on('click', '.form-group.component, #legend', this.open_props_form);
		$('form').on('mousedown', '.component', this.drag_element);
		$('#target').on('click', '.component', this.add_vals);
		$("#navtab").on("click", '#sourcetab', this.generateHTML);
	},

	// open properties form in tab panel
	open_props_form(){
		$('.nav-tabs a[href="#properties"]').tab('show')
		var form = $(this).data('content');
		$('#properties').empty();
		$('#properties').append(form);

	},

	drag_element(md){

	    md.preventDefault();
	    var tops = [];
	    var mouseX = md.pageX;
	    var mouseY = md.pageY;
	    var $temp;
	    var timeout;
	    var $this = $(this);
	    var delays = {
	    	main: 0,
	    	form: 120
	    }
	    
	    var type;

	    if($this.parent().parent().parent().parent().attr("id") === "components"){
	    	type = "main";
	    }else {
	    	type = "form";
	    }

	    var delayed = setTimeout(function(){
	    	if(type === "main"){
	        	$temp = $("<form class='form-horizontal col-md-12' id='temp'></form>").append($this.clone());
	    	}else {
		        if($this.attr("id") !== "legend"){
		        	$temp = $("<form class='form-horizontal col-md-12' id='temp'></form>").append($this);
		        }
		    }

		    $("body").append($temp);

		    $temp.css(
		    	{
			    	"position" : "absolute",
			        "top"      : mouseY - ($temp.height()/2) + "px",
			    	"left"     : mouseX - ($temp.width()/2) + "px",
			        "opacity"  : "0.9"
			    }
			).show()

	    	var half_box_height = ($temp.height()/2);
	    	var half_box_width = ($temp.width()/2);
	    	var $target = $("#target");
	    	var tar_pos = $target.position();
	    	var $target_component = $("#target .component");

		    $(document).delegate("body", "mousemove", function(mm){
		    	var mm_mouseX = mm.pageX;
		        var mm_mouseY = mm.pageY;

	        	$temp.css({"top"      : mm_mouseY - half_box_height + "px",
	        		"left"      : mm_mouseX - half_box_width  + "px"});

	        	if ( mm_mouseX > tar_pos.left &&
	        		mm_mouseX < tar_pos.left + $target.width() + $temp.width() &&
	        		mm_mouseY > tar_pos.top &&
	        		mm_mouseY < tar_pos.top + $target.height() + $temp.height()
	        	){
	            	$("#target").css("background-color", "#fff");
	            	$target_component.css({"border-top" : "1px solid white", "border-bottom" : "none"});
	            	tops = $.grep($target_component, function(e){
	            		return ($(e).position().top -  mm_mouseY + half_box_height > 0 && $(e).attr("id") !== "legend");
	            	});
	            if (tops.length > 0){
	            	$(tops[0]).css("border-top", "1px solid #22aaff");
	            }else{
	            	if($target_component.length > 0){
	                	$($target_component[$target_component.length - 1]).css("border-bottom", "1px solid 22aaff");
	            	}
	            }
	        	}else{
	           		$("#target").css("background-color", "#fff");
	            	$target_component.css({"border-top" : "1px solid white", "border-bottom" : "none"});
	            	$target.css("background-color", "#fff");
	        	}
	    	});

	    	$("body").delegate("#temp", "mouseup", function(mu){
	        	mu.preventDefault();
	        	var mu_mouseX = mu.pageX;
	        	var mu_mouseY = mu.pageY;
	        	var tar_pos = $target.position();

	        	$("#target .component").css({"border-top" : "1px solid white", "border-bottom" : "none"});

	        	// acting only if mouse is in right place
	       		if (mu_mouseX + half_box_width > tar_pos.left &&
	        		mu_mouseX - half_box_width < tar_pos.left + $target.width() &&
	        		mu_mouseY + half_box_height > tar_pos.top &&
	        	mu_mouseY - half_box_height < tar_pos.top + $target.height()
	        	){
	            	$temp.attr("style", null);
	            	// where to add
	            	if(tops.length > 0){
	             		$($temp.html()).insertBefore(tops[0]);
	            	}else {
	            		// append in form builder dropzone
	             	$("#target fieldset").append($temp.append("\n\n\ \ \ \ ").html());
	            	}
	         	}else{
	            	// no add
	            	$("#target .component").css({"border-top" : "1px solid white", "border-bottom" : "none"});
	            	tops = [];
	        	}

		        $target.css("background-color", "#fff");
		        $(document).undelegate("body", "mousemove");
		        $("body").undelegate("#temp","mouseup");
		        $temp.remove();
		        PORTAL_FORM_BUILDER.generateHTML();
	    	});
	    }, delays[type]);

	    $(document).mouseup(function () {
	    	clearInterval(delayed);
	     	return false;
	    });

	    $(this).mouseout(function () {
	    	clearInterval(delayed);
	    	return false;
	    });

	    if($('#target fieldset .component').length){
	    	$(' #target fieldset').find('p').remove();
	    }
	},

	add_vals(e){
    	e.preventDefault();
    	var $active_component = $(this);
    	var valtypes = $active_component.find(".valtype");
    	$.each(valtypes, function(i,e){
    		var valID ="#" + $(e).attr("data-valtype");
    		var val;
    		if(valID ==="#placeholder"){
        		val = $(e).attr("placeholder");
        		$(".elements #properties form " + valID).val(val);
    		}else if(valID ==="#href"){
        		val = $(e).attr("href");
        		$(".elements #properties form " + valID).val(val);
    		}else if(valID ==="#src"){
        		val = $(e).attr("src");
        		$(".elements #properties form " + valID).val(val);
    		}else if(valID==="#checkbox"){
        		val = $(e).attr("checked");
        		$(".elements #properties form " + valID).attr("checked",val);
    		}else if(valID==="#option"){
        		val = $.map($(e).find("option"), function(e,i){return $(e).text()});
        		val = val.join("\n")
    			$(".elements #properties form "+valID).text(val);
    		}else if(valID==="#checkboxes"){
        		val = $.map($(e).find("label"), function(e,i){return $(e).text().trim()});
        		val = val.join("\n")
    			$(".elements #properties form "+valID).text(val);
    		}else if(valID==="#radios"){
        		val = $.map($(e).find("label"), function(e,i){return $(e).text().trim()});
        		val = val.join("\n");
        		$(".elements #properties form "+valID).text(val);
        		$(".elements #properties form #name").val($(e).find("input").attr("name"));
    		}else if(valID==="#inline-checkboxes"){
        		val = $.map($(e).find("label"), function(e,i){return $(e).text().trim()});
        		val = val.join("\n")
        		$(".elements #properties form "+valID).text(val);
    		}else if(valID==="#inline-radios"){
        		val = $.map($(e).find("label"), function(e,i){return $(e).text().trim()});
        		val = val.join("\n")
        		$(".elements #properties form "+valID).text(val);
        		$(".elements #properties form #name").val($(e).find("input").attr("name"));
    		}else if(valID==="#button") {
        		val = $(e).text();
        		var type = $(e).find("button").attr("class").split(" ").filter(function(e){return e.match(/btn-.*/)});
        		$(".elements #properties form #color option").attr("selected", null);

	        	if(type.length === 0){
	        		$(".elements #properties form #color #default").attr("selected", "selected");
	        	}else {
	        		$(".elements #properties form #color #"+type[0]).attr("selected", "selected");
	        	}

	        	val = $(e).find(".btn").text();
        		$(".elements #properties form  #button").val(val);
    		}else {
        		val = $(e).text();
        		$(".elements #properties form  " + valID).val(val);
      		}
    	});

    	/// CANCEL EDIT
		$('.elements #properties form').on('click', '.btn.cancel_edit', function(e){
			e.preventDefault();
			$('.nav-tabs a[href="#elements"]').tab('show')
			// PORTAL_FORM_BUILDER.add_vals()
		});

		/// SAVE EDIT
		$('.elements #properties form').on('click', '.btn.save_edit', function(e){
			e.preventDefault();
			var inputs = $('.elements #properties form input');
			inputs.push($('.elements #properties form textarea')[0]);

			$.each(inputs, function(i,e){
				var vartype = $(e).attr("id");
				var value = $active_component.find('[data-valtype="'+vartype+'"]')
				if(vartype==="placeholder"){
					$(value).attr("placeholder", $(e).val());
				}else if (vartype==="href"){
					$($active_component.find('a')).attr("href", $(e).val());
				}else if (vartype==="src"){
					$(value).attr("src", $(e).val());
				} else if (vartype==="checkbox"){
					if($(e).is(":checked")){
						$(value).attr("checked", true);
					}
					else{
						$(value).attr("checked", false);
					}
	    		} else if (vartype==="option"){
	        		var options = $(e).val().split("\n");
	        		$(value).html("");
	        		console.log(options);
	        		// $(value).append($('<option value="">').text('Select One'));
	      			$.each(options, function(i,e){
	        			// $(value).append("\n      ");
	        			$(value).append($('<option value='+e+'>').text(e));
	        			console.log(e);
	        		});
	    		}else if (vartype==="checkboxes"){
	        		var checkboxes = $(e).val().split("\n");
	        		$(value).html("\n      <!-- Multiple Checkboxes -->");
	        		$.each(checkboxes, function(i,e){
	        			if(e.length > 0){
	            			$(value).append('\n      <label class="checkbox">\n        <input type="checkbox" value="'+e+'">\n        '+e+'\n      </label>');
	        			}
	        		});
	        		$(value).append("\n  ")
	    		} else if (vartype==="radios"){
	        		var group_name = $(".elements #properties form #name").val();
	        		var radios = $(e).val().split("\n");
	        		$(value).html("\n      <!-- Multiple Radios -->");
	        		$.each(radios, function(i,e){
	          			if(e.length > 0){
	            			$(value).append('\n      <label class="radio">\n        <input type="radio" value="'+e+'" name="'+group_name+'">\n        '+e+'\n      </label>');
	        			}
	        		});
	        		$(value).append("\n  ")
	        		$($(value).find("input")[0]).attr("checked", true)
	    		}else if (vartype==="inline-checkboxes"){
	        		var checkboxes = $(e).val().split("\n");
	        		$(value).html("\n      <!-- Inline Checkboxes -->");
	        		$.each(checkboxes, function(i,e){
	        			if(e.length > 0){
	            			$(value).append('\n      <label class="checkbox-inline"><input type="checkbox" value="'+e+'">\n        '+e+'\n</label>');
	          			}
	        		});
	        		$(value).append("\n  ")
	    		}else if (vartype==="inline-radios"){
	        		var radios = $(e).val().split("\n");
	        		var group_name = $(".elements #properties form #name").val();
	        		$(value).html("\n      <!-- Inline Radios -->");
	        		$.each(radios, function(i,e){
	          			if(e.length > 0){
	            			$(value).append('\n      <label class="radio-inline"><input type="radio" name="'+group_name+'" value="'+e+'">\n        '+e+'\n</label>');
	          			}
	        		});
	        		$(value).append("\n  ")
	          		$($(value).find("input")[0]).attr("checked", true)
	    		}else if (vartype === "button"){
	        		var btn_type =  $(".elements #properties form #color option:selected").attr("id");
	        		var btn_size =  $(".elements #properties form #btn_size option:selected").attr("id");
	        		$(value).find("button").text($(e).val()).attr("class", "btn "+btn_type + " " + btn_size);

	    		}else if(vartype==="select-basic"){
	    			console.log('test');
	    			console.log($(this).find('select option').html());
	    		}else {
	        		$(value).text($(e).val());
	      		}

	    		$('.nav-tabs a[href="'+PORTAL_FORM_BUILDER.active_tab+'"]').tab('show');	    		
	    	});

	    	PORTAL_FORM_BUILDER.generateHTML();
	    });
	},

	/// GENERATE CODE
	generateHTML(){
		// get HTML of elements dragged to the dropzone
	    var $temptxt = $("<div>").html($("#build").html());

	    $($temptxt).find(".component").attr({"title": null,
	    	"data-original-title":null,
	    	"data-type": null,
	    	"data-content": null,
	    	"rel": null,
	    	"trigger":null,
	    	"data-html":null,
	    	"style": null
	    });
		
		$($temptxt).find(".valtype").attr("data-valtype", null).removeClass("valtype");
		$($temptxt).find(".component").removeClass("component");
		$($temptxt).find("form").attr({"id":  null, "style": null});
	    
	    PORTAL_FORM_BUILDER.generatePreview();
	},

	generatePreview(){
		var $temptxt = $("#build").html();
		$($temptxt).find(".component .hidetilloaded");

		// form heading
		var html = '<h2>'+$('#build legend.valtype').text()+'</h2>';

		$('#build .hidetilloaded').each(function(){
			html+=$(this).html();
		});

		$("#source").html(html.replace(/\n\ \ \ \ \ \ \ \ \ \ \ \ /g,"\n"));		
		$('.form_preview #source').show();
	}
}

$(document).ready(function(){

	PORTAL_FORM_BUILDER.init();

	$('.nav-tabs a').on('click', function(){
		PORTAL_FORM_BUILDER.active_tab = $(this).attr('href');
	});	
});