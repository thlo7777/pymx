/*
 * load ampa
 * for hotel gps location and user handler map
 */
;(function($, window, document, undefined) {
    // Create the defaults once
    var pluginName = 'load_amap',
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
    }

    $.fn.load_map_init = function() {

        plugin = this.data('plugin_' + pluginName);
        var opts = plugin.options;

        $(this).loading_gif_on();
        $map_container = $('<div>').attr({'id': "map_container"}).appendTo(this);
        var get_gpslocation_callback = function (address, target_id) {

            AMapUI.loadUI(['misc/PositionPicker'], function(PositionPicker) {
                map = new AMap.Map(target_id, {
                    zoom: 16,
                    center:[address.longitude, address.latitude]
                });
                var positionPicker = new PositionPicker({
                    mode:'dragMap',//设定为拖拽地图模式，可选'dragMap'、'dragMarker'，默认为'dragMap'
                    map:map//依赖地图对象
                });
                //TODO:事件绑定、结果处理等
                //

                //add toolBar plugin
                map.plugin(["AMap.ToolBar"],function(){
                    //加载工具条
                    var tool = new AMap.ToolBar({
                        //position: 'LT'
                        offset: new AMap.Pixel(10,100)
                    });
                    map.addControl(tool);
                });

                positionPicker.on('success', function(positionResult) {
                    //console.log(positionResult);

                    var province = positionResult.regeocode.addressComponent.province;
                    var city = positionResult.regeocode.addressComponent.city;
                    var district = positionResult.regeocode.addressComponent.district;

                    if (city == "") {
                        city = province;
                    }

                    address = positionResult.address.slice(province.length);
                    $(opts.address).val( address );
                    opts.hotel_info.address = address;
                    opts.hotel_info.latitude = positionResult.position.getLat();
                    opts.hotel_info.longitude = positionResult.position.getLng();

//                    opts.apply_form.marker_addr = {
//                        province: province,
//                        city: city,
//                        district: district,
//                        lng: positionResult.position.getLng(),
//                        lat: positionResult.position.getLat()
//                    };

                    //console.log(opts);
                });

                //start dragmap mode
                positionPicker.start();

            });

            $(this).loading_gif_off();
        };

        this.amap_getaddress_from_wechat_geo('map_container', get_gpslocation_callback, 'get_gpslocation_callback');
        return this;

    }

}(jQuery, window, document));

