$(function() {

    var FORM_BUILDER = {
        el: null,
        method: "POST",
        action: "",
        delimeter: '=',
        dropzone_height:300,

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
                    .replace('class="form-horizontal"', '')
                    .replace('class="mt10 mb20"', '')
                    .replaceAll('disabled="" ', '')
                    .replaceAll('ui-draggable element', 'col-sm-6')
                    .replace(/<div class="close">.<\/div>/g, '')
                    // .replaceAll(/<div class="controls"><\/div>/g, '')
                    .replace(/ data-(.+)="(.+)"/g, '');
        },

        // update source code
        updateSource: function() {
            var content =   "<form method=\"" + this.method + "\" " +
                            "action=\"" + this.action + "\" " +
                            "class=\"form-horizontal\">\n" +
                            $("#builder_content").html() +
                            "\n</form>";
            

            source.setValue(this.cleanContent(content));
            source.autoFormatRange(
                { line: 0, ch: 0 },
                { line: source.lastLine() + 1, ch: 0 }
            );

            FORM_BUILDER.generatePreview();
        },

        generatePreview: function(){

        	var source_code = "<form method=\"" + this.method + "\" " +
        	                "action=\"" + this.action + "\">" +
        	                $("#builder_content").html() +
        	                "\n</form>";

        	source_code=source_code
	        	.replaceAll('ui-draggable element', 'col-sm-6')
        		.replaceAll('<div class="controls">', '')
                .replaceAll('disabled', '')
        		.replace(/<div class="close">.<\/div>/g, '')
        	;

    		$('.form_preview').html(source_code).show();
        },

        // add component to dropzone
        add_component: function(component) {
            component
            .parent()
            .next()
            .clone()
            .removeClass('component')
            .removeClass('hidetilloaded')
            .addClass('element')
            .removeAttr('id')
            .prepend('<div class="close">&times;</div>')
            .appendTo("#builder_content");
            // .find('.form-control').removeAttr('disabled');

            FORM_BUILDER.dropzone_height = $('form#builder_content').outerHeight(true);
            this.updateSource();
        },

        // remove component from dropzone
        remove_component:function(component){
    	    $(component).parent().remove();
    	    FORM_BUILDER.dropzone_height = $('form#builder_content').outerHeight(true);
    	    FORM_BUILDER.updateSource();
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

                // set options panel type
                options.data('type', type);

                // load relevant options
                content.html(data);

                // set selected element to clicked element
                // this removes the need to generate unique
                // id's for every created element at they are
                // passed instead of referenced
                FORM_BUILDER.setElement($el);

                // add current options into fields and do any
                // necessary preprocessing

                FORM_BUILDER[type].get();

                // show options panel
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
                var el = FORM_BUILDER.getElement(),
                    legend = el.find('legend');

                $(this.prefix + 'name')
                .val(legend.text())
                .focus();
            },

            // set title options
            set: function() {
                var el = FORM_BUILDER.getElement(),
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
                var el = FORM_BUILDER.getElement();
                $(this.prefix + 'name').val(el.find('input[type=text]').attr('name'));
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'placeholder').val(el.find('input[type=text]').attr('placeholder'));
            },

            // set text options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    input = el.find('input[type=text]'),
                    label = el.find('label'),
                    name = FORM_BUILDER.cleanName($(this.prefix + 'name').val());

                input.attr('name', name);
                input.attr('field-name', name);
                label.text($(this.prefix + 'label').val()).attr('for', name);
                input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
            }
        },

        password_input:{
        	// options class prefix
        	prefix: '.options_password_',

        	// get text options
        	get: function() {
        	    var el = FORM_BUILDER.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=password]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=password]').attr('placeholder'));
        	},

        	// set text options
        	set: function() {
        	    var el = FORM_BUILDER.getElement(),
        	        input = el.find('input[type=password]'),
        	        label = el.find('label'),
        	        name = FORM_BUILDER.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
                input.attr('field-name', name);
        	    label.text($(this.prefix + 'label').val()).attr('for', name);
        	    input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
        	}
        },

        phone_input:{
        	// options class prefix
        	prefix: '.options_text_',

        	// get phone text options
        	get: function() {
        	    var el = FORM_BUILDER.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=tel]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=tel]').attr('placeholder'));
        	},

        	// set phone text options
        	set: function() {
        	    var el = FORM_BUILDER.getElement(),
        	        input = el.find('input[type=tel]'),
        	        label = el.find('label'),
        	        name = FORM_BUILDER.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
                input.attr('field-name', name);
        	    label.text($(this.prefix + 'label').val()).attr('for', name);
        	    input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
        	}
        },

        email_input:{
        	// options class prefix
        	prefix: '.options_text_',

        	// get email text options
        	get: function() {
        	    var el = FORM_BUILDER.getElement();
        	    $(this.prefix + 'name').val(el.find('input[type=email]').attr('name'));
        	    $(this.prefix + 'label').val(el.find('label').text());
        	    $(this.prefix + 'placeholder').val(el.find('input[type=email]').attr('placeholder'));
        	},

        	// set email text options
        	set: function() {
        	    var el = FORM_BUILDER.getElement(),
        	        input = el.find('input[type=email]'),
        	        label = el.find('label'),
        	        name = FORM_BUILDER.cleanName($(this.prefix + 'name').val());

        	    input.attr('name', name);
                input.attr('field-name', name);
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
                var el = FORM_BUILDER.getElement();

                $(this.prefix + 'name').val(el.find('.form-control').attr('name'));
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'placeholder').val(el.find('textarea').attr('placeholder'));
            },

            // set textarea options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    textarea = el.find('textarea'),
                    label = el.find('label');

                textarea.attr('field-name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                textarea.attr('name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                label.text($(this.prefix + 'label').val());
                textarea.attr('placeholder', $(this.prefix + 'placeholder').val());
            }
        },

        // basic select box options
        select_basic: {
            // options class prefix
            prefix: '.options_select_basic_',

            // get basic select options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                // loop through each select option
                el.find('select > option').each(function(key, val) {
                    // if value and display text are equal
                    // dont bother showing value
                    var val_and_split = FORM_BUILDER.cleanName($(val).text()) == $(val).val() ?
                                        '' :
                                        ($(val).val() + split);

                    // add option to list
                    list_options += val_and_split + $(val).text()+"\n";
                });

                $(this.prefix + 'name').val(el.find('.form-control').attr('name'));
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set basic select options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    select = el.find('select'),
                    label = el.find('label'),
                    split = FORM_BUILDER.delimeter,

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    select_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // options buffer
                    list_options = "\n";

                // loop through each option
                $.each(select_options, function(key, val) {
                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if(val.indexOf(split) !== -1) {
                            var opt = val.split(split);

                            list_options += "<option value=\"" + opt[0] + "\">" + opt[1] + "</option>\n";
                        } else {
                            list_options += "<option value=\"" + FORM_BUILDER.cleanName(val) + "\">" + val + "</option>\n";
                        }
                    }
                });

                select.attr('name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                select.attr('field-name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                label.text($(this.prefix + 'label').val());
                select.html(list_options);
            }
        },

        // multi select box options
        select_multiple: {
            // options class prefix
            prefix: '.options_select_multiple_',

            // get multiple select options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                // loop through each select option
                el.find('select > option').each(function(key, val) {
                    // if value and display text are equal
                    // dont bother showing value
                    var val_and_split = FORM_BUILDER.cleanName($(val).text()) == $(val).val() ?
                                        '' :
                                        ($(val).val() + split);

                    // add option to list
                    list_options += val_and_split + $(val).text()+"\n";
                });

                $(this.prefix + 'name').val(el.find('.form-control').attr('name'));
                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set multiple select options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    select = el.find('select'),
                    label = el.find('label'),
                    split = FORM_BUILDER.delimeter,

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    select_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // options buffer
                    list_options = "\n";

                // loop through each option
                $.each(select_options, function(key, val) {
                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if(val.indexOf(split) !== -1) {
                            var opt = val.split(split);

                            list_options += "<option value=\"" + opt[0] + "\">" + opt[1] + "</option>\n";
                        } else {
                            list_options += "<option value=\"" + FORM_BUILDER.cleanName(val) + "\">" + val + "</option>\n";
                        }
                    }
                });

                select.attr('name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                select.attr('field-name', FORM_BUILDER.cleanName($(this.prefix + 'name').val()));
                label.text($(this.prefix + 'label').val());
                select.html(list_options);
            }
        },

        // checkbox options
        checkbox: {
            // options class prefix
            prefix: '.options_checkbox_',

            // get checkbox options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                // loop through each select option
                el.find('input[type=checkbox]').each(function(key, val) {
                    // if checkbox has value that isn't just "on", show it
                    var val_and_split = $(this).val().length > 0 && $(this).val() !== 'on' ?
                                        $(this).val()+split :
                                        '';

                    list_options += val_and_split + $(this).closest('label').text().trim() + "\n";
                });

                $(this.prefix + 'name').val('');
                $(this.prefix + 'label').val(el.find('label:first').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set checkbox options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    label = el.find('label:first'),
                    split = FORM_BUILDER.delimeter,

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    checkbox_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // element name
                    name = FORM_BUILDER.cleanName($(this.prefix + 'name').val()),

                    // options buffer
                    list_options = "\n";

                // loop through each option
                $.each(checkbox_options, function(key, val) {
                    var id = name + '_' + key;

                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if( val.indexOf(split) !== -1) {
                            var opt = val.split(split);

                            list_options += "<div class=\"checkbox\"><label for=\"" + id + "\">\n" +
                                            "<input type=\"checkbox\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + opt[0] + "\">\n" +
                                            opt[1] + "\n" +
                                            "</label></div>\n";
                        } else {
                            list_options += "<div class=\"checkbox\"><label for=\"" + id + "\">\n" +
                                            "<input type=\"checkbox\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + FORM_BUILDER.cleanName(val) + "\">\n" +
                                            val + "\n" +
                                            "</label></div>\n";
                        }
                    }
                });

                label.text($(this.prefix + 'label').val());
                el.find('.controls').html(list_options);
            }
        },

        // inline checkbox options
        inline_checkbox: {
            // options class prefix
            prefix: '.options_inline_checkbox_',

            // get checkbox options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                $(this.prefix + 'name').val(el.find(' input').attr('name'));

                // loop through each select option
                el.find('input[type=checkbox]').each(function(key, val) {
                    // if checkbox has value that isn't just "on", show it
                    var val_and_split = $(this).val().length > 0 && $(this).val() !== 'on' ?
                                        $(this).val()+split :
                                        '';

                    list_options += val_and_split + $(this).closest('label').text().trim() + "\n";
                });

                // $(this.prefix + 'name').val('');
                $(this.prefix + 'label').val(el.find('label:first').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set checkbox options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    label = el.find('label:first'),
                    split = FORM_BUILDER.delimeter,

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    checkbox_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // element name
                    name = FORM_BUILDER.cleanName($(this.prefix + 'name').val()),

                    // options buffer
                    list_options = "\n";

                // loop through each option
                $.each(checkbox_options, function(key, val) {
                    var id = name + '_' + key;

                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if( val.indexOf(split) !== -1) {
                            var opt = val.split(split);

                            list_options += "<label class=\"checkbox-inline\" for=\"" + id + "\">\n" +
                                            "<input type=\"checkbox\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + opt[0] + "\">\n" +
                                            opt[1] + "\n" +
                                            "</label>\n";
                        } else {
                            list_options += "<label class=\"checkbox-inline\" for=\"" + id + "\">\n" +
                                            "<input type=\"checkbox\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + FORM_BUILDER.cleanName(val) + "\">\n" +
                                            val + "\n" +
                                            "</label>\n";
                        }
                    }
                });

                label.text($(this.prefix + 'label').val());
                el.find('.controls').html(list_options);
            }
        },

        // radio buttons options
        radio: {
            // options class prefix
            prefix: '.options_radio_',

            // get radio buttons options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                // loop through each select option
                el.find('input[type=radio]').each(function(key, val) {
                    // if radio has value that isn't just "on", show it
                    var val_and_split = $(this).val().length > 0 && $(this).val() !== 'on' ?
                                        $(this).val() + split :
                                        '';

                    list_options += val_and_split + $(this).closest('label').text().trim() + "\n";
                });

                $(this.prefix + 'name').val(el.find(' input').attr('name'));
                $(this.prefix + 'label').val(el.find('label:first').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set radio button options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    label = el.find('label:first'),
                    split = FORM_BUILDER.delimeter,

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    radio_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // element name
                    name = FORM_BUILDER.cleanName($(this.prefix + 'name').val()),

                    // options buffer
                    list_options = "\n";

                // loop through each option
                $.each(radio_options, function(key, val) {
                    var id = name+'_'+key;

                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if (val.indexOf(split) !== -1) {
                            var opt = val.split(split);

                            list_options += "<div class=\"radio\"><label for=\"" + id + "\">\n" +
                                            "<input type=\"radio\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + opt[0] + "\">\n" +
                                            opt[1] + "\n" +
                                            "</label></div>\n";
                        } else {
                            list_options += "<div class=\"radio\"><label for=\"" + id + "\">\n" +
                                            "<input type=\"radio\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + FORM_BUILDER.cleanName(val) + "\">\n" +
                                            val + "\n" +
                                            "</label></div>\n";
                        }
                    }
                });

                label.text($(this.prefix + 'label').val());
                el.find('.controls').html(list_options);
            }
        },

         // inline radio buttons options
        inline_radio:{
            // options class prefix
            prefix: '.options_inline_radio_',

            // get radio buttons options
            get: function() {
                var el = FORM_BUILDER.getElement(),
                    list_options = '',
                    split = FORM_BUILDER.delimeter;

                $(this.prefix + 'name').val(el.find('input').attr('name'));

                // loop through each select option
                el.find('input[type=radio]').each(function(key, val) {
                    // if radio has value that isn't just "on", show it
                    var val_and_split = $(this).val().length > 0 && $(this).val() !== 'on' ?
                                        $(this).val() + split :
                                        '';

                    list_options += val_and_split + $(this).closest('label').text().trim() + "\n";
                });

                $(this.prefix + 'label').val(el.find('label:first').text());
                $(this.prefix + 'options').val(list_options);
            },

            // set radio button options
            set: function() {
                var el = FORM_BUILDER.getElement(),
                    label = el.find('label:first'),
                    split = FORM_BUILDER.delimeter,
                    input = el.find('input[type=text]'),

                    // textarea options
                    options_blob = $(this.prefix + 'options').val(),

                    // split options by line break
                    radio_options = options_blob.replace(/\r\n/, "\n").split("\n"),

                    // element name
                    name = FORM_BUILDER.cleanName($(this.prefix + 'name').val()),

                    // options buffer
                    list_options = "\n";
                // loop through each option
                $.each(radio_options, function(key, val) {
                    var id = name+'_'+key;

                    if (val.length > 0) {
                        // if delimiter found, split val into array value -> display
                        if (val.indexOf(split) !== -1) {
                            var opt = val.split(split);
                            list_options += "<label class=\"radio-inline\" for=\"" + id + "\">\n" +
                                            "<input type=\"radio\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + opt[0] + "\">\n" +
                                            opt[1] + "\n" +
                                            "</label>\n";
                        } else {
                            list_options += "<label class=\"radio-inline\" for=\"" + id + "\">\n" +
                                            "<input type=\"radio\" name=\"" + name + "\" " +
                                            "id=\"" + id + "\" " +
                                            "value=\"" + FORM_BUILDER.cleanName(val) + "\">\n" +
                                            val + "\n" +
                                            "</label>\n";
                        }
                    }
                });

                // input.attr('name', name);
                // input.attr('placeholder', $(this.prefix + 'placeholder').val()).attr('id', name);
                label.text($(this.prefix + 'label').val());
                el.find('.controls').html(list_options);
            }
        },

        // static text options
        static_text: {
            prefix: '.options_static_text_',

            get: function() {
                var el = FORM_BUILDER.getElement();

                $(this.prefix + 'label').val(el.find('label').text());
                $(this.prefix + 'text').val(el.find('.form-group p').html().trim());
            },

            set: function() {
                var el = FORM_BUILDER.getElement();
                el.find('label').text($(this.prefix + 'label').val());
                el.find('.form-group p').html($(this.prefix + 'text').val());
            }
        },

        //button options
        button:{
        	prefix: '.options_button_',

        	// get text options
        	get: function() {
        	    var el = FORM_BUILDER.getElement();
        	    $(this.prefix + 'label').val(el.find('input[type=submit]').val());
        	    $(this.prefix + 'color').val(el.find('input[type=submit]').attr('class').split(' ')[1]);
        	    $(this.prefix + 'size').val(el.find('input[type=submit]').attr('class').split(' ')[2]);
        	},

        	// set text options
        	set: function() {
        	    var el = FORM_BUILDER.getElement();
        	    el.find('input[type=submit]').val($(this.prefix+'label').val());
        	    el.find('input[type=submit]').removeClass();
        	    el.find('input[type=submit]').addClass('btn '+ $(this.prefix+'color').val() +' '+ $(this.prefix+'size').val());
        	}
        },
    };

    //  make form elements components that can be dragged or clicked
    $(".component")
    .draggable({
        helper: function(e) {
            return $(this).clone().addClass('component-drag');
        }
    })
    .on('click', function(e) {
        FORM_BUILDER.add_component($(this));
    });

    // remove element when clicking close button
    $(document).on('click', '.element > .close', function(e) {
        e.stopPropagation();
        FORM_BUILDER.remove_component($(this));
    });

    // elements are components that have been added to the dropzone/ clicking an element opens options panel
    $(document).on('click', '.element', function(e) {
        FORM_BUILDER.loadOptions.call(this, $(this).find('.form-group').data('type'));
    });

    // save option values
    $(".options").on('click', '#save_options', function() {

        var options = $(".options"),
            content = options.find('.option_vals'),
            type = options.data('type');

        // call corresponding save method to process entered variables
        FORM_BUILDER[type].set();
        goBackUnfocus();
        FORM_BUILDER.generatePreview();
    });

    // go back to elements panel
    $('.options .back').on('click', function(e){
    	e.preventDefault();
    	goBackUnfocus();
    });

    // cancel and go back to elements panel
    $('.options').on('click', 'button#cancel_options', function(e){
    	e.preventDefault();
    	goBackUnfocus();
    });

    // remove active class from element, show elements panel, hide options panel
    function goBackUnfocus(){
		$('#builder_content .element').removeClass('active');
		$('.elements').show();
	    $('.options').hide();
    }

    //prevent default of form elements
    $(document).on('click', '.element > input, .element > textarea, .element > label', function(e) {
        e.preventDefault();
    });

    // dropzone accepts components and converts them to elements / makes them sortable
    $("#builder_content")
    .droppable({
        accept: '.component',
        hoverClass: 'content-hover',
        drop: function(e, ui) {
            console.log('Dropped');
            FORM_BUILDER.add_component(ui.draggable);
        }
    })
    .sortable({
        placeholder: "element-placeholder",
        start: function(e, ui) {
            console.log('Sorted');

            ui.item.popover('hide');
            setTimeout(function() {
               FORM_BUILDER.updateSource();
               source.refresh();
           }, 1);
        }
    })
    .disableSelection();

    //  change form title by clicking the legend
    $("#content_form_name").on('click', function(e) {
        FORM_BUILDER.loadOptions.call(this, 'title');
    });

    // create codemirror instance & assign to global var source
    source = CodeMirror.fromTextArea(document.getElementById("source"), {
        lineNumbers: true,
        tabMode: 'indent',
        mode: { name: 'htmlmixed' }
    });

    // hack to sort random bug with codemirror & bootstrap tabs not playing nicely
    $("a[href=#source-tab],a[href=#preview-tab]").on('click', function(e) {
        setTimeout(function() {
            FORM_BUILDER.updateSource();
            source.refresh();
        }, 1);
    });

    // scroll elements sidebar / stop at bottom of preview tab-panel
    var $sidebar   = $(".elements_col"), 
        $window    = $(window),
        offset     = $sidebar.offset(),
        topPadding = 15
    ;
    
    FORM_BUILDER.dropzone_height = $('form#builder_content').outerHeight(true);
        
    $window.scroll(function() {
    	if($window.scrollTop() >= FORM_BUILDER.dropzone_height) {
            $sidebar.stop().animate({
                marginTop: 0
            });
        }else if ($window.scrollTop() > offset.top) {
            $sidebar.stop().animate({
                marginTop: $window.scrollTop() - offset.top
            });
        }
    });

    $('.download_file').on('click', function(e){
        e.preventDefault();

        var elem = document.createElement('a');
        elem.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(get_html()));
        elem.setAttribute('download', 'index.html');

        elem.style.display = 'none';
        document.body.appendChild(elem);
        elem.click();
        document.body.removeChild(elem);
    });

    function get_html(){
         var source =   '<!DOCTYPE html>'+"\n"+
                        '<html>'+"\n"+
                        '<head>'+"\n"+
                            '<meta charset="utf-8">'+"\n"+
                            '<meta http-equiv="X-UA-Compatible" content="IE=edge">'+"\n"+
                            '<meta name="viewport" content="width=device-width, initial-scale=1">'+"\n"+
                            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap-theme.css" crossorigin="anonymous" />'+"\n"+
                            +"\n"+
                            '<form method=" + this.method + " ' +
                            'action=" + this.action + " ' +
                            'class="form-horizontal">' +
                            $("#builder_content").html() +
                            '\n</form>';
        // console.log(content);
        return source;
    }

});