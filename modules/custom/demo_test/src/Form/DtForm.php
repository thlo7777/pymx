<?php

namespace Drupal\demo_test\Form;


use Drupal\wechat_api\Service\WechatApiService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\taxonomy\Entity\Term;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a singe text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class DtForm extends FormBase {

    protected $wechat_api;

    protected $term_storage;

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('service.wechatapi')
        );
    }

    public function __construct(WechatApiService $service) {
        $this->wechat_api = $service;

        $this->term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    }

    protected static $test_array = [
        'event' => [
            'subscribe' => 'func1',
            'unsubscribe' => 'func2'
        ],
        'text' => 'recv_text',
        'image' => 'recv_image',
    ];

    public function recv_image($str) {
        //dpm('image: '. $str);
    }

    public function recv_text($str) {
        //dpm('text: '. $str);
    }

    public function func1($int) {
        //dpm('func1: ' . $int);
    }
    public function func2($int) {
        //dpm('func2: ' . $int);
    }

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#description' => $this->t('Title must be at least 5 characters in length.'),
          '#required' => TRUE,
        ];

        //$node = \Drupal::entityManager()->getStorage('node')->load(6)->toArray();
        //kint($node);

//        $node = \Drupal::entityManager()->getStorage('node')->load(16)->toArray();
//
//        //dpm($node);
//        $ref_ids = $node['field_ref_wechat_api'];
//        //dpm($ref_ids);
//
//        //$func = $test_array['event']['unsubscribe'];
//        $func = self::$test_array['text'];
//        $this->$func('hello');
//
//        $func = self::$test_array['event']['subscribe'];
//        $this->$func(4);

//        $ch = curl_init();

//        $dld_app_config = \Drupal::service('config.factory')->getEditable('dld.wxapp.config');
//        $dld_app_config->delete('hello config');
//        $dld_app_config->save();
//        $token = \Drupal::config('dld.wxapp.config')->get('get access token');
//        $AppID = \Drupal::config('dld.wxapp.config')->get('AppID');
//        $AppSecret = \Drupal::config('dld.wxapp.config')->get('AppSecret');
//        $token_url = t( $token, array( '@APPID' => $AppID, '@APPSECRET' => $AppSecret) )->render();

        //dpm($token_url);

        $service = \Drupal::service('service.wechatapi');

        \Drupal::logger('DtForm')->notice( 'ip addr: <pre>@data</pre>', array('@data' => print_r($this->wechat_api->get_ip_server(), true)) );

//        $service = \Drupal::service('service.wechatmsgcrypt');
////$encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
//$encodingAesKey = "jNzJclldDQ0Nt6A2z4EdFLOMbGp3jTo3ilpFLN8qcZl";
//$token = "pamtest";
//$timeStamp = "1409304348";
//$nonce = "xxxxxx";
//$appId = "wxb11529c136998cb6";
//$text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";
//
//
//$service->SetParameters($token, $encodingAesKey, $appId);
//
//$encryptMsg = '';
//$errCode = $service->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
//if ($errCode == 0) {
//    \Drupal::logger('DtForm')->notice('$encryptMsg: @data', array('@data' => $encryptMsg));
//} else {
//    dpm("errcode " . $errCode);
//}
//
//$xmldata = simplexml_load_string($encryptMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
//$xml_post = $this->xml2array($xmldata);
//dpm($xml_post);
//
//$encrypt = $xml_post['Encrypt'];
//$msg_sign = $xml_post['MsgSignature'];
////$xml_tree = new DOMDocument();
////$xml_tree->loadXML($encryptMsg);
////$array_e = $xml_tree->getElementsByTagName('Encrypt');
////$array_s = $xml_tree->getElementsByTagName('MsgSignature');
////$encrypt = $array_e->item(0)->nodeValue;
////$msg_sign = $array_s->item(0)->nodeValue;
////
//
//$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
//$from_xml = sprintf($format, $encrypt);
//
//// 第三方收到公众号平台发送的消息
//$msg = '';
//$errCode = $service->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
//if ($errCode == 0) {
//    \Drupal::logger('DtForm')->notice('$msg: @data', array('@data' => $msg));
//    dpm("解密后: " . $msg);
//} else {
//    dpm("errcode " . $errCode);
//}
//
//

//        dpm($this->wechat_api->get_access_token());
//
//        $entity = \Drupal::entityManager()->getStorage('node')->load(15);
//        $entity->field_text1->value = "hello token";
//        $entity->save();
//
//        $result = $this->wechat_api->wechat_php_curl_https_get($token_url);
//        \Drupal::logger('DtForm')->notice( 'data: <pre>@data</pre>', array('@data' => print_r($result, true)) );

        
//        $build_tree = [];
//
//        $term_root = $this->term_storage->loadTree("yuwenzhishidian", 0, 1);
//        //$term_root = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree("yuwenzhishidian", 0, 1);
//
//        $parent = 0;
//
//        foreach ($term_root as $term) {
//
//            //\Drupal::logger('buildTree')->notice('$term: <pre>@data</pre>', array('@data' => print_r($term, true)));
//            $this->get_children($build_tree, $term, "yuwenzhishidian", 1);
//        }
//
//        //dpm(json_encode($build_tree, JSON_UNESCAPED_UNICODE));
//        //ksm($build_tree);
//
//        //dpm($this->term_storage->loadTree("yuwenzhishidian", 47, 1));
//
//
//        $term_root = $this->term_storage->loadTree("yuwen_shiti", 9, 1);
//        $child_tree = [];
//
//
//        if ( !count($term_root) ) {
//
//            //$term = $this->term_storage->load(9);
//            $term = Term::load(9);
//
//            ksm($term);
//            $child_tree[$term->id()]->id = $term->id();
//            $child_tree[$term->id()]->vid = $term->getVocabularyId();
//            $child_tree[$term->id()]->name = $term->getName();
//            $child_tree[$term->id()]->children = [];
//
//
//            //ksm($term->toArray());
//            //$child_tree[$object->tid] = $object;
//        } else {
//            foreach ($term_root as $term) {
//                $this->build_child_term_tree($child_tree, $term, "yuwen_shiti", 1);
//            }
//        }
//
//
//        ksm($child_tree);

        return $form;
    }

    protected function build_child_term_tree(&$build, $object, $vid, $max_leve) {

        $child_tree = $this->term_storage->loadTree($vid, $object->tid, 1);

        //ksm($object->tid);
        $build[$object->tid] = $object;
        $build[$object->tid]->children = [];
        $object_children = &$build[$object->tid]->children;

        if ( !count($child_tree) ) {
            return ; 
            
        } else {

            foreach ($child_tree as $childObject) {
                $this->build_child_term_tree($object_children, $childObject, $vid, 1);
            }
        }

    }

    public function get_children(&$build, $object, $vid, $max_leve) {

        //$children = $this->term_storage->loadChildren($object->tid, $vid);
        $child_tree = $this->term_storage->loadTree($vid, $object->tid, 1);
        
        $node_item = new term_node();
        $node_item->text = $object->name;
        $node_item->href = "javascript:void(0)";
        $node_item->tags = [$object->tid];

        if ( count($child_tree) ) {
            $node_item->nodes = [];

            foreach ($child_tree as $childObject) {
                $this->get_children($node_item->nodes, $childObject, $vid, 1);
            }
        }

        $build[] = $node_item;

    }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
    public function getFormId() {
        return 'demo_test_form';
    }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
//    $title = $form_state->getValue('title');
//    if (strlen($title) < 5) {
//      // Set an error for the form element with a key of "title".
//      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
//    }
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * This would normally be replaced by code that actually does something
     * with the title.
     */
//    $title = $form_state->getValue('title');
//    drupal_set_message(t('You specified a title of %title.', ['%title' => $title]));
  }

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

class term_node {
    public $text;
    public $href;
    public $tags;
}

