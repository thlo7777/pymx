/*
 * form plugin template
 * for hotel gps location and user handler map
 */
;(function($, window, document, undefined) {
    // Create the defaults once
    var pluginName = 'FormBuild',
        defaults = {
            nid: 0,
            intervalID: -1,
        };

    // The actual plugin constructor
    function Plugin( element, options ) {
        this.element = element;
        // jQuery has an extend method that merges the
        // contents of two or more objects, storing the
        // result in the first object. The first object
        // is generally empty because we don't want to alter
        // the default options for future instances of the plugin
        this.options = $.extend( {}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype.init = function () {
        // Place initialization logic here
        // You already have access to the DOM element and
        // the options via the instance, e.g. this.element
        // and this.options
  
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                new Plugin( this, options ));
            }
        });
    };

    $.fn.createForm = function(config) {
        $formID = $('<form>').attr({'class': config.formID}).appendTo(this);
        // Initialize the editor
//        var fields, form;
//        fields = [
//            {
//            name: 'username',
//            label: '酒店名称',
//            type: 'text',
//            id: 'aabcc',
//            placeholder: '输入酒店名称'
//            }, {
//            name: 'password',
//            label: 'Passwort',
//            type: 'password'
//            }, {
//            label: 'Anmelden',
//            type: 'submit',
//            class: 'btn-default',
//            icon: 'ok'
//            }
//        ];

        form = FormForm( $formID, config.fields );
        form.render();

    };


}(jQuery, window, document));
