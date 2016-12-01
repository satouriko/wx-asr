<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "cool2645");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();

if($wechatObj->checkSignature())
{
    $wechatObj->responseMsg();
}
else
{
    echo "Invalid access!";
}

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $msgType = $postObj->MsgType;
                switch ($msgType) {
                    case "text":
                        $keyword = trim($postObj->Content);
                        $time = time();
                        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                        if(!empty( $keyword ))
                        {
                            $msgType = "text";
                            $contentStr = $this->replyByKeyword($keyword);
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            $this->saveMsg($fromUsername, $keyword, $resultStr);
                        }else{
                            echo "Input something QwQ";
                        }
                        break;
                    case "voice":
                        $mediaId = $postObj->MediaId;
                        $format = $postObj->Format;
                        $this->http_get_data($mediaId, $format);
                        $reco = $postObj->Recognition;
                        $time = time();
                        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                        $msgType = "text";
                        $contentStr = $reco;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        $this->saveMsg($fromUsername, $mediaId.$format, $resultStr);
                }

        }else {
        	echo "";
        	exit;
        }
    }

    private function replyByKeyword($keyword) {
        switch ($keyword) {
            case "你好":
                return "你好！";
            default:
                return "欢迎使用李家豪的Pi-Car语音控制测试号！ <a href='http://linux.cool2645.com/pi/'>Pi-Car Index</a> <a href='https://github.com/hudson6666/wx-csr'>本项目Repo</a> ";
        }
    }

    private function saveMsg($fromUsername, $content, $resultStr)
    {
        if(($logFile = fopen("msglog.txt","a+")) != NULL)
        {
            $timestr = "[" . date('y-m-d h:i:s',time()) . "]";
            $username = "From: " . $fromUsername;
            $content = "Content: " . $content;
            $resultStr = "Reply: " . $resultStr;
            $splitter = "************************************";
            fwrite($logFile,$timestr . "\n" . $username . "\n" . $content . "\n" . $resultStr . "\n" . $splitter . "\n");
        }
        else
            echo "Log file open error!";
    }

    private function http_get_data($mediaId, $format) {
        if(!file_exists("tmp"))
            mkdir("tmp");
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_URL, "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".TOKEN."&media_id=".$mediaId );
        ob_start ();
        curl_exec ( $ch );
        $return_content = ob_get_contents ();
        ob_end_clean ();

        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );

        $filename = $mediaId.$format;
        $fp= @fopen("tmp/".$filename,"a"); //将文件绑定到流 
        fwrite($fp,$return_content);
    }

	public function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>