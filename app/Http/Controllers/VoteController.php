<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class VoteController extends Controller
{
    public function index(){
//        echo __METHOD__;
//   echo '<pre>';print_r($_GET);echo '</pre>';

   $code = $_GET['code'];

    //获取access_token
     $data = $this->getAccessToken($code);
     //获取用户信息
        $info = $this->getUserInfo($data['access_token'],$data['openid']);


        $keys='h:info'.$info['openid'];
        Redis::hMset($keys,$info);
        $key='vote:1905wx';
        if(Redis::zrank($key,$info['openid'])){
            echo "已经投过票了";echo '</br>';
        }else{
            Redis::zadd($key,time(),$info['openid']);
            echo "投票成功";
            echo '</br>';
        }
        $total = Redis::zCard($key);        // 获取总数
        echo '投票总人数： '.$total;echo '</br>';
        $members = Redis::zRange($key,0,-1,true);       // 获取所有投票人的openid
//        echo '<pre>';print_r($members);echo '</pre>';
        foreach($members as $k=>$v){
//            echo "用户： ".$k . ' 投票时间: '. date('Y-m-d H:i:s',$v);echo '</br>';
            $u_k = 'h:info'.$k;
            $u = Redis::hgetAll($u_k);
            //$u = Redis::hMget($u_k,['openid','nickname','sex','headimgurl']);
//            echo ' <img src="'.$u['headimgurl'].'"> ';
            echo '<img src="'.Redis::hget('h:info'.$k,'headimgurl').'">';
        }
    }
        /**
        根据code 获取access_token
         * @param $code
         */
        protected function getAccessToken($code){
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECREET').'&code='.$code.'&grant_type=authorization_code';
            $json_data = file_get_contents($url);1
            return json_decode($json_data,true);
//            echo '<pre>';print_r($data);echo '<pre>';

    }
    /**
        获取用户基本相信
     */
    protected function getUserInfo($access_token,$openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);
       if(isset($data['errcode'])){
           //TOOO  错误处理
           die ("出错了    40001");    //  40001错误标识  获取用户信息失败
       }
       return $data;            //返回用户信息
    }
}
