/*
 * add new shop for shopkeeper
 */
;(function($, window, document, undefined) {
 
    // Create the defaults once
    var pluginName = 'dld_shop_form',
        defaults = {
            nid: 0,
            intervalID: -1,
            progressBar: 10,  //10%
            apply_form: {}
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

    /*
     * gif loading for generate share shop qr code
     */
    $.fn.loading_gif_on = function() {
        $(this).removeClass('loader-off').addClass('loader-on');
        $(this).css("background-image","url(" + gif_loading + ")");
        $(this).width(64);
        $(this).height(64);
        $(this).offset({ top: ($(window).height() - $(this).height()) / 2, left: ($(window).width() - $(this).width()) / 2 });
    };
    $.fn.loading_gif_off = function() {
        $(this).removeClass('loader-on').addClass('loader-off');
    };

    /*
     * create customer service registration form
     */
    $.fn.shop_load_map = function() {

        //gif loading on for wait generate qr
        $('#dld-loading-gif').loading_gif_on();

        plugin = this.data('plugin_' + pluginName);

        var opts = plugin.options;

        $map_container = $('<div>').attr({'id': "map_container"}).appendTo(this);
        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_dld_shop_gps_loc.description
        ).appendTo(this);

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

                    opts.apply_form.marker_addr = {
                        province: province,
                        city: city,
                        district: district,
                        lng: positionResult.position.getLng(),
                        lat: positionResult.position.getLat()
                    };

                    //console.log(opts);
                });

                //start dragmap mode
                positionPicker.start();

            });

            $('#dld-loading-gif').loading_gif_off();
        };

        this.amap_getaddress_from_wechat_geo('map_container', get_gpslocation_callback, 'get_gpslocation_callback');

        return this;

    };  //$.fn.shop_load_map

    /*
     * create shopkeeper register form
     */
    $.fn.add_new_shop = function() {

        plugin = this.data('plugin_' + pluginName);

        var self_form_name = "form-add-new-shop";
        plugin.options.self_form_name = self_form_name;

        var opts = plugin.options;

        $form = $('<form>').attr({
            'class': "form-horizontal " + self_form_name
        }).appendTo(this);

        $fieldset = $('<fieldset>').appendTo($form);
        $('<legend>').html('填写新店信息').appendTo($fieldset);

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_radiogroup_2switch.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group'}).appendTo($fieldset);
        $('<label>').attr({'class': "col-lg-2 control-label"}).html('批发或零售行业').appendTo($form_group);
        $col_lg_10 = $('<div>').attr({'class': "col-lg-10"}).appendTo($form_group);

        $div = $('<div>').attr({
            'class': "dld-btn-toggle retail-whls",
            'data-toggle': "buttons"}).appendTo($col_lg_10);
        //零售业务
        $('<label>').attr({
            'class': "btn btn-sm btn-danger test-shop-type",
            'id': "shop-retail"
            }).html(
            '<input type="radio" name="options" id="option-rw1" autocomplete="off" checked>' +
            '<i class="zmdi zmdi-store-24 zmdi-hc-lg zmdi-hc-fw"></i>零售行业'
        ).appendTo($div);
        //批发业务
        $('<label>').attr({
            'class': "btn btn-sm btn-warning test-shop-type active",
            'id': "shop-whls"
            }).html(
            '<input type="radio" name="options" id="option-rw2" autocomplete="off">' +
            '<i class="zmdi zmdi-store zmdi-hc-lg zmdi-hc-fw"></i>批发行业'
        ).appendTo($div);
        $(document).on('click', '.test-shop-type', function(e) {
            console.log($(this).prop('id'));
            if ($(this).prop('id') == 'shop-retail') {
                //to whls
            } else {
                //to retail
            }
        });

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="select-shop-term' + opts.nid + '" class="col-lg-2 control-label">选择店铺类型</label>' +
            '<div class="col-lg-10">' +
                '<select class="form-control" data-vid="' + shop_category_voc + '" id="select-shop-term">' +
                    '<option></option>' +
                '</select>' +
            '</div>'
        ).appendTo($fieldset);
        $("#select-shop-term").select2({

            placeholder: "选择店铺类型",
            minimumResultsForSearch: Infinity,
            language: "zh-CN",

            //ajax call
            cache: true,
            ajax: {
                url: $.dld_get_restful_url(),
                dataType: "json",
                //json data
                data: function(params) {
                    return {
                        data: {
                            type: "get_shop_category_term",
                            xyz: opts.xyz,
                            vid: shop_category_voc
                        } 
                    };
                },

                //process result data
                processResults: function (data, params) {
                    return {
                        results: data.data.terms
                    };
                }
            }

        }); //dld shop category term

        $("#select-shop-term").on('select2:select', function (event) {
            //upload_product_content.sm_tid = event.params.data.id;
            opts.apply_form.shop_term = {vid: $(this).data('vid'), tid: event.params.data.id};
            //console.log($(this).data('vid'));
            //console.log(event.params.data.id);
        });

        //select_shop_category_term("#select-shop-term", opts);

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-name-' + opts.nid + '" class="col-lg-2 control-label">店铺名称</label>' +
            '<div class="col-lg-10">' +
                '<input type="text" class="form-control" id="input-name-' + opts.nid + '" placeholder="填写店铺名称">' +
            '</div>'
        ).appendTo($fieldset);

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-phone-' + opts.nid + '" class="col-lg-2 control-label">联系电话</label>' +
            '<div class="col-lg-10">' +
                '<input type="number" class="form-control" id="input-phone-' + opts.nid + '" placeholder="填写电话">' +
            '</div>'
        ).appendTo($fieldset);

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-home-' + opts.nid + '" class="col-lg-2 control-label">店铺详细地址</label>' +
            '<div class="col-lg-10">' +
                '<textarea class="form-control" rows="3" id="input-home-' + opts.nid + '" placeholder="精确到门牌楼号"></textarea>' +
            '</div>'
        ).appendTo($fieldset);

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-desc-' + opts.nid + '" class="col-lg-2 control-label">店铺简介</label>' +
            '<div class="col-lg-10">' +
                '<textarea class="form-control" rows="3" id="input-desc-' + opts.nid + '" placeholder="店铺经营业务简单介绍"></textarea>' +
            '</div>'
        ).appendTo($fieldset);

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_shop_qualify_pics.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group pics-form'}).html(
            '<label for="input-picture-bl-' + opts.nid + '" class="col-lg-2 control-label">营业执照</label>' +
            '<div class="col-lg-10">' +
                '<a href="javascript:void(0)" class="btn btn-primary btn-sm input-picture-bl" data-id="' + opts.nid + '">' +
                    '<i class="zmdi zmdi-plus zmdi-hc-lg zmdi-hc-fw"></i>' +
                    '添加照片</a>' +
            '</div>'
        ).appendTo($fieldset);

        $(document).on('click', '.input-picture-bl', function(event) {
            event.preventDefault();
            $('.form-group.pics-form').input_pics_button(1);
        });


        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_restaurant_pictures_large.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group facade-pics'}).html(
            '<label for="input-facade-pics-' + opts.nid + '" class="col-lg-2 control-label">门脸照片或店铺商标</label>' +
            '<div class="col-lg-10">' +
                '<a href="javascript:void(0)" class="btn btn-primary btn-sm input-facade-pics" ' +
                'data-id="' + opts.nid + '"> ' + 
                    '<i class="zmdi zmdi-plus zmdi-hc-lg zmdi-hc-fw"></i>' +
                '添加门脸照片</a>' +
            '</div>'
        ).appendTo($fieldset);
        $(document).on('click', '.input-facade-pics', function(event) {
            event.preventDefault();
            $('.form-group.facade-pics').input_pics_button(1);
        });

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_restaurant_min_charge.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-min-charge-' + opts.nid + '" class="col-lg-2 control-label">起送价格</label>' +
            '<div class="col-lg-10">' +
                '<div class="input-group">' +
                '<input type="number" class="form-control" id="input-min-charge-' + opts.nid + '" placeholder="起送价格">' +
                '<span class="input-group-addon">元</span>' +
                '</div>' +
            '</div>'
        ).appendTo($fieldset);

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_free_express_distance.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-exp-dist-' + opts.nid + '" class="col-lg-2 control-label">免费快递距离</label>' +
            '<div class="col-lg-10">' +
                '<div class="input-group">' +
                '<input type="number" class="form-control" id="input-exp-dist-' + opts.nid + '" placeholder="免费快递距离">' +
                '<span class="input-group-addon">公里</span>' +
                '</div>' +
            '</div>'
        ).appendTo($fieldset);

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_dist_exceed_fee.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-dist-exceed-fee-' + opts.nid + '" class="col-lg-2 control-label">超出免费距离快递费</label>' +
            '<div class="col-lg-10">' +
                '<div class="input-group">' +
                '<input type="number" class="form-control" id="input-dist-exceed-fee-' + opts.nid + '" placeholder="快递费">' +
                '<span class="input-group-addon">元</span>' +
                '</div>' +
            '</div>'
        ).appendTo($fieldset);

        $('<div>').attr({'class': "alert alert-info"}).html(
            opts.node_help.field_exp_time_arrival.description
        ).appendTo($fieldset);
        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-exp-tiem-arrival-' + opts.nid + '" class="col-lg-2 control-label">快递送达时间</label>' +
            '<div class="col-lg-10">' +
                '<input type="text" class="form-control" id="input-exp-tiem-arrival" placeholder="填写快递时间">' +
            '</div>'
        ).appendTo($fieldset);

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="input-work-time-' + opts.nid + '" class="col-lg-2 control-label">营业时间</label>' +
            '<div class="col-lg-5">' +
                '<div class="input-group bootstrap-timepicker timepicker">' +
                    '<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i>上午</span>' +
                    '<input id="timepicker1" type="text" class="form-control input-small">' +
                '</div>' +
                '<div class="input-group bootstrap-timepicker timepicker">' +
                    '<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i>下午</span>' +
                    '<input id="timepicker2" type="text" class="form-control input-small">' +
                '</div>' +
            '</div>'
        ).appendTo($fieldset);


        $('#timepicker1').timepicker({
            minuteStep: 15,
            showSeconds: false,
            showInputs: false,
            showMeridian: false,
            disableFocus: false
        });

        $('#timepicker2').timepicker({
            minuteStep: 15,
            showSeconds: false,
            showInputs: false,
            showMeridian: false,
            disableFocus: false
        });

        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<label for="week-day-' + opts.nid + '" class="col-lg-2 control-label">选择工作日</label>' +
            '<div class="col-lg-10">' +
            	'<div class="btn-group" data-toggle="buttons">' +
			
                    '<label class="weekday btn btn-sm btn-success active">' +
                        '<input type="checkbox" autocomplete="off" checked>一' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +

                    '<label class="weekday btn btn-sm btn-primary active">' +
                        '<input type="checkbox" autocomplete="off">二' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +
		
                    '<label class="weekday btn btn-sm btn-info active">' +
                        '<input type="checkbox" autocomplete="off">三' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +
		
                    '<label class="weekday btn btn-sm btn-default active">' +
                        '<input type="checkbox" autocomplete="off">四' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +

                    '<label class="weekday btn btn-sm btn-warning active">' +
                        '<input type="checkbox" autocomplete="off">五' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +
		
                    '<label class="weekday btn btn-sm btn-danger active">' +
                        '<input type="checkbox" autocomplete="off">六' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +

                    '<label class="weekday btn btn-sm alert-info active">' +
                        '<input type="checkbox" autocomplete="off">日' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                    '</label>' +
                
                '</div>' +
            '</div>'
        ).appendTo($fieldset);

        //submit register page
        $form_group = $('<div>').attr({'class': 'form-group'}).html(
            '<div class="col-lg-12 text-center">' +
                '<button type="submit" class="btn btn-primary submit-register-form">提交注册信息</button>' +
            '</div>'
        ).appendTo($fieldset);
        $(document).on('click', '.submit-register-form', { plugin : plugin }, function(event) {

            event.preventDefault();

            plugin = event.data.plugin;
            opts = plugin.options;
            
            if (! $('.' + opts.self_form_name).form_validation(plugin)) {
                return ;
            }

            $(this).prop("disabled", true); //disable button before send data to server
            $('.' + opts.self_form_name).submit_register_form(plugin, '.submit-register-form');

        });

        return this;

    };  //$.fn.add_register_form

    //form validation
    $.fn.form_validation = function(plugin) {

        opts = plugin.options;

        //validate data
        if (! opts.apply_form.hasOwnProperty('marker_addr')) {
            $('html, body').animate({
                scrollTop: $('body').offset().top - 50
            }, 1000);
        }

        $input = $('#select-shop-term');
        if (! opts.apply_form.hasOwnProperty('shop_term')) {
            alert('请选择店铺分类');
            $input.focus();
            return false;
        }

        $('.retail-whls > label').each(function(e) {

            if ($(this).hasClass('active')) {
                if ($(this).attr('id') == "shop-whls") {
                    opts.apply_form.whls_retail = 1;    //reverse with active
                } else {
                    opts.apply_form.whls_retail = 0;
                }
            }
        });

        $input = $('#input-name-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入店铺名称');
            $input.focus();
            return false;
        } else {
            opts.apply_form.shop_name = $input.val();
        }

        $input = $('#input-phone-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入店铺联系电话');
            $input.focus();
            return false;
        } else {
            intRegex = /^\d{11}$/;
            if(($input.val().length < 6) || (!intRegex.test($input.val()))) {
                alert('请输入有效的移动电话号码');
                $input.focus();
                return ;
            }
        }
        opts.apply_form.shop_phone = $input.val();

        $input = $('#input-home-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入店铺详细地址,精确到门牌楼号');
            $input.focus();
            return false;
        } else {
            opts.apply_form.shop_home_addr = $input.val();
        }

        $input = $('#input-desc-' + opts.nid);
        if ($input.val().length) {
            opts.apply_form.shop_desc = $input.val();
        }

        img_num = $('.form-group.pics-form').find('.row.pic-grid .pic-item img');
        if (! img_num.length) {
            $('.input-picture-bl').focus();
            alert('请添加营业执照');
            return false;
        } else {
            opts.apply_form.pics_form = {};
            opts.apply_form.pics_form.localids = [];
            img_num.each(function() {
                opts.apply_form.pics_form.localids.push($(this).attr('src'));
            });
        }

        img_num = $('.form-group.facade-pics').find('.row.pic-grid .pic-item img');
        if (! img_num.length) {
            $('.input-facade-pics').focus();
            alert('请添加门脸照片');
            return false;
        } else {
            opts.apply_form.facade_pics = {};
            opts.apply_form.facade_pics.localids = [];
            img_num.each(function() {
                opts.apply_form.facade_pics.localids.push($(this).attr('src'));
            });
        }


        $input = $('#input-min-charge-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入起送价格');
            $input.focus();
            return false;
        } else {
            opts.apply_form.min_charge_fee = $input.val();
        }

        $input = $('#input-exp-dist-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入免费快递距离');
            $input.focus();
            return false;
        } else {
            opts.apply_form.exp_distance = $input.val();
        }

        $input = $('#input-dist-exceed-fee-' + opts.nid);
        if (! $input.val().length) {
            alert('请输入超出快递距离价格');
            $input.focus();
            return false;
        } else {
            opts.apply_form.dist_exceed_fee = $input.val();
        }


        $input = $('#input-exp-tiem-arrival');
        if ($input.val().length) {
            opts.apply_form.exp_time_a = $input.val();
        } else {
            opts.apply_form.exp_time_a = '';
        }

        //working time
        $input = $('#timepicker1');
        if ($input.val().length) {
            opts.apply_form.time_am = $input.val();
        }

        $input = $('#timepicker2');
        if ($input.val().length) {
            opts.apply_form.time_pm = $input.val();
        }

        //week day
        $days = $('.btn-group > label.btn');
        opts.apply_form.week_days = [];
        opts.apply_form.week_days[0] = 'w';
        var i = 1;
        $days.each(function() {
            if ($(this).hasClass('active')) {
                opts.apply_form.week_days[i] = 1;
            } else {
                opts.apply_form.week_days[i] = 0;
            }
            i++;
        });

        return true;

    };

    $.fn.submit_register_form = function(plugin, buttonClass) {
        var opts = plugin.options;

        //notify url for apply to super admin
        opts.apply_form.notify_url = opts.notify_url;

        $(buttonClass).prop("disabled", true); //disable button before send data to server

        //show pregressbar modal
        $('#upload-progressbar').modal({
            backdrop: 'static',
            show: true
        });
        $('#form-upload-progress').css({'width': "0px"});
        $('.progress-completed').html("0%");

        //upload shop license pics and get media ids
        var pics_form = upload_deferred('pics-form', opts.apply_form.pics_form.localids);
        $.when(pics_form).done(function(var1) {
            //console.log(var1);
            if (var1.length){
                opts.apply_form.license_pics = var1;
            }
            width = $('#form-upload-progress').css({'width': "50%"});
            $('.progress-completed').html("50%");

            //facede pics
            var facade_pics = upload_deferred('facade-pics', opts.apply_form.facade_pics.localids);
            $.when(facade_pics).done(function(var1) {
                //console.log(var1);
                if (var1.length){
                    opts.apply_form.facade_pics = var1;
                }
                width = $('#form-upload-progress').css({'width': "100%"});
                $('.progress-completed').html("100%");
                setTimeout(function(){
                    $('#upload-progressbar').modal('hide');
                }, 500);

                //call ajax send form to server
                var json_data = {
                    type: "add_dld_shop_form",
                    xyz: opts.xyz,
                    apply_form: opts.apply_form
                };
                var form_submit_finish = function(data, that) {
                    //clear submit button, move to top and aler info waiting audit
                    //window.location.href = opts.manage_url;
                    clear_submit_button(that, buttonClass);
                    //go to generate shop page
                    new_url = gen_shopqr_url.replace(/%/, data.shop_nid);

                    window.location.href = new_url;    //go to shop qr page
                };
                $.dld_ajax_submit("POST", json_data, plugin, form_submit_finish, 'form_submit_finish');

            }).fail(function(var1) {
                alert(var1);
                console.log(var1);
            });

        }).fail(function(var1) {
            alert(var1);
            console.log(var1);
        });

    };

    //call wechat to select pictures for each input pics button
    $.fn.input_pics_button = function(num) {

        var render_local_pics = function(localids, that) {

            //find all imgs number
            var imgs_num = that.find('.row.pic-grid img').length;

            //can plug how many pics to form pics
            var rest_imgs = num - imgs_num;
            if (! rest_imgs) {
                return ;    //enough pics by num size
            }

            //rest_imgs = rest_imgs >= localids.length ? localids.length : rest_imgs;
            var add_ids = localids.slice(0, rest_imgs);

            //have to check if row pic-grid exists then plugin a pic or create new row pic-grid
            var row_grid = that.find('.row.pic-grid').each(function() {

                image_width = $(this).width()/2 - 3;

                if ($(this).find('.pic-item img').length < 2) {

                    $(this).find('.pic-item').each(function() {
                        var item_this = this;
                        if (! $(item_this).find('img').length && add_ids.length) {
                            $('<img>').attr({
                                "src": add_ids.shift(),
                                "class": "img-responsive"
                            }).css({"width": image_width, "height": "100%"}).appendTo(item_this);

                            pic_a = $('<a>').attr({
                                "href": "javascript:void(0)",
                                "class": "delete-image"
                            }).css({"margin-left": image_width/2 - 7}).html('<i class="zmdi zmdi-minus-circle-outline zmdi-hc-lg"></i>').appendTo(item_this);

                        }
                    });

                    if ($(this).find('.pic-item').length != 2 && add_ids.length) {

                        pic_grid_6 = $('<div>').attr({
                            "class": "col-xs-6 pic-item",
                            "data-left": 0
                        }).appendTo(this);

                        $('<img>').attr({
                            "src": add_ids.shift(),
                            "class": "img-responsive"
                        }).css({"width": image_width, "height": "100%"}).appendTo(pic_grid_6);

                        pic_a = $('<a>').attr({
                            "href": "javascript:void(0)",
                            "class": "delete-image"
                        }).css({"margin-left": image_width/2 - 7}).html('<i class="zmdi zmdi-minus-circle-outline zmdi-hc-lg"></i>').appendTo(pic_grid_6);

                    }
                }
                //console.log(self);
            });

            //define each of row has 2 pic
            var rows = Math.ceil(add_ids.length/2);
            for (var i = 0; i < rows; i++) {
                pic_row = $('<div>').attr({"class": "row pic-grid"}).appendTo(that);
                image_width = pic_row.width()/2 - 3;

                    for (var j = 0; j < 2; j++) {
                        if (add_ids.length) {

                            if (j % 2) {
                                pic_grid_6 = $('<div>').attr({
                                    "class": "col-xs-6 pic-item",
                                    "data-left": 0
                                }).appendTo(pic_row);
                            } else {
                                pic_grid_6 = $('<div>').attr({
                                    "class": "col-xs-6 pic-item",
                                    "data-left": 1
                                }).appendTo(pic_row);
                            }

                            $('<img>').attr({
                                "src": add_ids.shift(),
                                "class": "img-responsive"
                            }).css({"width": image_width, "height": "100%"}).appendTo(pic_grid_6);

                            pic_a = $('<a>').attr({
                                "href": "javascript:void(0)",
                                "class": "delete-image"
                            }).css({"margin-left": image_width/2 - 7}).html('<i class="zmdi zmdi-minus-circle-outline zmdi-hc-lg"></i>').appendTo(pic_grid_6);

                    }
                }

            }
        };

        var that = this;    //protect self element for sucess function overwrite this
        //select image form wechat
        wx.chooseImage({
            count: num, // 默认9
            sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片, how many rows 
                render_local_pics(res.localIds, that);
            }
        });

    };



    //for input pics delete image
    $(document).on('click', '.delete-image', function(event) {
        event.preventDefault();
        var $p_element = $(this).parent().parent();
        $(this).parent().children().each(function() {
            $(this).remove();
        });

        var count = $p_element.find('img').length;
        if ( ! count) {
            $p_element.remove();
        }
    });

    //recursive function for upload locaid to wechat server and get media id
    var upload_deferred = function(type, localids) {
        var dtd = $.Deferred(); //在函数内部，新建一个Deferred对象
        var media_ids = [];

        //sync pics with wechat server
        var syncUpload = function(localIds) {
            //alert(localIds.length);

            if (localIds.length) {
                var localId = localIds.shift();

                if (window.__wxjs_is_wkwebview === true) {
                    //https://mp.weixin.qq.com/advanced/wiki?t=t=resource/res_main&id=mp1483682025_enmey
                    //iOS WKWebview 网页开发适配指南 
                    //二：页面通过LocalID预览图片 
                    //变化：1.2.0以下版本的JSSDK不再支持通过使用chooseImage api返回的
                    //localld以如：”img src=wxLocalResource://50114659201332”的方式预览图片。 
                    //适配建议：直接将JSSDK升级为1.2.0最新版本即可帮助页面自动适配，但在部分场景下可能无效，此时可以使用
                    //getLocalImgData 接口来直接获取数据。
                    //
                    var upload_image_callback = function(data) {
                        //alert(JSON.stringify(data));
                        if (data.message == "ok" && data.count == 1) {

                            var serverId = data.serverId; // 返回图片的服务器端ID
                            media_ids.push(serverId);

                            if(localIds.length > 0){
                                syncUpload(localIds);
                            } else {
                                //get all meida ids and return value
                                dtd.resolve(media_ids);
                            }
                        }
                    };
                    $.dld_uploadImage(localId, upload_image_callback);

                } else {

                    wx.uploadImage({

                        localId: localId,
                        isShowProgressTips: 0,  //don't show wechat instead use progress bar
                        success: function (res) {
                            var serverId = res.serverId; // 返回图片的服务器端ID
                            media_ids.push(serverId);

                            if(localIds.length > 0){
                                syncUpload(localIds);
                            } else {
                                //get all meida ids and return value
                                dtd.resolve(media_ids);
                            }
                        },

                        //error handle
                        fail: function(res) {
                            alert(JSON.stringify(res));
                            dtd.reject(type + ": upload error");
                        }

                    });
                }

            } else {
                dtd.resolve(null);
            }
        };

        //call upload multip images
        syncUpload(localids);

        return dtd.promise(); // 返回promise对象
    };
 
    /*
     * clear submit button for create and modified form
     */
    function clear_submit_button(plugin, button_type) {
        var opts = plugin.options;
        //remove submit button
        $(button_type).remove();

//        $('div.alert.alert-info').remove();
//        
//        $alert = $('<div>').addClass('alert alert-info').css({ "overflow-y": "hidden", "height": "auto" }).html(
//            '<h4>管理员审核，请等待。。。</h4>'
//        );
//        $(plugin.element).prepend($alert);
//
//        //move to top
//        $('html, body').animate({
//            scrollTop: $('body').offset().top
//        }, 500);
    }

    /*
     * insert pics to row, each row include 2 pics
     */
    function edit_insert_pics_to_form($target, add_ids) {

        var rows = Math.ceil(add_ids.length/2);
        for (var i = 0; i < rows; i++) {
            pic_row = $('<div>').attr({"class": "row pic-grid"}).appendTo($target);
            image_width = pic_row.width()/2 - 3;

                for (var j = 0; j < 2; j++) {
                    if (add_ids.length) {

                        if (j % 2) {
                            pic_grid_6 = $('<div>').attr({
                                "class": "col-xs-6 pic-item",
                                "data-left": 0
                            }).appendTo(pic_row);
                        } else {
                            pic_grid_6 = $('<div>').attr({
                                "class": "col-xs-6 pic-item",
                                "data-left": 1
                            }).appendTo(pic_row);
                        }

                        $('<img>').attr({
                            "src": add_ids.shift(),
                            "class": "img-responsive"
                        }).css({"width": image_width, "height": "100%"}).appendTo(pic_grid_6);

                        pic_a = $('<a>').attr({
                            "href": "javascript:void(0)",
                            "class": "delete-image"
                        }).css({"margin-left": image_width/2 - 7}).html('<i class="zmdi zmdi-minus-circle-outline zmdi-hc-lg"></i>').appendTo(pic_grid_6);

                }
            }

        }

    }

    /*
     * select2 render shop category term
     */
    function select_shop_category_term($select_id, opts) {

        var render_shop_term_select = function(data, select_id) {

            /*
             * select2 onchange event
             */
            select_id.on("select2:select", function (event) {
                //upload_product_content.sm_tid = event.params.data.id;
                console.log(event.params.data.id);
            });

            /*
             * get all supermarket name and id
             */
            if (data.count) {
                //console.log(data);
                select_id.select2({
                    placeholder: "选择店铺类型",
                    theme: "bootstrap",
                    language: "zh-CN",
                    data: data.lists
                });

            } else {
                select_id.select2({
                    placeholder: "选择店铺类型",
                    theme: "bootstrap",
                    language: "zh-CN",
                });
            }

        };

        json_data = {
            type: "get_shop_category_term",
            xyz: opts.xyz,
            vid: shop_category_voc
        };
        $.dld_ajax_submit("GET", json_data, $($select_id), render_shop_term_select, $select_id);

    }

}(jQuery, window, document));


