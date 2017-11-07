<?php

namespace Drupal\wechat_mobile\Controller;

use Drupal\wechat_api\Service\WechatApiService;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Renderer;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides route responses for the container info pages.
 */
class WechatMobileController extends ControllerBase implements ContainerInjectionInterface {

    protected $logger;
    protected $wechatConfig;

    protected $renderer;

    protected $css;
    protected $js;

    protected $wechat_api;

    public function __construct(Renderer $render, WechatApiService $service) {

        $this->logger = $this->getLogger('wechat mobile');
        $this->wechatConfig = $this->config('dld.wxapp.config');
        $this->wechat_api = $service;
        $this->renderer = $render;

        global $base_url;
        $this->css = array(
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/bootstrap.min.css',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/material-design-iconic-font.min.css',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/jquery-confirm.min.css',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/loading_gif.css',
        ); 

        $this->js = array(
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/jquery-3.2.1.min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/bootstrap.min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/jquery.ajax-retry.min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/jquery-confirm.min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/plugin/loading_gif.js'
        );
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('renderer'),
            $container->get('service.wechatapi')
        );
    }

    /**
     * build simple test theme page
     * path /wechat-mobile/create-leaf
     **/
    public function CreateLeafPage(Request $request) {
        $values = $request->query->all();
        \Drupal::logger('GetTestThemePage')->notice( 'request: <pre>@data</pre>', array('@data' => print_r($values, true)) );

        //$twigFilePath = drupal_get_path('module', 'wechat_mobile') . '/templates/wechat-mobile-gen-tpl.html.twig';
        //$template = $this->twig->loadTemplate($twigFilePath);

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "world",
            '#page' => array('body_id' => 'create-leaf-page')
        );
        $html = $this->renderer->renderRoot($build);
        $response = new Response();
        $response->setContent($html);

        return $response;
    }

    public function HotelCreatePage(Request $request) {
        global $base_url;

        array_push($this->css,
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/hotel_create_page.css',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/progressBar.css'
        );

        array_push($this->js,
            AMAP_JS_REST_API . '&key=' . AMAP_JS_KEY,   //amap javascript api
            AMAP_UI_COMPONENTS, //amap UI components
            'https://res.wx.qq.com/open/js/jweixin-1.2.0.js',    //wechat javascript api'
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/mobile_plugin_api.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/plugin/load_amap.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/plugin/progressBar.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/underscore-min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/bootstrap_formform.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/plugin/form_tpl.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/hotel_create_page.js'
        );

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "建立客栈主页",
            '#page' => array('body_id' => 'hotel-create-page')
        );

        $html = $this->renderer->renderRoot($build);
        $response = new Response();
        $response->setContent($html);

        return $response;
    }

    public function HotelMainPage(Request $request, $nid) {
        global $base_url;
        $this->logger->notice(__FUNCTION__ . 'nid: <pre>@data</pre>', array('@data' => print_r($nid, true)) );

        $this->css[] = 
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/hotel_main_page.css';

        array_push($this->js,
            AMAP_JS_REST_API . '&key=' . AMAP_JS_KEY,   //amap javascript api
            'https://res.wx.qq.com/open/js/jweixin-1.2.0.js',    //wechat javascript api'
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/mobile_plugin_api.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/hotel_main_page.js'
        );

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "客栈主页",
            '#page' => array('body_id' => 'hotel-main-page')
        );
        $html = $this->renderer->renderRoot($build);
        $response = new Response();
        $response->setContent($html);

        return $response;
    }

    public function JSApiConfig(Request $request) {
        //$values = $request->query->all();
        //$this->logger->notice( __FUNCTION__ . ' request: <pre>@data</pre>', array('@data' => print_r($values, true)) );
        $code = $request->query->get('code');
        $url = $request->query->get('name');
        if ($code == 'url' && $url != '') {
            $response = $this->jsapi_getSignPackage($url);
            return new JsonResponse( $response );
        } else {
            return array();
        }
    }

    protected function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //jspai sign package
    protected function jsapi_getSignPackage($url) {

        if($url == '') {
            return array();
        }

        $appId = $this->wechatConfig->get('AppID');
        $jsapiTicket = $this->wechatConfig->get('jsapi_ticket');
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "signature" => $signature,
        );

        return $signPackage;
    }

} /*end class*/

