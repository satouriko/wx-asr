<?php
/**
 * Created by PhpStorm.
 * User: lijiahao
 * Date: 12/3/16
 * Time: 11:59 AM
 */

class baiduCallbackapiTest
{
    private function get_token() {
        $auth_url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=".BAIDU_APP_KEY."&client_secret=".BAIDU_APP_SECRET;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($ch);
        if(curl_errno($ch))
        {
            print curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($response, true);
        return $response['access_token'];
    }

    public function get_reco($file) {
        $name_array = explode(".", $file);
        $leng = count($name_array);
        if($leng > 1)
            $extra = $name_array[$leng - 1];
        else
            $extra = "amr";
        $audio = file_get_contents($file);
        $base_data = base64_encode($audio);
        $array = array(
            "format" => $extra,
            "rate" => 8000,
            "channel" => 1,
            //"lan" => "zh",
            "token" => $this->get_token(),
            "cuid"=> "linux.cool2645.com",
            //"url" => "http://www.xxx.com/sample.pcm",
            //"callback" => "http://www.xxx.com/audio/callback",
            "len" => filesize($file),
            "speech" => $base_data,
        );
        $json_array = json_encode($array);
        $content_len = "Content-Length: ".strlen($json_array);
        $header = array ($content_len, 'Content-Type: application/json; charset=utf-8');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://vop.baidu.com/server_api");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_array);
        $response = curl_exec($ch);
        if(curl_errno($ch))
        {
            return curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($response);
        if($response->err_no == 0)
            return $response->result[0];
        else
            return $response->err_msg;
    }
}
