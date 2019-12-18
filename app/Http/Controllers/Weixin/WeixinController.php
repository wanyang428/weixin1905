<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use App\Model\WxImgModel;
use App\Model\WxLiuyanModel;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class WeixinController extends Controller
{
    protected $access_token;

    //获取token
    public function __construct()
    {
        //获取sccess_token
        $this->access_token = $this->GetAccessToken();
    }
    public function GetAccessToken()
    {
        $keys = "wx_access_token";
        $access_token = Redis::get($keys);
        if ($access_token) {
            return $access_token;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . env('WX_APPID') . '&secret=' . env('WX_APPSECREET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json, true);
        Redis::set($keys, $arr['access_token']);
        Redis::expire($keys, 3600);
        return $arr['access_token'];
    }

    //接入微信
    public function wx()
    {
        $token = '90d162aa1f38ee74a8a7041bd2201ba4';
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr = $_GET['echostr'];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);


        if ($tmpStr == $signature) {
            echo $echostr;
        } else {
            die('not ok');
        }

    }

    //接收微信推送事件
    public function receiv()
    {
        $log_file = 'wx.log';
        $xml_str = file_get_contents("php://input");
        //将接收的数据记录到日志文件
        $data = date('Y-m-d H:i:s') . $xml_str;
        file_put_contents($log_file, $data, FILE_APPEND);         //追加写
        //处理xml数据
        $xml_obj = simplexml_load_string($xml_str);
        //获取TOKEN
        $access_token = $this->GetAccessToken();
        //调用微信用户信息
        $yonghu = $this->getUserInfo($access_token, $xml_obj->FromUserName);
        //转换用户信息
        $userInfo = json_decode($yonghu, true);
        //打印用户信息
        //        dd($userInfo);
        $openid = $xml_obj->FromUserName;   //获取用户的openid
        if ($xml_obj->MsgType == 'event') {
            $event = $xml_obj->Event;  //获取事件7类型 是不是关注
            if ($event == 'subscribe') {
                $user_data = [
                    'openid' => $openid,
                    'sub_time' => $xml_obj->CreateTime,
                    'nickname' => $userInfo['nickname'],
                    'headimgurl' => $userInfo['headimgurl'],
                    'sex' => $userInfo['sex']
                ];
                $u = WxUserModel::where(['openid' => $openid])->first();
                if ($u) {
                    $this->huifu($xml_obj, 3, $userInfo['nickname']);
                } else {
                    //入库
                    $uid = WxUserModel::insertGetId($user_data);
                    $this->huifu($xml_obj, 2, $userInfo['nickname']);
                }
            }elseif($event=='CLICK'){
                //点击获取天气
                if($xml_obj->EventKey=='tianqi'){
                    //天气链接
                   $url="https://free-api.heweather.net/s6/weather/now?location=beijing&key=0d739e0b250a41d18e29ce498727d9ec";
                    //读取天气
                    $url_info=file_get_contents($url);
                    //转换成数组
                    $url_info_arr=json_decode($url_info,true);
                    //获取城市
                    $location=$url_info_arr['HeWeather6']['0']['basic']['location'];
                    //获取天气状况
                    $cond_txt=$url_info_arr['HeWeather6']['0']['now']['cond_txt'];
                    //获取天气温度
                    $tmp=$url_info_arr['HeWeather6']['0']['now']['tmp'];
                    //获取天气风向
                    $wind_dir=$url_info_arr['HeWeather6']['0']['now']['wind_dir'];
                    //获取天气风力
                    $wind_sc=$url_info_arr['HeWeather6']['0']['now']['wind_sc'];
                    $res=$location.' 天气:'.$cond_txt.' 温度:'.$tmp."\n".' 风向:'.$wind_dir.' 风力'.$wind_sc;
                    $this->huifu($xml_obj, 5, $userInfo['nickname'],$res);

                }
            }
            //判断格式图片
        }elseif ($xml_obj->MsgType == 'image') {
            $media_id = $xml_obj->MediaId;
            $openid = $xml_obj->FromUserName;
            //            dd($media_id);die;
            $res = $this->picture($media_id, $openid);
            $user_data=[
                'openid' => $openid,
                'imgs' => $res['lujing'],
            ];
               WxImgModel::insert($user_data);
            if ($res) {
                $this->huifu($xml_obj, 4, $userInfo['nickname'],$res['url']);
            }
            //判断格式视频
        } elseif ($xml_obj->MsgType == 'video') {
            $media_id = $xml_obj->MediaId;
            $openid = $xml_obj->FromUserName;
            //            dd($media_id);die;
            $res = $this->video($media_id, $openid);
            if ($res) {
                $this->huifu($xml_obj, 4, $userInfo['nickname'],$res);
            }
            //语言消息
        } elseif ($xml_obj->MsgType == 'voice') {
            $media_id = $xml_obj->MediaId;
            $openid = $xml_obj->FromUserName;
            //            dd($media_id);die;
            $res = $this->voice($media_id, $openid);
            if ($res) {
                $this->huifu($xml_obj, 4, $userInfo['nickname'],$res);
            }

            //文字消息
        } elseif ($xml_obj->MsgType == 'text') {
            $user_data = [
                'openid' => $openid,
                'content' => $xml_obj->Content,
            ];
            WxLiuyanModel::insert($user_data);
            $this->huifu($xml_obj, 1, $userInfo['nickname']);
        }


    }

    //获取用户基本信息
    public function getUserInfo($access_token, $openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        //发送网络请求
        $json_str = file_get_contents($url);
        $log_file = 'wx.user.log';
        file_put_contents($log_file, $json_str, FILE_APPEND);
        return $json_str;
    }

    //给用户发送消息
    public function huifu($xml_obj, $code, $nickname,$res="")
    {
        $time = time();
        $touser = $xml_obj->FromUserName;  //接受用户的openid
        $fromuser = $xml_obj->ToUserName;   //开发者公众号的id

        if ($code == 1) {
            $content = "您好 " . $nickname . " 现在北京时间" . date('Y-m-d H:i:s') . "   " . $xml_obj->Content;
        } elseif ($code == 2) {
            $content = "您好 " . $nickname . " 现在北京时间" . date('Y-m-d H:i:s') . "   \n" . "欢迎关注";
        }elseif ($code == 3) {
            $content = "您好 " . $nickname . " 现在北京时间" . date('Y-m-d H:i:s') . "   \n" . "欢迎回来";
        } elseif ($code == 4) {
            $content = "您好 " . $nickname . " 现在北京时间" . date('Y-m-d H:i:s') . "   \n" . "获取成功\n".$res;

        }elseif($code==5){
            $content = "您好 " . $nickname . " 现在北京时间" . date('Y-m-d H:i:s') . "   \n" .$res;

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

    //获取文件后辍
    public function fromat($media_id)
    {
        $client = new Client();
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->access_token . '&media_id=' . $media_id;
        $format = $client->request('GET', $url)->getHeader('Content-disposition')[0];
        $format = trim(substr($format, strpos($format, '.') + 1), '\"');
        return '.' . $format;
    }

    //微信下载图片
    public function picture($media_id, $openid)
    {
        $access_token = $this->GetAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
        //下载图片
        $img = file_get_contents($url);
        //图片名称
        $name = $this->fromat($media_id);
        $name = date('YmdHis') . rand(10000, 99999) . $name;    
        //保存图片
        $time = date('Ymd');
        $wenjian = 'ziliao/image/' . $time . '/' . $openid . '/';
        if (!is_dir($wenjian)) {
            $res = mkdir($wenjian, 0777, true);
        }
        file_put_contents($wenjian . '/' . $name, $img);
        $lujing=$wenjian . '/' . $name;
        $data=[
            'lujing'=>$lujing,
            'url'=>$url,
        ];
        return $data;

        //        file_put_contents('123/cat2.jpg',$img);


    }

    //微信下载视频
    public function video($media_id, $openid){
        $access_token = $this->GetAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
        //下载视频
        $img = file_get_contents($url);
        //视频名称
        $name = $this->fromat($media_id);
        $name = date('YmdHis') . rand(10000, 99999) . $name;
        //

        //保存视频
        $time = date('Ymd');
        $wenjian = 'ziliao/video/' . $time . '/' . $openid . '/';
        if (!is_dir($wenjian)) {
            $res = mkdir($wenjian, 0777, true);
        }

        file_put_contents($wenjian . '/' . $name, $img);
        return "$url";

//        file_put_contents('123/cat2.jpg',$img);


}

    //微信下载语音
    public  function voice($media_id, $openid)
    {
        $access_token = $this->GetAccessToken();

        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
        //下载语音
        $img = file_get_contents($url);
        //名称
        $name = $this->fromat($media_id);
        $name = date('YmdHis') . rand(10000, 99999) . $name;
        //

        //保存语音
        $time = date('Ymd');
        $wenjian = 'ziliao/voice/' . $time . '/' . $openid . '/';
        if (!is_dir($wenjian)) {
            $res = mkdir($wenjian, 0777, true);
        }
         file_put_contents($wenjian . '/' . $name, $img);

        return "$url";


    }

    public  function  caidan(){
        $access_token = $this->GetAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
        $ment=[
         "button"=>[
             [
                 "type"=>"click",
                 "name"=>"今日天气",
                 "key"=>"tianqi"
             ],
             [
                 'name'=>'菜单',
                 'sub_button'=>[
                     [
                         "type"=>"click",
                         "name"=>"123",
                         "key"=>"123"
                     ],
                     [
                         "type"=>"view",
                         "name"=>"百度",
                         "url"=>"http://www.baidu.com"
                     ]
                        ]
             ]
        ]

 ];
        $json_ment=json_encode($ment,JSON_UNESCAPED_UNICODE);
        $client= new Client();
        $aaa=$client->request('POST',$url,[
            'body'=>$json_ment
        ]);
        echo $aaa->getBody();


    }


}
