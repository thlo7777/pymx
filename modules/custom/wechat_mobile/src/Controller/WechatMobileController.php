<?php

namespace Drupal\wechat_mobile\Controller;

use Drupal\wechat_api\Service\WechatApiService;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Renderer;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides route responses for the container info pages.
 */
class WechatMobileController extends ControllerBase implements ContainerInjectionInterface {

    /**
     * The EntityDisplayRepository service.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * @var \Drupal\Core\Entity\EntityFieldManagerInterface
     */
    protected $entityFieldManager;

    /**
     * @var \Drupal\Core\File\FileSystem
     **/
    protected $fileSystem;
    protected $logger;
    protected $wechatConfig;

    protected $renderer;

    protected $css;
    protected $js;

    protected $wechat_api;

    public function __construct(Renderer $render,
        WechatApiService $service,
        EntityTypeManagerInterface $entity_type_manager,
        EntityFieldManagerInterface $entity_field_manager,
        FileSystem $fileSystem ) {

        $this->logger = $this->getLogger('wechat mobile');
        $this->wechatConfig = $this->config('dld.wxapp.config');
        $this->wechat_api = $service;
        $this->renderer = $render;
        $this->entityTypeManager = $entity_type_manager;
        $this->entityFieldManager = $entity_field_manager;
        $this->fileSystem = $fileSystem;

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
            $container->get('service.wechatapi'),
            $container->get('entity_type.manager'),
            $container->get('entity_field.manager'),
            $container->get('file_system')
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

    //path: wechat-mobile/hotel-create-page
    public function HotelCreatePage(Request $request) {
        global $base_url;

        $page = array();

        $state = $request->query->get('state');
        switch ($state) {
            case '':
            case '100':
                // normally access from shopping home page and user id can be getted
                break;

            default:
                $page['error_info'] = '页面state: ' . $state . ' 错误';
                return $page;
        }
       //get page code
        $code = $request->query->get('code');

        if($code != '') {

            $curr_url = $request->getRequestUri();
            $page_path = parse_url($curr_url);

            $json = $this->wechat_api->wechat_api_oauth2_get_accss_token(
                $code, 'snsapi_userinfo', $state, $base_url . $page_path['path'], $page_path['path']
            );

            if ($json != null) {
                //get Openid by code
                
                $page['js_var']['xyz'] = md5($json->openid);
                $page['js_var']['abc'] = base64_encode("tmp_" . md5($json->openid) . ":" . strrev(md5($json->openid)));
                //$this->logger->notice(__FUNCTION__ . 'query <pre>@data</pre>', array('@data' => print_r($page['js_var'], true)) );
            }
        }

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

        $page['body_id'] = 'hotel-create-page';
        $page['js_var']['rest_api'] = $base_url . '/mxw/rest?_format=json';
        $page['js_var']['entity']['entity_type'] = 'hotel_main_page_add';

        $element = $this->entityFieldManager->getFieldDefinitions('node', 'hotel_main_page');
        foreach ($element as $key => $item) {
            if (preg_match('/^field_/', $key)) {
                $page['js_var']['entity'][$key]['field_type'] = $item->getType();
                $page['js_var']['entity'][$key]['label'] = $item->getLabel();

                if ($item->getType() == "entity_reference") {
                    $ref_bundle = reset($item->getSettings()['handler_settings']['target_bundles']);
                    $ref_element = $this->entityFieldManager->getFieldDefinitions('node', $ref_bundle);
                    foreach ($ref_element as $subkey => $subfield) {
                        if (preg_match('/^field_/', $subkey)) {
                            $page['js_var']['entity'][$key]['ref_field'][$subkey]['lable'] = $subfield->getLabel();
                            $page['js_var']['entity'][$key]['ref_field'][$subkey]['field_type'] = $subfield->getType();
                        }
                    }
                }
            }

        }

        //$this->logger->notice(__FUNCTION__ . 'field name <pre>@data</pre>', array('@data' => print_r($farray, true)) );

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "建立客栈主页",
            '#page' => $page
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

        $page['body_id'] = 'hotel-main-page';
        $page['js_var']['rest_api'] = $base_url;

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "客栈主页",
            '#page' => $page
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

    /*
     * ID pictures, large picture, height 768 1024
     */
    public function set_pics_field_pictures(&$field, $media_ids, $field_name, $image_path) {

        //download and format pictures
        $image_file = array();
        //create large image and small image
        foreach($media_ids as $delta => $media_id) {
            $this->download_media_id($image_file, $media_id, $image_path);
        }

        if (!empty($image_file)) {
            //get caridtia
            $field_info  = field_info_field($field_name);
            $cardinality = $field_info['cardinality'];

            foreach ($image_file as $file_path) {

                $file_temp = file_get_contents($file_path);
                $scheme = variable_get('file_default_scheme', 'public') . '://';
                $split_str = explode("/", $file_path);
                $fm = $scheme . SHOP_CONTENT_PATH . end($split_str);
                $file = file_save_data($file_temp, $fm, FILE_EXISTS_REPLACE);

                if ($cardinality > 1) {
                    $field[] = array('fid' => $file->fid);
                } else {
                    $field->set(array('fid' => $file->fid));
                }

            }

        } else {
            return false;
        }

    return true;


    }

    //download media id from wechat server
    protected function download_media_id(&$image_file, $media_id, $image_path) {

        $scheme = file_default_scheme() . '://';
        $save_path = $this->fileSystem->realpath($scheme . $image_path);

        $file_name = $this->wechat_api->wechat_api_get_temp_media($save_path, $media_id);

        $image_file[] = $file_name;

        return null;

    }

} /*end class*/

