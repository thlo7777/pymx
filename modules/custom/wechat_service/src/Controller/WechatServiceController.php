<?php

namespace Drupal\wechat_service\Controller;

use Drupal\wechat_api\Service\WechatApiService;
use Drupal\wechat_api\Service\WXBizMsgCrypt;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * wechat service Controller
 */
class WechatServiceController extends ControllerBase {

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    protected $wechat_api;
    protected $wechatConfig;
    protected $wxMsgCrypt;

    /**
     * private data
     **/
    private $msg_signature;
    private $timestamp;
    private $nonce;

    private $access_token;

    /**
     * {@inheritdoc} 
     **/
    public function __construct(WechatApiService $service, WXBizMsgCrypt $msgCrypt) {

        $this->logger = $this->getLogger('wechat access enter');
        $this->wechatConfig = $this->config('dld.wxapp.config');
        $this->wxMsgCrypt = $msgCrypt;
        $this->wechat_api = $service;

    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('service.wechatapi'),
            $container->get('service.wechatmsgcrypt')
        );
    }

    /**
     * wechat access enter
     **/
    public function access() {

        $echostr = filter_input(INPUT_GET, 'echostr', FILTER_SANITIZE_SPECIAL_CHARS);
        //Retrun echostr for wechat server certification
        if(isset($echostr) || !is_null($echostr) || !empty($echostr)){
            echo $echostr;

            //$this->logger->notice('@data', array('@data' => $echostr));
            return new Response(null, 200, array());    //return empty to wechat
        }

        //$this->logger->notice('appID @data', array('@data' => $this->wechatConfig->get('AppID')));

        if( $this->checkSignature() ) {
            $xmldata = file_get_contents("php://input"); //get xml data
            $result = $this->routing_wechat_message($xmldata);  // handle received message 

            echo $result;

        }else{
            $this->logger->error('check signature error');
            echo '';
        }

        return new Response(null, 200, array());    //return empty to wechat
    }


    /**
     * Check signature for legal user
     * signature、timestamp、nonce、echostr
     * 1. 将token、timestamp、nonce三个参数进行字典序排序
     * 2. 将三个参数字符串拼接成一个字符串进行sha1加密
     * 3. 开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
     **/
    public function checkSignature() {
        $this->msg_signature = filter_input(INPUT_GET, 'msg_signature', FILTER_SANITIZE_SPECIAL_CHARS);
        $signature = filter_input(INPUT_GET, 'signature', FILTER_SANITIZE_SPECIAL_CHARS);
        $this->timestamp = filter_input(INPUT_GET, 'timestamp', FILTER_SANITIZE_SPECIAL_CHARS);
        $this->nonce = filter_input(INPUT_GET, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS);

        $token = $this->wechatConfig->get('WX Token');

        $tmpArr = array($token, $this->timestamp, $this->nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * dispatch every message to web service
     **/
    public function routing_wechat_message($xmldata) {

        $result = 'success';

        //set default WX token and aeskey and appid
        $token = $this->wechatConfig->get('WX Token');
        $encodingAesKey = $this->wechatConfig->get('EncodingAESKey');
        $appId = $this->wechatConfig->get('AppID');
        $this->wxMsgCrypt->SetParameters($token, $encodingAesKey, $appId);

        //decrypte message
        $recv_msg = '';
        $errCode = $this->wxMsgCrypt->decryptMsg($this->msg_signature, $this->timestamp, $this->nonce, $xmldata, $recv_msg);
        if ($errCode == 0) {

            libxml_disable_entity_loader(true);
            $postArray = $this->xml2array(simplexml_load_string($recv_msg, 'SimpleXMLElement', LIBXML_NOCDATA));
//            $this->logger->notice(__FUNCTION__ . ': postArray <pre>@data</pre>', array('@data' => print_r($postArray, TRUE)));

            $MsgType = $postArray['MsgType'];

            //dispatch message
            if ($MsgType == 'event') {
                $event = $postArray['Event'];
                $cb_func = WechatApiService::$recv_msg_array_func[$MsgType][$event];
                $result = $this->wechat_api->$cb_func($postArray);
            } else {
                $cb_func = WechatApiService::$recv_msg_array_func[$MsgType];
                $result = $this->wechat_api->$cb_func($postArray);
            }
        } else {
            $this->logger->error(__FUNCTION__ . ' errcode: @data', array('@data' => $errCode, TRUE));
        }

        //watchdog('wechat recv message', 'postArray <pre>@print_r</pre>', array('@print_r' => print_r($postArray, TRUE)));
//
//        //$MsgType = (string)$xmldata->MsgType;
//        $MsgType = $postArray['MsgType'];
//
//        $recv_msg = variable_get('wechat_recv_msg');
//
//        if($MsgType == 'event'){
//        //$event = (string)$xmldata->Event;
//        $event = $postArray['Event'];
//        $callback_func = $recv_msg[$MsgType][$event];
//        //    watchdog('wechat recv message',
//        //             'msg type @msgtype and event type @event, call @call_back',
//        //             array('@msgtype' => $MsgType, '@event' => $event, '@call_back' => $callback_func));
//        if(!isset($callback_func)){
//        watchdog('wechat recv message', 'can not find msg type @msgtype and event @event in recv msg callback array',
//        array('@msgtype' => $MsgType, '@event' => $event));
//
//        return $result;
//        }
//        }else{
//        $callback_func = $recv_msg[$MsgType];
//        //    watchdog('wechat recv message', 'msg type @msgtype, call @call_back', array('@msgtype' => $MsgType, '@call_back' => $callback_func));
//        if(!isset($callback_func)){
//        watchdog('wechat recv message', 'can not find msg type @msgtype in recv msg callback array',
//        array('@msgtype' => $MsgType));
//
//        return $result;
//        }
//        }
//        $cb = (string)$callback_func;
//        //watchdog('wechat recv message', 'callback function @msgtype ', array('@msgtype' => $cb));
//        //callback function invoked by message type and event
//        $result = $callback_func($postArray);
//
        return $result;

    }

    /**
     * Convert xml object to array
     */
    public function xml2array($xml) {
        $arr = array();

        foreach ($xml->children() as $r)
        {
            $t = array();
            if(count($r->children()) == 0)
            {
                $arr[$r->getName()] = strval($r);
            }
            else
            {
                $arr[$r->getName()][] = xml2array($r);
            }
        }
        return $arr;
    }
}


