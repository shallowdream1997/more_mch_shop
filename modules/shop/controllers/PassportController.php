<?php
/**
 * more_mch_shop
 * ==================================================================
 * CopyRight © 2017-2099 广州米袋软件有限公司
 * 官网地址：http://www.mdsoftware.cn
 * 售后技术支持：15017566075
 * ------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下
 * 对本程序代码进行修改和使用；不允许对本程序代码以任何目的的再发布。
 * ==================================================================
 *
 * @ClassName PassportController
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-01-29 11:31 星期五
 * @Version 1.0
 * @Description 登陆控制器
 */
namespace app\modules\shop\controllers;


use app\modules\shop\models\SLoginForm;

class PassportController extends Controller
{
    public $layout = 'passport';

    public function actionLogin()
    {
        \Yii::$app->session->open();
        if (\Yii::$app->request->isAjax) {
            $form = new SLoginForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->login();
        } else {
            return $this->render('login');
        }
    }

}
