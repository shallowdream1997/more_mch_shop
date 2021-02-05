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
 * @ClassName ShopController
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-02 16:47 星期二
 * @Version 1.0
 * @Description 账户端控制器基类
 */

namespace app\modules\shop\controllers;


use app\modules\shop\behaviors\ShopLoginBehavior;
use app\modules\shop\models\MchLoginForm;

class ShopController extends Controller
{
    public function behaviors()
    {
        return [
            'login' => [
                'class' => ShopLoginBehavior::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax){
            $form = new MchLoginForm();
            $data = \Yii::$app->request->post();
            $form->mch_id = $data['Mchid'];
            $res = $form->save();
            return $res;
        }else{
            return [
                'code' => 1,
                'msg' => '进入失败'
            ];
        }
    }

    /**
     *
     * 注销
     */
    public function actionLoginout()
    {
        \Yii::$app->shop->logout();
        \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['shop/passport/login']))->send();
        return [
            'code' => 0,
        ];
    }
}
