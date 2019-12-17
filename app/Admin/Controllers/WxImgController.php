<?php

namespace App\Admin\Controllers;

use App\Model\WxImgModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WxImgController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Model\WxImgModel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WxImgModel);

        $grid->column('iid', __('Iid'));
        $grid->column('openid', __('Openid'));
        $grid->column('imgs', __('Imgs'))->display(function($img){
            return "<img src='".env('UPLOAD_URL').$img."' width='60' height='60' >";
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(WxImgModel::findOrFail($id));

        $show->field('iid', __('Iid'));
        $show->field('openid', __('Openid'));
        $show->field('imgs', __('Imgs'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WxImgModel);

        $form->text('openid', __('Openid'));
        $form->text('imgs', __('Imgs'));

        return $form;
    }
}
