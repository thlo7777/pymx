//hotel_main_page.js
;(function($, window, document, undefined) {
    //init ajaxSetup
    $.ajaxSetup({
        cache: false,
        headers: { "cache-control": "no-cache" },
    });

    $(document).config_wechat_location_api();

}(jQuery, window, document));

