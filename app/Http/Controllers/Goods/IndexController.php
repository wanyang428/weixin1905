<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function detail()
    {
        /**
            商品详情页
         */
        return view('goods.detail');
    }
}
