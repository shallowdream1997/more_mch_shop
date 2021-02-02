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
 * @ClassName User
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-02 16:15 星期二
 * @Version 1.0
 * @Description
 */

namespace app\modules\shop\controllers;


use app\models\Mch;
use app\modules\shop\behaviors\MchLoginBehavior;
use app\modules\shop\behaviors\ShopLoginBehavior;
use yii\data\Pagination;

class AccountController extends Controller
{
    public function behaviors()
    {
        return [
            'login' => [
                'class' => ShopLoginBehavior::className(),
            ],
            'mch' => [
                'class' => MchLoginBehavior::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        if (!\Yii::$app->shop->isGuest && !\Yii::$app->mch->isGuest)
        {
            echo "<pre>";
            var_dump(\Yii::$app->mch->identity);
            var_dump(\Yii::$app->shop->identity);
            exit;
        }
        $return_utl = \Yii::$app->request->absoluteUrl;
        \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['shop/user/select-mch', 'return_utl' => $return_utl]))->send();
    }
}
