<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index(){
//        echo __METHOD__;
   echo '<pre>';print_r($_GET);echo '</pre>';

   $code = $_GET['code'];

    //获取access_token
     $this->getAccessToken($code);
    }
        /**
        根据code 获取access_token
         * @param $code
         */
        protected function getAccessToken($code){
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECREET').'&code='.$code.'&grant_type=authorization_code';
            $json_data = file_get_contents($url);
            $data = json_decode($json_data,true);
            echo '<pre>';print_r($data);echo '<pre>';

    }
}
