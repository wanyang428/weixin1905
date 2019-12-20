<?php

namespace App\Admin\Controllers;

use App\Model\GoodsModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GoodsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GoodsModel);

        $grid->column('id', __('Id'));
        $grid->column('goos_name', __('Goos name'));
        $grid->column('img', __('Img'));
        $grid->column('price', __('Price'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updata_at', __('Updata at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(GoodsModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('goos_name', __('Goos name'));
        $show->field('img', __('Img'));
        $show->field('price', __('Price'));
        $show->field('created_at', __('Created at'));
        $show->field('updata_at', __('Updata at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GoodsModel);

        $form->text('goos_name', __('Goos name'));
        $form->image('img', __('Img'));
        $form->number('price', __('Price'));
        $form->datetime('updata_at', __('Updata at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
