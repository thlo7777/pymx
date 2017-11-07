(function($) {


    //error display for js error
    window.onerror = function( msg, url, line) {
        //only for wechat WeixinJSBridge is not defined and url is empaty error
        if (url == '' || url == null || url === undefined) return true;
        alert(msg + " line: " + line + " url: " + url);
    }

    $(document).ready(function() {

        //main menu click event
        $('.main-menu').on('click', function(event) {

            event.preventDefault();
            href = $(this).attr('href');
            var pathname = window.location.pathname; // Returns path only
            var url = window.location.href;     // Returns full URL
            menu = url.split("?");
            menu = menu[0].split("/");
            menu_redirect = href.split("/");
            compare = false;
            $.each(menu_redirect, function(i, data) {
                if (menu[menu.length - 1] == data) {
                    compare = true;
                    return false;
                }
            });

            if (compare) {
                return ;
            }
            //href = href.substring(1, href.length);  //remove #
            window.location.replace(href);    //jump to main menu page
        });

    }); //document ready

    //for Jquery element method $().method
    $.fn.extend({

        config_wechat_location_api: function() {
            //wechat js API support
            //handle all wx api function
            wx.ready(function () {
                // 在这里调用 API
                //
                //check jsapi version
                // 1 判断当前版本是否支持指定 JS 接口，支持批量判断
                wx.checkJsApi({
                    jsApiList: [
                    'getNetworkType',
                    'getLocation',
                    ],
                    success: function (res) {

                        var errmsg = res.errMsg;
                        var regexp = /checkJsApi:ok/i;
                        var found = errmsg.match(regexp);
                        if(found === null) {
                        alert("您的微信版本太低，请下载最新版本！");
                        alert(JSON.stringify(res));
                        return false;
                        }
                    }
                });

                //detect user's wechat turn on gps location function
                wx.getLocation({
                    type:'gcj02',       //火星坐标，same geo with 高德地图
                    success: function (res) {
                        geo_location = 1;
                    },

                    cancel: function (res) {
                        //alert('用户拒绝授权获取地理位置');
                        geo_location = -1;
                    },

                    fail: function (res) {
                        //alert('用户拒绝授权获取地理位置');
                        geo_location = -1;
                    }
                });

            });//wx.ready

            //wx error handle
            wx.error(function (res) {
                alert(res.errMsg);
                return false;
            });
        },

        /*
         * sometime, amap call wechat location again will failed, so this config api doesn't like
         * config_wechat_location_api, it does not getLocation in config phase.
         * if use config_wechat_location_api, then amap call getLocation again will failed in first enter page.
         */
        config_wechat_only_location_api: function() {
            //wechat js API support
            //handle all wx api function
            wx.ready(function () {

                // 在这里调用 API
                //
                //check jsapi version
                // 1 判断当前版本是否支持指定 JS 接口，支持批量判断
                wx.checkJsApi({
                    jsApiList: [
                    'getNetworkType',
                    'getLocation',
                    ],
                    success: function (res) {

                        var errmsg = res.errMsg;
                        var regexp = /checkJsApi:ok/i;
                        var found = errmsg.match(regexp);
                        if(found === null) {
                        alert("您的微信版本太低，请下载最新版本！");
                        alert(JSON.stringify(res));
                        return false;
                        }
                    }
                });

            });//wx.ready

            //wx error handle
            wx.error(function (res) {
                alert(res.errMsg);
                return false;
            });
        },
        /*
         * wechat submit a payment interface
         */
        wechat_chooseWXPay: function(data, target, callback) {

            wx.ready(function () {
                //call wx pay
                wx.chooseWXPay({
                    timestamp: data.timeStamp,
                    nonceStr: data.nonceStr,
                    package: data.package,
                    signType: data.signType, // 注意：新版支付接口使用 MD5 加密
                    paySign: data.paySign,
                    success: function (res) {

                        var errmsg = res.errMsg;
                        var regexp = /chooseWXPay:ok/i;
                        var found = errmsg.match(regexp);
                        if(found === null) {
                            alert("支付系统返回错误: " + JSON.stringify(res));
                        }
                        // 支付成功后的回调函数
                        if (callback != null) {
                            callback(data, target, 'success', res);
                        }
                    },

                    cancel: function (res) {
                        //alert('您已取消支付' + JSON.stringify(res));
                        if (callback != null) {
                            callback(data, target, 'cancel', res);
                        }
                    },

                    fail: function (res) {
                        //alert('支付失败 ' + JSON.stringify(res));
                        if (callback != null) {
                            callback(data, target, 'fail', res);
                        }
                    }

                });
            });//wx.ready
        },
        /*
         * should wrapper wx.ready call when call any wechap api
         * for test user wechat location function
         */
        wechat_getLocation: function(gps_type, callback) {
            //wechat js API support
            wx.ready(function () {
                wx.getLocation({
                    type: gps_type,       //wgs84 or gcj02 火星坐标, 高德，百度使用gcj02, 百度需要转换from 3 to 5
                    success: function (res) {
                        //alert(JSON.stringify(res));
                        //call amap function to get address
                        if (callback != null) {
                            callback(res.longitude, res.latitude);
                        }
                        //get_curr_geo_loc_to_address(res.longitude, res.latitude, panel_id);
                        geo_location = 1;
                    },

                    cancel: function (res) {
                        //alert('用户拒绝授权获取地理位置');
                        geo_location = -1;
                        if (callback != null) {
                            callback(-1, -1);
                        }
                    },

                    fail: function (res) {
                        //alert(JSON.stringify(res));
                        geo_location = -1;
                        if (callback != null) {
                            callback(-2, -2);
                        }
                    }

                });
            });//wx.ready
        },

        amap_getaddress_bylnglat: function(longitude, latitude, target_page, callback, func_name) {
            //amap js API support
            AMap.service(["AMap.Geocoder"], function() {
                geocoder = new AMap.Geocoder({
                    radius: 100,
                    extensions: "base"
                });
                //步骤三：通过服务对应的方法回调服务返回结果，本例中通过逆地理编码方法getAddress回调结果
                geocoder.getAddress(new AMap.LngLat(longitude,latitude), function(status, result){
                    //根据服务请求状态处理返回结果
                    if(status=='error') {
                        alert(func_name + " 服务请求出错啦！");
                    }
                    if(status=='no_data') {
                        alert(func_name + "无数据返回!");
                    } else {
                        //alert(result.regeocode.formattedAddress);
                        //console.log(result);
                        return_addr = {};
                        return_addr.longitude = longitude;
                        return_addr.latitude = latitude;

                        return_addr.province = result.regeocode.addressComponent.province;
                        return_addr.city = result.regeocode.addressComponent.city;
                        return_addr.district = result.regeocode.addressComponent.district;
                        //alert(result.regeocode.addressComponent.township);
                        return_addr.township = result.regeocode.addressComponent.township;
                        return_addr.formattedAddress = result.regeocode.formattedAddress;
                        //set city to new address panel
                        callback(return_addr, target_page);
                    }
                });
            });
        },

        amap_getaddress_from_wechat_geo: function(target_page, callback, func_name) {
            wx.ready(function () {
                wx.getLocation({
                    type:'gcj02',       //火星坐标，same geo with 高德地图
                    success: function (res) {
                        //alert(JSON.stringify(res));
                        //call amap function to get address
                        AMap.service(["AMap.Geocoder"], function() {
                            geocoder = new AMap.Geocoder({
                                radius: 100,
                                extensions: "base"
                            });
                            //步骤三：通过服务对应的方法回调服务返回结果，本例中通过逆地理编码方法getAddress回调结果
                            geocoder.getAddress(new AMap.LngLat(res.longitude, res.latitude), function(status, result){
                                //根据服务请求状态处理返回结果
                                if(status=='error') {
                                    alert(func_name + " 服务请求出错啦！");
                                }
                                if(status=='no_data') {
                                    alert(func_name + "无数据返回!");
                                } else {
                                    //console.log(result);
                                    return_addr = {};
                                    return_addr.longitude = res.longitude;
                                    return_addr.latitude = res.latitude;
                                    return_addr.province = result.regeocode.addressComponent.province;
                                    return_addr.city = result.regeocode.addressComponent.city;
                                    return_addr.district = result.regeocode.addressComponent.district;
                                    return_addr.township = result.regeocode.addressComponent.township;
                                    return_addr.formattedAddress = result.regeocode.formattedAddress;

                                    callback(return_addr, target_page);
                                }
                            });
                        });
                    },

                    cancel: function (res) {
                        //alert('用户拒绝授权获取地理位置');
                        geo_location = -1;
                    },

                    fail: function (res) {
                        //alert(JSON.stringify(res));
                        geo_location = -1;
                    }

                });
            });//wx.ready

            //wx error handle
            wx.error(function (res) {
                alert(res.errMsg);
                return false;
            });
        },

        amap_getgeo_byaddress: function(user_addr, target_page, callback, func_name) {
            //amap js API support
            //加载地理编码
            AMap.service(["AMap.Geocoder"], function() {
                geocoder = new AMap.Geocoder({
                    radius: 100,
                });
                //步骤三：通过服务对应的方法回调服务返回结果，本例中通过逆地理编码方法getAddress回调结果
                geocoder.getLocation(user_addr, function(status, result) {
                    //根据服务请求状态处理返回结果
                    if(status=='error') {
                        alert(func_name + " 服务请求出错啦！");
                    }
                    if(status=='no_data') {
                        alert("搜索没有结果，请加入城市和区县再次搜索!");
                    } else {
                        //console.log(result);
                        geocode = result.geocodes;

                        return_addr = {};
                        for (var i = 0; i < geocode.length; i++) {
                            //拼接输出html
                            return_addr.longitude = geocode[i].location.getLng();
                            return_addr.latitude = geocode[i].location.getLat();
                            return_addr.province = geocode[i].addressComponent.province;
                            return_addr.city = geocode[i].addressComponent.city;
                            return_addr.district = geocode[i].addressComponent.district;
                            return_addr.township = geocode[i].addressComponent.township;
                            return_addr.formattedAddress = geocode[i].formattedAddress;
                        }
                        callback(return_addr, target_page);
                    }
                });
            });
        },

        //wechat preview image API
        wechat_previewImage: function(curImageSrc, imgArray) {
            wx.ready(function () {
                // 在这里调用 API
                wx.previewImage({
                    current: curImageSrc,
                    urls: imgArray
                });

            });//wx.ready
        },

    }); /*$.fn.extend*/

    //for Jquery $ function: $.function
    $.extend({

        set_wx_config : function() {
            var path = window.location.origin + "/wechat-mobile/jsapi-config";
            var url = window.location.href;     // Returns full URL
            //console.log(window.location);
            //1. get signpackage from server by ajax
            var request = $.ajax({
                method: "GET",
                url: path,
                data: {code: "url", name: url} 
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {
              alert('获取json失败:' + textStatus); //other error
            });

            request.always(function(data) {
            });

            request.done(function(data) {
                //2. config wx.config
                wx.config({
                    debug: false,
                    appId: data.appId,
                    nonceStr: data.nonceStr,
                    timestamp: data.timestamp,
                    signature: data.signature,
                    jsApiList: [
                        'checkJsApi',
                        'onMenuShareTimeline',
                        'onMenuShareAppMessage',
                        'onMenuShareQQ',
                        'onMenuShareWeibo',
                        'onMenuShareQZone',
                        'hideMenuItems',
                        'showMenuItems',
                        'hideAllNonBaseMenuItem',
                        'showAllNonBaseMenuItem',
                        'translateVoice',
                        'startRecord',
                        'stopRecord',
                        'onVoiceRecordEnd',
                        'playVoice',
                        'onVoicePlayEnd',
                        'pauseVoice',
                        'stopVoice',
                        'uploadVoice',
                        'downloadVoice',
                        'chooseImage',
                        'previewImage',
                        'uploadImage',
                        'downloadImage',
                        'getNetworkType',
                        'openLocation',
                        'getLocation',
                        'hideOptionMenu',
                        'showOptionMenu',
                        'closeWindow',
                        'scanQRCode',
                        'chooseWXPay',
                        'openProductSpecificView',
                        'addCard',
                        'chooseCard',
                        'openCard'
                    ]
                }); //end wx.config

            }); //end request.done

        },  //end $.set_wx_config

        //https://mp.weixin.qq.com/advanced/wiki?t=t=resource/res_main&id=mp1483682025_enmey
        //iOS WKWebview 网页开发适配指南 
        //二：页面通过LocalID预览图片 
        //变化：1.2.0以下版本的JSSDK不再支持通过使用chooseImage api返回的
        //localld以如：”img src=wxLocalResource://50114659201332”的方式预览图片。 
        //适配建议：直接将JSSDK升级为1.2.0最新版本即可帮助页面自动适配，但在部分场景下可能无效，此时可以使用
        //getLocalImgData 接口来直接获取数据。
        // upload base64 image to dld server, then use wechat_api_upload_temp_media_file to get media ID and return to 
        // mobile client. same like wx.uploadImage
        //
        dld_uploadImage: function(localId, callback) {

            base_url = 'https://dld.dreamland360.com/menu-dld-restful/dld-uploadimage-api';

            var json_data = {
                type: 'dld_uploadImage',
                localId: localId
            };

            request = $.ajax({
                url: base_url,
                method: "POST",
                data: JSON.stringify(json_data),  //for POST
            });

            request.retry({times:3, timeout:1000}).then(function(data) {

                if (typeof(data) === "undefined") {
                    alert("error: dld_uploadImage 结果未定义");
                } else {
                    if (data === null || data.length == 0) {
                        alert("error: dld_uploadImage 结果为空");
                    } else {
                        //console.log(data);
                        if (callback !== null) {
                            callback(data.data);
                        }
                    }
                }
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {

                if (jqXHR.status != "200") {
                    alert("dld_uploadImage connected server error: " + jqXHR.status + " " + jqXHR.statusText);
                }
            });
        },

        //for ios with array medias upload
        dld_uploadMedia: function(localId, object_media, index, callback) {

            base_url = 'https://dld.dreamland360.com/menu-dld-restful/dld-uploadimage-api';

            var json_data = {
                type: 'dld_uploadImage',
                localId: localId
            };

            request = $.ajax({
                url: base_url,
                method: "POST",
                data: JSON.stringify(json_data),  //for POST
            });

            request.retry({times:3, timeout:1000}).then(function(data) {

                if (typeof(data) === "undefined") {
                    alert("error: dld_uploadMedia 结果未定义");
                } else {
                    if (data === null || data.length == 0) {
                        alert("error: dld_uploadMedia 结果为空");
                    } else {
                        //console.log(data);
                        if (callback !== null) {
                            callback(data.data, index, object_media);
                        }
                    }
                }
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {

                if (jqXHR.status != "200") {
                    alert("dld_uploadMedia connected server error: " + jqXHR.status + " " + jqXHR.statusText);
                }
            });
        },

        dld_get_restful_url: function() {
            base_url = 'https://dld.dreamland360.com/menu-dld-restful/dld-restful-api';
            return base_url;
        },

        //dld ajax interface
        dld_ajax_submit: function(ajax_type, json_data, target_page, call_back, fun_name) {

            base_url = 'https://dld.dreamland360.com/menu-dld-restful/dld-restful-api';

            if (ajax_type == "GET") {
                request = $.ajax({
                    url: base_url,
                    method: "GET",
                    data: {data: json_data} //for GET
                });
            } else if (ajax_type == "POST"){
                request = $.ajax({
                    url: base_url,
                    method: "POST",
                    data: JSON.stringify(json_data),  //for POST
                });
            } else {
                return false;
            }

            request.retry({times:3, timeout:1000}).then(function(data) {

                if (typeof(data) === "undefined") {
                    alert("error: " + fun_name + " 结果未定义");
                } else {
                    if (data === null || data.length == 0) {
                        alert("error: " + fun_name + " 结果为空");
                    } else {
                        //console.log(data);
                        if (call_back !== null) {
                            call_back(data.data, target_page);
                        }
                    }
                }
            });

            request.fail(function(jqXHR, textStatus, errorThrown) {

                if (jqXHR.status != "200") {
                    alert(fun_name + " connected server error: " + jqXHR.status + " " + jqXHR.statusText);
                }
            });
        },

        //calculates the distance between two points (given the latitude/longitude of those points).
        dld_gps_distance: function(lng1, lat1, lng2, lat2) {

            var radlat1 = Math.PI * parseFloat(lat1)/180
            var radlat2 = Math.PI * parseFloat(lat2)/180
            var theta = parseFloat(lng1)-parseFloat(lng2)
            var radtheta = Math.PI * theta/180
            var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
            dist = Math.acos(dist)
            dist = dist * 180/Math.PI
            dist = dist * 60 * 1.1515

            dist = dist * 1.609344;
            return dist

        },

        //share current page to friends
        share_current_page: function(title, desc, link, imgUrl) {

            wx.ready(function () {  //should add wx ready when share, otherwise wechat js can't call share api
                //share to friend
                wx.onMenuShareAppMessage({
                    title: title, // 分享标题
                    desc: desc, // 分享描述
                    link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: imgUrl, // 分享图标
                    success: function () { 
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () { 
                        // 用户取消分享后执行的回调函数
                    }
                });

                //share to friends
                wx.onMenuShareTimeline({
                    title: title, // 分享标题
                    link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: imgUrl, // 分享图标
                    success: function () { 
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () { 
                        // 用户取消分享后执行的回调函数
                    }
                });

                //share to QQ
                wx.onMenuShareQQ({
                    title: title, // 分享标题
                    desc: desc, // 分享描述
                    link: link, // 分享链接
                    imgUrl: imgUrl, // 分享图标
                    success: function () { 
                       // 用户确认分享后执行的回调函数
                    },
                    cancel: function () { 
                       // 用户取消分享后执行的回调函数
                    }
                });

                //share to weibo
                wx.onMenuShareWeibo({
                    title: title, // 分享标题
                    desc: desc, // 分享描述
                    link: link, // 分享链接
                    imgUrl: imgUrl, // 分享图标
                    success: function () { 
                       // 用户确认分享后执行的回调函数
                    },
                    cancel: function () { 
                       // 用户取消分享后执行的回调函数
                    }
                });

                //share to QZone
                wx.onMenuShareQZone({
                    title: title, // 分享标题
                    desc: desc, // 分享描述
                    link: link, // 分享链接
                    imgUrl: imgUrl, // 分享图标
                    success: function () { 
                       // 用户确认分享后执行的回调函数
                    },
                    cancel: function () { 
                       // 用户取消分享后执行的回调函数
                    }
                });

            });//wx.ready
            
        }  //end $.share_merchandise_page

    }); /*$.extend*/

    $.set_wx_config();  /*execute wechat config*/

})(jQuery);
