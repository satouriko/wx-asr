<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "cool2645");
define("WECHAT_APP_KEY", "");
define("WECHAT_APP_SECRET", "");
define("BAIDU_APP_KEY", "");
define("BAIDU_APP_SECRET", "");

require_once ("include/wechat.php");
require_once ("include/baidu.php");

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