<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class WeixinController extends Controller
{
    protected $access_token;

    public function __construct()
    {
        //获取sccess_token
        $this->access_token = $this->GetAccessToken();
    }

    public function GetAccessToken(){
        $keys="wx_access_token";
        $access_token=Redis::get($keys);
        if($access_token){
            return $access_token;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECREET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json,true);
        Redis::set($keys,$arr['access_token']);
        Redis::expire($keys,3600);
        return $arr['access_token'];
    }

    //接入微信
    public function wx(){
        $token='90d162aa1f38ee74a8a7041bd2201ba4';
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr=$_GET['echostr'];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );



        if($tmpStr == $signature){
            echo $echostr;
        }else{
            die('not ok');
        }
        
    }

    /*
   * 接收微信推送事件
   */
    public function receiv(){
        $log_file = 'wx.log';
        $xml_str = file_get_contents("php://input");
        //将接收的数据记录到日志文件
        $data = date('Y-m-d H:i:s').$xml_str;
        file_put_contents($log_file,$data,FILE_APPEND);         //追加写
        //处理xml数据
        $xml_obj = simplexml_load_string($xml_str);
        //获取TOKEN
        $access_token=$this->GetAccessToken();
        //调用微信用户信息
        $yonghu=$this->getUserInfo($access_token,$xml_obj->FromUserName);
       //转换用户信息
        $userInfo=json_decode($yonghu,true);
        //打印用户信息
//        dd($userInfo);
        $openid = $xml_obj->FromUserName;   //获取用户的openid
        if($xml_obj->MsgType=='event') {
           $event = $xml_obj->Event;  //获取事件7类型 是不是关注
           if ($event == 'subscribe') {
               $user_data = [
                   'openid' => $openid,
                   'sub_time' => $xml_obj->CreateTime,
                   'nickname' =>$userInfo['nickname'],
                   'sex' =>$userInfo['sex']
               ];
               $u = WxUserModel::where(['openid' => $openid])->first();
               if ($u) {
                   $this->huifu($xml_obj,3,$userInfo['nickname']);
               } else {
                   //入库
                   $uid = WxUserModel::insertGetId($user_data);
                   $this->huifu($xml_obj,2,$userInfo['nickname']);
               }
           }
       }elseif($xml_obj->MsgType=='image'){ //图片
            $media_id=$xml_obj->MediaId;
            $openid=$xml_obj->FromUserName;
//            dd($media_id);die;
            $res=$this->picture($media_id,$openid);
            if($res){
                $this->huifu($xml_obj,4,$userInfo['nickname']);
            }
        }


        $msg_type = $xml_obj->MsgType;
        if ($msg_type == 'text') {
            $this->huifu($xml_obj,1,$userInfo['nickname']);

        }
    }

    /**
     *获取用户基本信息
     */
    public function getUserInfo($access_token,$openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        //发送网络请求
        $json_str = file_get_contents($url);
        $log_file = 'wx.user.log';
        file_put_contents($log_file,$json_str,FILE_APPEND);
        return $json_str;
    }
     //给用户发送消息
    public  function  huifu($xml_obj,$code,$nickname){
        $time = time();
        $touser = $xml_obj->FromUserName;  //接受用户的openid
        $fromuser = $xml_obj->ToUserName;   //开发者公众号的id

        if($code==1){
            $content = "您好 ".$nickname . " 现在北京时间".date('Y-m-d H:i:s') . "   " . $xml_obj->Content;
        }elseif($code==2){
            $content ="您好 ". $nickname ." 现在北京时间".date('Y-m-d H:i:s') . "   " . "欢迎关注";
        }elseif($code==3){
            $content = "您好 ". $nickname ." 现在北京时间".date('Y-m-d H:i:s') . "   " . "欢迎回来";
        }elseif($code==3){
            $content = "您好 ". $nickname ." 现在北京时间".date('Y-m-d H:i:s') . "   " . "欢迎回来";
        }elseif($code==4){
            $content = "您好 ". $nickname ." 现在北京时间".date('Y-m-d H:i:s') . "   " . "下载成功";
        }

        $response_text = '<xml>
  <ToUserName><![CDATA[' . $touser . ']]></ToUserName>
  <FromUserName><![CDATA[' . $fromuser . ']]></FromUserName>
  <CreateTime>' . $time . '</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[' . $content . ']]></Content>
</xml>';
        echo $response_text;            // 回复用户消息

    }

    //微信下载图片
    public  function  picture($media_id,$openid){
        $access_token=$this->GetAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
         //下载图片
        $img= file_get_contents($url);
        $time=date('Ymd');
        //保存图片
        file_put_contents('ziliao/'.$time.'/'.$openid.'/cat2.jpg',$img);
//        file_put_contents('123/cat2.jpg',$img);

     return "下载成功";
    }

}
