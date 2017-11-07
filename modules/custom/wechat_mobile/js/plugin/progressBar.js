//progress bar modal
;(function($, window, document, undefined) {

    // Create the defaults once
    var pluginName = 'progressbar',
        defaults = {
            backdrop: 'static',
            show: true
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
    };

    Plugin.prototype.init = function () {
        // Place initialization logic here
        // You already have access to the DOM element and
        // the options via the instance, e.g. this.element
        // and this.options

        this.options.modalID = '#upload-progressbar';
        $modal = $('<div>').attr({
            'class': "modal fade",
            'id': "upload-progressbar",
            'role': "dialog",
            'tabindex': -1}).appendTo(this.element);
        $modal_dialog = $('<div>').attr({'class': "modal-dialog", 'role': "document"}).appendTo($modal);
        $content = $('<div>').attr({'class': "modal-content"}).appendTo($modal_dialog);
        $header = $('<div>').attr({'class': "modal-header"}).appendTo($content);
        $('<h4>').attr({'class': "modal-title"}).html( '正在上传...请等待' ).appendTo($header);
        $body = $('<div>').attr({'class': "modal-body"}).appendTo($content);
        $progress = $('<div>').attr({'class': "progress"}).appendTo($body);
        $('<div>').attr({
            'id': "form-upload-progress",
            'class': "progress-bar progress-bar-success",
            'style': "width: 0px"
        }).appendTo($progress);
        $('<span>').attr({
            'class': "progress-completed"
        }).html('0%').appendTo($progress);

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

//    $.fn.loadProgressBar = function() {
//
//        return this;
//    }
//
    $.fn.openModal = function() {
        plugin = this.data('plugin_' + pluginName);
        var opts = plugin.options;
        console.log(opts);

        console.log($(opts.modalID));
        $(opts.modalID).modal({
            backdrop: opts.backdrop,
            show: opts.show
        });
        return this;
    }

    $.fn.setProgress = function(value) {
        $('#form-upload-progress').css({'width': value.width});
        $('.progress-completed').html(value.per);
    }


}(jQuery, window, document));
