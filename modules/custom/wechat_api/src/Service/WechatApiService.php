<?php

#service.wechatapi
namespace Drupal\wechat_api\Service;

// These classes are used to implement a stream wrapper class.
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get jsapi ticket');
        $token = $this->configFactory->get('dld.wxapp.config')->get('access_token');
        $token_url = t( $api_interface, array( '@ACCESS_TOKEN' => $token ) )->render();

        $result = $this->wechat_php_curl_https_get($token_url);

        if (!$result) {

            $this->logger->error(__FUNCTION__ . ": can't get result");
            return FALSE;
        }

        $json_data = json_decode($result);

        if($json_data->errcode != 0) {

            $this->logger->error(__FUNCTION__ . ": errcode @error and errmsg @errmsg",
                array(
                    '@error' => $json_data->errcode,
                    '@errmsg' => $json_data->errmsg
                )
            );
            return false;
        }

        return $json_data->ticket;
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

        if($code == '') {
            $this->logger->error($watchdog_title . ": code is empty");
            return null; 
        }

        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get oauth2 access_token');
        $appID = $this->configFactory->get('dld.wxapp.config')->get('AppID');
        $appSec = $this->configFactory->get('dld.wxapp.config')->get('AppSecret');
        $req_url = t( $api_interface, array('@APPID' => $appID, '@APPSECRET' => $appSec, '@CODE' => $code) )->render();

        $result = $this->wechat_php_curl_https_get($req_url);

        if ( !$result ) {
            $this->logger->error(__FUNCTION__ . ": can't get result");
            return null;
        }

        $json_value = json_decode($result);
        if(isset($json_value->errcode)) {

            global $base_url;
            //if 40029 happend, because browser history store last oauth2 redirect url, and contains old code. so
            //resend oauth2 redirect ulr to this page, then ok. don't forget drupal_exit :)
            if ($json_value->errcode == "40029" && preg_match('/invalid code/', $json_value->errmsg)) {
                $this->logger->info($watchdog_title . ": redirect 40029 error");

                $api_interface = $this->configFactory->get('dld.wxapp.config')->get('oauth2 redirect request');
                $redirect_40029_req_url = t( $api_interface, array(
                    '@APPID' => $appID,
                    '@URL' => $url,
                    '@SNSAPI' => $scope,
                    '@STATE' => $state))->render();

                $response = new RedirectResponse($redirect_40029_req_url);
                $response->send();
                return null;
            } elseif ($json_value->errcode == "40163" && preg_match('/code been used/', $json_value->errmsg)) {
                //if 40163 happend, same like 40029 
                //resend oauth2 redirect ulr to this page, then ok. don't forget drupal_exit :)
                $this->logger->info($watchdog_title . ": redirect 40163 error");

                $api_interface = $this->configFactory->get('dld.wxapp.config')->get('oauth2 redirect request');
                $redirect_40163_req_url = t( $api_interface, array(
                    '@APPID' => $appID,
                    '@URL' => $url,
                    '@SNSAPI' => $scope,
                    '@STATE' => $state))->render();

                $response = new RedirectResponse($redirect_40163_req_url);
                $response->send();
                return null;
            } else {
                $this->logger->error($watchdog_title . ": error code: @error and errmsg: @errmsg at @line in @filename", 
                    array(
                    '@error' => $json_value->errcode,
                    '@errmsg' => $json_value->errmsg,
                    '@line' => __LINE__,
                    '@filename' => __FILE__,
                    )
                );

                return null;
            }
        }

        if ($scope == 'snsapi_base') {
            //finished it, and only return openid
            //return value
            //{ "access_token":"ACCESS_TOKEN",    
            //"expires_in":7200,    
            //"refresh_token":"REFRESH_TOKEN",    
            //"openid":"OPENID",    
            //"scope":"SCOPE" } 
            return $json_value;
        }

        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get oauth2 user info');
        //get user info
        $req_url = t( $api_interface, array('@ACCESS_TOKEN' => $json_value->access_token,
            '@OPENID' => $json_value->openid))->render();

        $result = $this->wechat_php_curl_https_get($req_url);
        if ($result == Null) {
            $this->logger->error($watchdog_title . ": error: get user info return null in @line line:@filename", 
                array(
                '@line' => __LINE__,
                '@filename' => __FILE__,
                )
            );
            return null;
        }

        $json_value = json_decode($result);
        if(isset($json_value->errcode)) {
            $this->logger->error($watchdog_title . ": can not get wechat user info code: @error and errmsg: @errmsg at @line in @filename", 
                array(
                '@error' => $json_value->errcode,
                '@errmsg' => $json_value->errmsg,
                '@line' => __LINE__,
                '@filename' => __FILE__,
                )
            );
            return null;
        }

    //reuturn value

    //{"openid":" OPENID",  
    // "nickname": NICKNAME,   
    // "sex":"1",   
    // "province":"PROVINCE"   
    // "city":"CITY",   
    // "country":"COUNTRY",    
    // "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ
    //4eMsv84eavHiaiceqxibJxCfHe/46",  
    //"privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],    
    // "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL" 
    //} 

        return $json_value;
    }

    /**
     * get temp media from wechat
     **/
    public function wechat_api_get_temp_media($save_path, $media_id) {
        $file_name = null;

        $api_interface = $this->configFactory->get('dld.wxapp.config')->get('get temp media id');
        $token = $this->configFactory->get('dld.wxapp.config')->get('access_token');
        $token_url = t( $api_interface, array( '@ACCESS_TOKEN' => $token, '@MEDIA_ID' => $media_id) )->render();

        $result = $this->wechat_php_curl_https_get($token_url, true);

        if (!$result) {
            $this->logger->error(__FUNCTION__ . ": can't get result");
            return $file_name;
        }

        list($header, $body) = explode("\r\n\r\n", $result, 2);

        preg_match('@Content-disposition[ \t]*:[ \t]*attachment[ \t]*;[ \t]*filename[ \t]*=[ \t]*\".*\"@i', $header, $matches);
        preg_match('@\".*\"@i', $matches[0], $filename);
        $filename = trim($filename[0], '"');

       //watchdog('wechat_api_get_temp_media', '$filename = <pre>@data</pre>', array('@data' => print_r($filename, TRUE)));

        $destination = $save_path . "/" . $filename;
        $filename = file_unmanaged_save_data($body, $destination, FILE_EXISTS_RENAME);

        return $filename;



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
        //$this->logger->notice(__FUNCTION__ . ": openID @openid subscribe", array('@openid' => $openID));
        if(!empty($recvMsg['EventKey'])) {  //通过二维码扫码加关注
            /**
             * User subscrib by scan bar code, eventkey is qrscene_ plus scene number
             * <xml><ToUserName><![CDATA[toUser]]></ToUserName>
             * <FromUserName><![CDATA[FromUser]]></FromUserName>
             * <CreateTime>123456789</CreateTime>
             * <MsgType><![CDATA[event]]></MsgType>
             * <Event><![CDATA[subscribe]]></Event>
             * <EventKey><![CDATA[qrscene_123123]]></EventKey>
             * <Ticket><![CDATA[TICKET]]></Ticket>
             * </xml>
             */
            $this->wechat_subscribe_event($recvMsg, $recvMsg['EventKey']); 
            $this->logger->notice(__FUNCTION__ . ": subscribe EventKey @data", array('@data' => $recvMsg['EventKey']));
        } else {    //通过微信关注
            /**
             * <xml>
             * <ToUserName><![CDATA[toUser]]></ToUserName>
             * <FromUserName><![CDATA[FromUser]]></FromUserName>
             * <CreateTime>123456789</CreateTime>
             * <MsgType><![CDATA[event]]></MsgType>
             * <Event><![CDATA[subscribe]]></Event>
             * </xml>
             */
            $this->wechat_subscribe_event($recvMsg); 
        }

        return 'success';
    }

    public function recv_event_user_unsubscribe($recvMsg) {

        $openID = $recvMsg['FromUserName'];
        $this->logger->notice(__FUNCTION__ . ": openID @openid unsubscribe", array('@openid' => $openID));

        $uid = $this->check_userid_exists($openID);
        if ( $uid ) {
            //set unsubscribe time and unsubscribe status
            //$this->logger->notice(__FUNCTION__ . ": uid=<pre>@data</pre>", array('@data' => print_r($uid, true)) );
            $user = User::load($uid);
            $user->field_wechat_subscribe = false;
            $user->field_unsubscribe_time = time();
            $user->save();
        }

        return 'success';
    }

    public function recv_event_view_msg_callback($recvMsg) {
        $openID = $recvMsg['FromUserName'];
        //$this->logger->notice(__FUNCTION__ . ": openID @openid unsubscribe", array('@openid' => $openID));

        return 'success';
    }

    public function recv_user_text($recvMsg) {
        $openID = $recvMsg['FromUserName'];
        $content = $recvMsg['Content'];
        //$this->logger->notice(__FUNCTION__ . ": user text @data", array('@data' => $content));

        return 'success';
    }


    protected function wechat_subscribe_event($recvMsg, $scene_str = '') {
        $openID = $recvMsg['FromUserName'];
        $uid = $this->check_userid_exists($openID);
        if ( !$uid ) {
            //create new user 
            $this->create_new_wechat_user($recvMsg, $openID);

            if ( !empty($scene_str) ) { //handle qrcode scene event
            }
        } else {
            //update user field_wechat_subscribe status
            //$this->logger->notice(__FUNCTION__ . ": uid=<pre>@data</pre>", array('@data' => print_r($uid, true)) );
            $user = User::load($uid);
            $user->field_wechat_subscribe = TRUE;
            $user->save();
        }

    }

    public function get_userid_bymd5($md5openid) {
        $ids = \Drupal::entityQuery('user')
                ->condition('field_wechat_openid', $openid)
                ->execute();
        return reset($ids); //return first id or false if $ids is empty
    }

    public function check_userid_exists($openid) {
        $ids = \Drupal::entityQuery('user')
                ->condition('field_wechat_openid', $openid)
                ->execute();
        return reset($ids); //return first id or false if $ids is empty
    }

    protected function create_new_wechat_user($recvMsg, $openID) {
        $user = User::create();

        $md5openID = md5($openID);

        $user->setPassword(strrev($md5openID));   //reverse md5 openid
        $user->enforceIsNew();
        $user->setEmail('user@gmail.com');
        //This username must be unique and accept only a-Z,0-9, - _ @ .
        $user->setUsername('tmp_' . $md5openID);
        $user->field_subscribe_time = time();
        $user->field_wechat_openid = $openID;
        $user->field_openid_md5 = $md5openID;
        $user->field_wechat_subscribe = TRUE;

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user->set("langcode", $language);
        $user->set("preferred_langcode", $language);
        $user->activate();
        $user->save();

    }

}
