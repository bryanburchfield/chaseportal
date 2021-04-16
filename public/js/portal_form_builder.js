$(function() {

    var form_builder = {
        el: null,
        method: "POST",
        action: "",
        delimeter: '=',
        setElement: function(el) {
            this.el = el;
        },

        getElement: function() {
            return this.el;
        },

        cleanName: function(name) {
            if (name === undefined || name.length === 0) return '';

            return name
                    .trim()
                    .replace(/ /g, '_')
                    .replace(/\W/g, '')
                    .toLowerCase();
        },

        cleanContent: function(content) {
            return content
                    .replace(/\t/, '')
                    .replace(/ ui-draggable| element/gi, '')
                    .replace(/<div class="close">.<\/div>/g, '')
                    .replace(/ data-(.+)="(.+)"/g, '');
        },

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
        },

        // add component to form
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
            .appendTo("#builder_content");

            $("#options_modal").modal('hide');

            this.updateSource();
        },

        // load element options
        loadOptions: function(type) {
            var $el = $(this),
                $modal = $("#options_modal"),
                content = $modal.find('.modal-body');

            if (! type) {
                return false;
            }

            return $.get('formbuilder/' + type , function(data) {            	

                if (data === undefined) {
                    return false;
                }

                $modal.data('type', type);
                content.html(data);
                form_builder.setElement($el);
                form_builder[type].get();
                $modal.modal('show');
                return true;
            });
        },

        // text input options
        text: {
            prefix: '.options_text_',
            get: function() {
                var el = form_builder.getElement();

                $(this.prefix + 'name').val('');
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

    // modal window
    $(document).on('click', '.element', function(e) {
        form_builder.loadOptions.call(this, $(this).find('.form-group').data('type'));
    });


});