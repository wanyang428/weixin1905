<?php

namespace App\Admin\Controllers;

use App\Model\WxUserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Client;

class WxMsgController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微信用户管理';



    public function sendMsg()
    {

        echo __METHOD__;
        $openid_arr = WxUserModel::select('openid','nickname','sex')->get()->toArray();
        echo '<pre>';print_r($openid_arr);echo '</pre>';
        $openid = array_column($openid_arr,'openid');
        echo '<pre>';print_r($openid);echo '</pre>';

        $url ='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=28_3zPhznlXDpDRaH7CzlJ7LQgf60S9j1fS7s97tR5XAPJsxI1q7ki8Ixzo0_OUCcgc3a2-6q-3_fiZzYz2tgJyWJVmUpH_5I_lMMITLSLNvnOgnBXnacNbhTENqZoRX7Kz2CqaGZwXNTWaLMprNKMdACAVSU';

        $msg = date('Y-m-d H:i:s') .'123';

        $data = [
            'touser'  => $openid,
            'msgtype' => 'test',
            'test'    => ['content'=>$msg]
        ];

        $client = new Client();
        $response = $client->request('POST',$url,[
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        echo $response->getBody();
    }

}
