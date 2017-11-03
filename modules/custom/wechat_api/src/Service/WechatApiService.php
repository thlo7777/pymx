<?php

#service.wechatapi
namespace Drupal\wechat_api\Service;

// These classes are used to implement a stream wrapper class.
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * wechat api service for all wechat api interface and include curl http interface
 **/
class WechatApiService {

    protected $logger;
    protected $configFactory;

    public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactory $loggerFactory) {
        $this->configFactory = $config_factory;
        $this->logger = $loggerFactory->get('WechatApiService');

    }

    /**
     * Implment php5 curl api to get https url
     */
    public function wechat_php_curl_https_get($url, $h = false) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Edit: prior variable $postFields should be $postfields;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($h) {
            //include header information
            curl_setopt($ch, CURLOPT_HEADER, true); 
        }

        $result = curl_exec($ch);
        if(curl_errno($ch)) {
            curl_close($ch);
            return NULL;
        }

        if($h) {
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
                return $result;
            }else{
                return NULL;
            }
        }

        curl_close($ch);
        return $result;
    }

    /**
     * Implment php5 curl api to post data to https url 
     **/
    public function wechat_php_curl_https_post($url, $postfields, $ct = '') {

        //$postfields = array('field1'=>'value1', 'field2'=>'value2');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        // Edit: prior variable $postFields should be $postfields;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($ct == 'json') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                    'Content-Length: ' . strlen($postfields))); 
        }

        //Keep this code to remind me use it for some chinese character just in case
        if($ct == 'utf-8') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=utf-8', 
                    'Content-Length: ' . strlen($postfields))); 
        }

        $result = curl_exec($ch);

        if(curl_errno($ch)) {
            curl_close($ch);
            return NULL;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * Returns the name of the stream wrapper for use in the UI.
     * @return string
     *   The stream wrapper name.
     **/
    public function getName() {
        $token = $this->configFactory->get('dld.wxapp.config')->get('AppID');
        //\Drupal::logger('WechatApiService')->critical('logger ok');
        $this->logger->notice('notice logger');
        return $token;
    }

    public function get_jsapi_ticket() {
    }

    /**
     * public get wechat general access token
     **/
    public function get_access_token() {
  
        $token = $this->configFactory->get('dld.wxapp.config')->get('get access token');
        $AppID = $this->configFactory->get('dld.wxapp.config')->get('AppID');
        $AppSecret = $this->configFactory->get('dld.wxapp.config')->get('AppSecret');
        $token_url = t( $token, array( '@APPID' => $AppID, '@APPSECRET' => $AppSecret) )->render();

        $result = $this->wechat_php_curl_https_get($token_url);

        if (!$result) {

            $this->logger->notice(__FUNCTION__ . ": can't get result");
            return FALSE;
        }

        $json_data = json_decode($result);
        if ( isset($json_data->errcode) ) {
            $this->logger->error(__FUNCTION__ . ": errcode @error and errmsg @errmsg",
                array(
                    '@error' => $json_data->errcode,
                    '@errmsg' => $json_data->errmsg
                )
            );
            return FALSE;
        }

        return $json_data->access_token;
    }

    /**
     * get wechat ip address
     **/
    public function get_ip_server() {
        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get ip server');
        $token = $this->configFactory->get('dld.wxapp.config')->get('access_token');
        $token_url = t( $api_interface, array( '@ACCESS_TOKEN' => $token ) )->render();

        $result = $this->wechat_php_curl_https_get($token_url);

        if (!$result) {

            $this->logger->notice(__FUNCTION__ . ": can't get result");
            return FALSE;
        }

        $json_data = json_decode($result);
        if ( isset($json_data->errcode) ) {
            $this->logger->error(__FUNCTION__ . ": errcode @error and errmsg @errmsg",
                array(
                    '@error' => $json_data->errcode,
                    '@errmsg' => $json_data->errmsg
                )
            );
            return FALSE;
        }

        return $json_data;
    }

    /**
     * create custom menu
     **/
    public function create_custom_menu($menu) {
        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('create menu');
        $token = $this->configFactory->get('dld.wxapp.config')->get('access_token');
        $token_url = t( $api_interface, array( '@ACCESS_TOKEN' => $token ) )->render();

        $result = $this->wechat_php_curl_https_post($token_url, $menu);

        if (!$result) {
            $this->logger->notice(__FUNCTION__ . ": can't get result");
            return FALSE;
        }

        $json_data = json_decode($result);
        if ( isset($json_data->errcode) && $json_data->errcode != 0 ) {
            $this->logger->error(__FUNCTION__ . ": errcode @error and errmsg @errmsg",
                array(
                    '@error' => $json_data->errcode,
                    '@errmsg' => $json_data->errmsg
                )
            );
            return FALSE;
        }

        return $json_data;
    }

    /**
     * get custom menu
     **/
    public function get_custom_menu() {
        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get menu');
        $token = $this->configFactory->get('dld.wxapp.config')->get('access_token');
        $token_url = t( $api_interface, array( '@ACCESS_TOKEN' => $token ) )->render();

        $result = $this->wechat_php_curl_https_get($token_url);

        if (!$result) {
            $this->logger->notice(__FUNCTION__ . ": can't get result");
            return FALSE;
        }

        return $result;
    }
    /**
     *
     *  get oauth2 access_token by redirect code, and finished if scope is snsapi_userinfo or snsapi_base
     *  paramter:
     *  $code: * 用户同意授权后
     *  如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE
     *  $scope: snsapi_userinfo / snsapi_base
     *  $state: any
     *  $url: redirect url when error
     *  $watchdog_title: page name
     */
    public function wechat_api_oauth2_get_accss_token($code, $scope = 'snsapi_base', $state, $url, $watchdog_title) {
    }

    /**
     * receive wechat server message
     **/
    public static $recv_msg_array_func = [
        'event' => [
            'subscribe' => 'recv_event_user_subscribe',
            'unsubscribe' => 'recv_event_user_unsubscribe',
            'VIEW' =>'recv_event_view_msg_callback'
        ],
        'text' => 'recv_user_text',
        'image' => 'recv_user_image',
    ];

    public function recv_event_user_subscribe($recvMsg) {
        $openID = $recvMsg['FromUserName'];
        $this->logger->notice(__FUNCTION__ . ": openID @openid subscribe", array('@openid' => $openID));

        return 'success';
    }

    public function recv_event_user_unsubscribe($recvMsg) {

        $openID = $recvMsg['FromUserName'];
        $this->logger->notice(__FUNCTION__ . ": openID @openid unsubscribe", array('@openid' => $openID));

        return 'success';
    }

    public function recv_event_view_msg_callback($recvMsg) {
        $openID = $recvMsg['FromUserName'];
        $this->logger->notice(__FUNCTION__ . ": openID @openid unsubscribe", array('@openid' => $openID));

        return 'success';
    }

    public function recv_user_text($recvMsg) {
        $openID = $recvMsg['FromUserName'];
        $content = $recvMsg['Content'];
        //$this->logger->notice(__FUNCTION__ . ": user text @data", array('@data' => $content));

        return 'success';
    }

}
