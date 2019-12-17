<?php

namespace App\Admin\Controllers;

use App\Model\WxLiuyanModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WxLiuyanController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Model\WxLiuyanModel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WxLiuyanModel);

        $grid->column('lid', __('Lid'));
        $grid->column('openid', __('Openid'));
        $grid->column('content', __('Content'));
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
        $show = new Show(WxLiuyanModel::findOrFail($id));

        $show->field('lid', __('Lid'));
        $show->field('openid', __('Openid'));
        $show->field('content', __('Content'));
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
        $form = new Form(new WxLiuyanModel);

        $form->text('openid', __('Openid'));
        $form->text('content', __('Content'));

        return $form;
    }
}
