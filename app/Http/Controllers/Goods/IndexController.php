<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\GoodsModel;

class IndexController extends Controller
{
    public function detail(Request $request)
    {
        /**
            商品详情页
         */
        $goods_id = $request->input('id');
        $goods = GoodsModel::find($goods_id);
//        echo '<pre>';print_r($goods);echo '</pre>';
        $data = [
            'goods'=>$goods
        ];
        return view('goods.detail',$data);
    }
}