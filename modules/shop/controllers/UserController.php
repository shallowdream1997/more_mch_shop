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
use app\modules\shop\behaviors\ShopLoginBehavior;
use yii\data\Pagination;

class UserController extends Controller
{
    public $layout = 'passport';
    public function behaviors()
    {
        return [
            'login' => [
                'class' => ShopLoginBehavior::className(),
            ],
        ];
    }

    public function actionSelectMch()
    {
        if (!\Yii::$app->shop->isGuest)
        {
            $page = \Yii::$app->request->get('page',1);
            $mch_arr = json_decode(\Yii::$app->shop->identity->mch_json,JSON_UNESCAPED_UNICODE);
            $condition = ['and', ['in', 'id', $mch_arr]];
            $query = Mch::find()->where($condition)->select('id,realname,tel,address');
            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count, 'page' => $page - 1, 'pageSize' => 10]);
            $mc = $query->limit($pagination->limit)->offset($pagination->offset)->asArray()->all();
            return $this->render('select-mch',[
                'mc' => $mc,
                'pagination' => $pagination,
            ]);
        }
        $return_utl = \Yii::$app->request->absoluteUrl;
        \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['shop/passport/login', 'return_utl' => $return_utl]))->send();
    }
}
