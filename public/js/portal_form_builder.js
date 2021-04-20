$(function() {

    var form_builder = {
        el: null,
        method: "POST",
        action: "",
        delimeter: '=',

        // set current element
        setElement: function(el) {
            this.el = el;
        },

        // get current element
        getElement: function() {
            return this.el;
        },

        // clean value to be used in name
        cleanName: function(name) {
            if (name === undefined || name.length === 0) return '';

            return name
                    .trim()
                    .replace(/ /g, '_')
                    .replace(/\W/g, '')
                    .toLowerCase();
        },

        // sanitize HTML content
        cleanContent: function(content) {
            return content
                    .replace(/\t/, '')
                    .replace('element',  '')
                    .replace('ui-draggable', 'col-sm-6')
                    .replace(/<div class="close">.<\/div>/g, '')
                    .replace(/ data-(.+)="(.+)"/g, '');
        },

        // update source code
        updateSource: function() {
            var content =   "<form method=\"" + this.method + "\" " +
                            "action=\"" + this.action + "\" " +
                            "class=\"form-horizontal\">\n" +
                            $("#builder_content").html() +
                            "\n</form>";

            generatePreview(this.cleanContent(content));
            source.setValue(this.cleanContent(content));

            source.autoFormatRange(
                { line: 0, ch: 0 },
                { line: source.lastLine() + 1, ch: 0 }
            );
        },

        // ADD COMPONENT TO DROPZONE
        addComponent: function(component) {
            component
            .parent()
            .next()
            .clone()
            .removeClass('component')
            .removeClass('hidetilloaded')
            .addClass('element')
            .removeAttr('id')
            .prepend('<div class="close">&times;</div>')
            .appendTo("#builder_content")
            .find('.form-control').removeAttr('disabled');

            $("#options_modal").modal('hide');

            this.updateSource();
        },

        // load element options
        loadOptions: function(type) {
            var $el = $(this),
                options = $(".options"),
                content = options.find('.option_vals');

            $('#builder_content .element').removeClass('active');
            $(this).addClass('active');

            // fail if no type set
            if (! type) {
                return false;
            }

            return $.get('formbuilder/' + type , function(data) {            	

                if (data === undefined) {
                    return false;
                }

                // set options modal type
                options.data('type', type);

                // load relevant options
                content.html(data);

                // set selected element to clicked element
                // this removes the need to generate unique
                // id's for every created element at they are
                // passed instead of referenced
                form_builder.setElement($el);

                // add current options into fields and do any
                // necessary preprocessing

                form_builder[type].get();

                // show options modal
                $('.elements').hide();
                options.show();

                return true;
            });
        },

        // form title options
        title: {
            // options class prefix
            prefix: '.options_title_',

            // get title options
            get: function() {
                var el = form_builder.getElement(),
                    legend = el.find('legend');

                $(this.prefix + 'name')
                .val(legend.text())
                .focus();
            },

            // set title options
            set: function() {
                var el = form_builder.getElement(),
                    legend = el.find('legend');

                legend.text($(this.prefix+'name').val());
            }
        },

        // text input options
        text: {
            // options class prefix
            prefix: '.options_text_',

            // get text options
            get: function() {
                var el = form_builder.getElement();
                $(this.prefix + 'name').val(el.find('input[type=text]').attr('name'));
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'placeholder').val(el.find('input[type=text]').attr('placeholder'));
            },

            // set text options
            set: function() {
                var el = form_builder.getElement(),
                    input = el.find('input[type=text]'),
                    label = el.find('label'),
                    name = form_builder.cleanName($(this.prefix + 'name').val());

                input.attr('name', name);
                label.text($(this.prefix + 'label').val()).attr('for', name);
                input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
            }
        },

        password_input:{
        	// options class prefix
        	prefix: '.options_password_',

        	// get text options
        	get: function() {
        	    var el = form_builder.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=password]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=password]').attr('placeholder'));
        	},

        	// set text options
        	set: function() {
        	    var el = form_builder.getElement(),
        	        input = el.find('input[type=password]'),
        	        label = el.find('label'),
        	        name = form_builder.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
        	    label.text($(this.prefix + 'label').val()).attr('for', name);
        	    input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
        	}
        },

        phone_input:{
        	// options class prefix
        	prefix: '.options_text_',

        	// get phone text options
        	get: function() {
        	    var el = form_builder.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=tel]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=tel]').attr('placeholder'));
        	},

        	// set phone text options
        	set: function() {
        	    var el = form_builder.getElement(),
        	        input = el.find('input[type=tel]'),
        	        label = el.find('label'),
        	        name = form_builder.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
        	    label.text($(this.prefix + 'label').val()).attr('for', name);
        	    input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
        	}
        },

        email_input:{
        	// options class prefix
        	prefix: '.options_text_',

        	// get email text options
        	get: function() {
        	    var el = form_builder.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=email]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=email]').attr('placeholder'));
        	},

        	// set email text options
        	set: function() {
        	    var el = form_builder.getElement(),
        	        input = el.find('input[type=email]'),
        	        label = el.find('label'),
        	        name = form_builder.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
        	    label.text($(this.prefix + 'label').val()).attr('for', name);
        	    input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
        	}
        },

        // textarea options
        textarea: {
            // options class prefix
            prefix: '.options_textarea_',

            // get textarea options
            get: function() {
                var el = form_builder.getElement();

                $(this.prefix + 'name').val('');
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'placeholder').val(el.find('textarea').attr('placeholder'));
            },

            // set textarea options
            set: function() {
                var el = form_builder.getElement(),
                    textarea = el.find('textarea'),
                    label = el.find('label');

                textarea.attr('name', form_builder.cleanName($(this.prefix + 'name').val()));
                label.text($(this.prefix + 'label').val());
                textarea.attr('placeholder', $(this.prefix + 'placeholder').val());
            }
        },

        //button options
        button:{
        	prefix: '.options_button_',

        	// get text options
        	get: function() {
        	    var el = form_builder.getElement();
        	    $(this.prefix + 'label').val(el.find('input[type=submit]').val());
        	    $(this.prefix + 'color').val(el.find('input[type=submit]').attr('class').split(' ')[1]);
        	    $(this.prefix + 'size').val(el.find('input[type=submit]').attr('class').split(' ')[2]);
        	},

        	// set text options
        	set: function() {
        	    var el = form_builder.getElement();
        	    el.find('input[type=submit]').val($(this.prefix+'label').val());
        	    el.find('input[type=submit]').removeClass();
        	    el.find('input[type=submit]').addClass('btn '+ $(this.prefix+'color').val() +' '+ $(this.prefix+'size').val());
        	}
        },
    };

    $(".component")
    .draggable({
        helper: function(e) {
            return $(this).clone().addClass('component-drag');
        }
    })
    .on('click', function(e) {
        form_builder.addComponent($(this));
    });

    // remove element
    $(document).on('click', '.element > .close', function(e) {
        e.stopPropagation();

        $(this).parent().fadeOut('normal', function() {
            $(this).remove();
        });
    });

    // elements are components that have been added to the form
    // clicking elements brings up customizable options in a
    // modal window
    $(document).on('click', '.element', function(e) {
        form_builder.loadOptions.call(this, $(this).find('.form-group').data('type'));
    });

    // SAVE OPTION VALUES
    $(".options").on('click', '#save_options', function() {

        var options = $(".options"),
            content = options.find('.option_vals'),
            type = options.data('type');

        // call corresponding save method to process entered variables
        form_builder[type].set();
        goBackUnfocus();
    });

    //GO BACK TO ELEMENT PANEL
    $('.options .back').on('click', function(e){
    	e.preventDefault();
    	goBackUnfocus();
    });

    // CANCEL AND GO BACK TO ELEMENT PANEL
    $('.options').on('click', 'button#cancel_options', function(e){
    	e.preventDefault();
    	goBackUnfocus();
    });

    // REMOVE ACTIVE CLASS FROM ELEMENT, SHOW ELEMENTS PANEL, HIDE OPTIONS PANEL
    function goBackUnfocus(){
		$('#builder_content .element').removeClass('active');
		$('.elements').show();
	    $('.options').hide();
    }

    // scroll elements columns down with page scroll
    var top = $('.elements_col').offset().top - parseFloat($('.elements_col').css('marginTop').replace(/auto/, 0));
    $(window).scroll(function (event) {
        var y = $(this).scrollTop();
        //if y > top, it means that if we scroll down any more, parts of our element will be outside the viewport
        //so we move the element down so that it remains in view.
        if (y >= top) {
           var difference = y - top;
           $('.elements_col').css("top",difference);
       }
   });

    function generatePreview(code){

    	console.log(code);

		// $("#source").html(html.replace(/\n\ \ \ \ \ \ \ \ \ \ \ \ /g,"\n"));		
		$('.form_preview').html(code).show();
    }
});