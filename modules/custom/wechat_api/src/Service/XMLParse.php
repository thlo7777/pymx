<?php

namespace Drupal\wechat_api\Service;

use Drupal\wechat_api\Service\ErrorCode;
/**
 * XMLParse class
 *
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 */
class XMLParse {

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
	/**
	 * 提取出xml数据包中的加密消息
	 * @param string $xmltext 待提取的xml字符串
	 * @return string 提取出的加密消息字符串
	 */
	public function extract($xmltext) {
		try {

            $xmldata = simplexml_load_string($xmltext, 'SimpleXMLElement', LIBXML_NOCDATA);
            $xml_post = $this->xml2array($xmldata);

//			$xml = new DOMDocument();
//			$xml->loadXML($xmltext);
//			$array_e = $xml->getElementsByTagName('Encrypt');
//			$array_a = $xml->getElementsByTagName('ToUserName');
//			$encrypt = $array_e->item(0)->nodeValue;
//			$tousername = $array_a->item(0)->nodeValue;

            $encrypt = $xml_post['Encrypt'];
            $tousername = $xml_post['ToUserName'];
			return array(0, $encrypt, $tousername);
		} catch (Exception $e) {
			//print $e . "\n";
			return array(ErrorCode::$ParseXmlError, null, null);
		}
	}

	/**
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 */
	public function generate($encrypt, $signature, $timestamp, $nonce) {
		$format = "<xml>
            <Encrypt><![CDATA[%s]]></Encrypt>
            <MsgSignature><![CDATA[%s]]></MsgSignature>
            <TimeStamp>%s</TimeStamp>
            <Nonce><![CDATA[%s]]></Nonce>
            </xml>";
		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}

}

?>
