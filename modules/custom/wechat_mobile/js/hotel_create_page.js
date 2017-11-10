(function($) {

    var hotel_info = {
        latitude: 0,
        longitude: 0,
        address: "",
        name: "",
    };

    console.log(jsObj.entity);
//
//    json_data = {
//        type: "get_all_user_profile_address",
//        xyz: 'xyz',
//        ids: 'fuck'
//    };
//
//    callback = function(data) {
//        console.log(data);
//    };
//    $.wechat_rest_ajax('GET', jsObj.rest_api, jsObj.abc, json_data, callback);

    $.fn.insert_footer = function() {

        $footer = $('<footer>').attr({'class': "mxw-footer navbar-fixed-bottom"}).insertAfter(this);
        $container = $('<div>').attr({'class': "container-fluid"}).appendTo($footer);
        $row = $('<div>').attr({'class': "row"}).appendTo($container);

        $col = $('<div>').attr({'class': "col-xs-3 col-xs-offset-3 text-center"}).appendTo($row);
        $a = $('<a>').attr({
            'class': "btn btn-success prev-page",
            'href': "#map-section"
        }).html(
             '<i class="zmdi zmdi-chevron-left zmdi-hc-fw"></i>上一页'
        ).appendTo($col);

        $col = $('<div>').attr({'class': "col-xs-3 text-center"}).appendTo($row);
        $a = $('<a>').attr({
            'class': "btn btn-success next-page",
            'href': "#hotel1-section"
        }).html(
             '下一页<i class="zmdi zmdi-chevron-right zmdi-hc-fw"></i>'
        ).appendTo($col);

    };

    $.fn.insert_head = function() {
        $navbar = $('<nav>').attr({'class': "nav-hide nav navbar navbar-default navbar-fixed-top"}).insertBefore(this);
        $ul = $('<ul>').attr({'class': "nav navbar-nav"}).appendTo($navbar);
        $li = $('<li>').attr({'class': "active"}).appendTo($ul);
        $a = $('<a>').attr({'href': "#hotelmap-section"}).appendTo($li);
        $li = $('<li>').appendTo($ul);
        $a = $('<a>').attr({'href': "#hotel1-section"}).appendTo($li);
        $li = $('<li>').appendTo($ul);
        $a = $('<a>').attr({'href': "#hotel2-section"}).appendTo($li);
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

    //form validation
    $.fn.form_validation = function(plugin) {

        opts = plugin.options;

        $input = $('#input-hotel-name');
        if (! $input.val().length) {
            $.alert('请输入酒店名称');
            $input.focus();
            return false;
        } else {
            jsObj.entity.title = $input.val();
            jsObj.entity.field_hotel_address.value = $('#input-hotel-address').val();
        }

        img_num = $('.form-group.pics-form').find('.row.pic-grid .pic-item img');
        if (! img_num.length) {
            $('.input-picture').focus();
            $.alert('请添加酒店门脸照片');
            return false;
        } else {
            opts.hotel_info.pics_form = {};
            opts.hotel_info.pics_form.localids = [];
            img_num.each(function() {
                opts.hotel_info.pics_form.localids.push($(this).attr('src'));
            });
        }

        return true;
    };

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

    $.fn.submit_register_form = function(plugin, buttonClass) {
        var opts = plugin.options;

        $(buttonClass).prop("disabled", true); //disable button before send data to server

        //show pregressbar modal
        var proBar = $('.pymxw-page').progressbar().openModal();
        proBar.setProgress({width: "0px", per: "0%"});

        //upload shop license pics and get media ids
        var pics_form = upload_deferred('pics-form', opts.hotel_info.pics_form.localids);
        $.when(pics_form).done(function(var1) {
            //console.log(var1);
            if (var1.length){
                opts.hotel_info.hotele_view_image = var1;
                jsObj.entity.field_hotel_view_image.value = var1;
            }

            proBar.setProgress({width: "100%", per: "100%"});
            //call ajax send form to server
//            var json_data = {
//                type: jsObj.entity_type,
//                apply_form: opts.hotel_info
//            };
            jsObj.entity.field_gps_latitude.value = opts.hotel_info.latitude;
            jsObj.entity.field_gps_longitude.value = opts.hotel_info.longitude;
            var form_submit_finish = function(data, that) {
                //clear submit button, move to top and aler info waiting audit
                //window.location.href = opts.manage_url;
                //clear_submit_button(that, buttonClass);
                //go to generate shop page
                //new_url = gen_shopqr_url.replace(/%/, data.shop_nid);

                proBar.closeModal();
                //window.location.href = new_url;    //go to shop qr page
            };

            $.wechat_rest_ajax('POST', jsObj.rest_api, jsObj.abc, jsObj, form_submit_finish, null, 'form_submit_finish');

        }).fail(function(var1) {
            alert(var1);
            console.log(var1);
        });

    };

    $(document).config_wechat_only_location_api();

    $('.mxw-content.container').insert_head();
    $('.mxw-content.container').insert_footer();

    $section = $('<div>').attr({'id': "hotelmap-section", 'class': "hotel-section"}).appendTo($('.mxw-content.container'));
    //load amap for create hotel
    $section.load_amap({
        hotel_info: hotel_info,
        address: "#input-hotel-address"
    }).load_map_init();


    $section = $('<div>').attr({'id': "hotel1-section", 'class': "hotel-section"}).appendTo($('.mxw-content.container'));
    //insert hotel address and hotel name
    $form = $('<form>').attr({
        'class': "form-horizontal"
    }).appendTo($section);
    $fieldset = $('<fieldset>').appendTo($form);
    //hotel address input
    $form_group = $('<div>').attr({'class': 'form-group'}).html(
        '<label for="input-hotel-address" class="col-lg-2 control-label">酒店地址</label>' +
        '<div class="col-lg-10">' +
            '<input type="text" class="form-control" id="input-hotel-address">' +
        '</div>'
    ).appendTo($fieldset);

    //hotel name input
    $form_group = $('<div>').attr({'class': 'form-group'}).html(
        '<label for="input-hotel-name" class="col-lg-2 control-label">酒店名称</label>' +
        '<div class="col-lg-10">' +
            '<input type="text" class="form-control" id="input-hotel-name" placeholder="酒店名称">' +
        '</div>'
    ).appendTo($fieldset);

    //hotel face photo
    $form_group = $('<div>').attr({'class': 'form-group pics-form'}).html(
        '<label for="input-picture" class="col-lg-2 control-label">酒店门脸照片</label>' +
        '<div class="col-lg-10">' +
            '<a href="javascript:void(0)" class="btn btn-primary btn-sm input-picture">' +
            '<i class="zmdi zmdi-collection-item-1 zmdi-hc-lg zmdi-hc-fw"></i>' +
            '添加图片</a>' +
        '</div>'
    ).appendTo($fieldset);


//    $section.createForm({
//        formID: "hotel1-form",
//        fields: [
//            {
//            name: 'hotel-address',
//            label: '酒店地址',
//            type: 'text',
//            id: 'input-hotel-address',
//            }, {
//            name: 'hotel-name',
//            label: '酒店名称',
//            type: 'text',
//            id: 'input-hotel-name',
//            placeholder: "酒店名称"
//            }, {
//            name: 'pic-form',
//            label: '酒店门脸照片',
//            type: 'anchorpicbtn',
//            fgClass: 'pics-form',
//            alabel: '添加图片',
//            iclass: 'input-picture',
//            iclass: 'zmdi zmdi-collection-item-1 zmdi-hc-lg zmdi-hc-fw'
//            }
//        ]
//    });
    $(document).on('click', '.input-picture', function(event) {
        event.preventDefault();
        $('.form-group.pics-form').input_pics_button(1);
    });

    $section = $('<div>').attr({'id': "hotel2-section", 'class': "hotel-section"}).appendTo($('.mxw-content.container'));

    //submit register page
    $form_group = $('<div>').attr({'class': 'form-group'}).html(
        '<div class="col-lg-12 text-center">' +
            '<button type="submit" class="btn btn-primary submit-register-form">提交酒店信息</button>' +
        '</div>'
    ).insertAfter($section);
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
    

    $('.prev-page').on('click', function(e){
        var prev = $('.navbar ul li.active').prev().find('a').attr('href');
        if (prev !== undefined) {
            $('html, body').animate({
                scrollTop: $(prev).offset().top
            }, 500);
        }
    });

    $('.next-page').on('click', function(e){
        var next = $('.navbar ul li.active').next().find('a').attr('href');
        if (next !== undefined) {
            $('html, body').animate({
                scrollTop: $(next).offset().top
            }, 500);
        }
        console.log(hotel_info);
    });

    $('body').scrollspy({ 
            target: '.navbar'
    })

    $('<button>').attr({'class': "btn btn-success copy-btn"}).html('拷贝').appendTo($fieldset);
    $('.copy-btn').on('click', function(e) {
        e.preventDefault();
        $(this).copyToClipboard('平遥美途驿栈');
    });

    $.fn.copyToClipboard = function (string) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(string).select();
        document.execCommand("copy");
        $temp.remove();
    }


})(jQuery);
