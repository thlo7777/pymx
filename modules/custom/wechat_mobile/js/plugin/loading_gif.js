/*
 * Gif loading plugin
 */
(function( $ ){
    $.fn.loading_gif_on = function() {

        $loading_fig = $('<div>').attr({'class': "loader-off", 'id': "loading-gif"}).appendTo(this);
        $loading_fig.removeClass('loader-off').addClass('loader-on');
        var loading_gif_url = window.location.origin + "/sites/default/files/pymxw/preloading_gif";
        $loading_fig.css("background-image","url(" + loading_gif_url + ")");
        $loading_fig.width(64);
        $loading_fig.height(64);
        $loading_fig.offset({
            top: ($(window).height() - $loading_fig.height()) / 2,
            left: ($(window).width() - $loading_fig.width()) / 2
        });
    };

    $.fn.loading_gif_off = function() {
        $('#loading-gif').removeClass('loader-on').addClass('loader-off');
    };

})( jQuery );

