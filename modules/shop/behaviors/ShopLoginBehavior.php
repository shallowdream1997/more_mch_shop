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
 * @ClassName ShopLoginBehavior
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-02 16:58 星期二
 * @Version 1.0
 * @Description 验证
 */

namespace app\modules\shop\behaviors;


use yii\base\ActionEvent;
use yii\base\ActionFilter;

class ShopLoginBehavior extends ActionFilter
{
    /**
     * @param ActionEvent $e
     */
    public function beforeAction($e)
    {
        if (\Yii::$app->shop->isGuest) {
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->data = [
                    'code' => -1,
                    'msg' => '请先登录'
                ];
                return false;
            } else {
                $return_utl = \Yii::$app->request->absoluteUrl;
                \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['shop/passport/login', 'return_utl' => $return_utl]))->send();
                return false;
            }
        }
        return true;
    }
}
