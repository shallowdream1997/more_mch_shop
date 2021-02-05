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
 * @ClassName Controller
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-01-29 11:37 星期五
 * @Version 1.0
 * @Description shop端基础控制器
 */

namespace app\modules\shop\controllers;


use app\models\Mch;
use app\models\Store;
use app\models\WechatApp;
use app\modules\shop\models\route\MenuForm;
use app\modules\user\models\permission\permission\MchRoleMenuForm;
use luweiss\wechat\Wechat;
use yii\web\Cookie;
use yii\web\HttpException;

class Controller extends \app\controllers\Controller
{
    public $layout = 'main';

    /** @var  Store $store */
    public $store;

    /** @var  Wechat $wechat */
    public $wechat;

    /** @var  Mch $mch */
    public $mch;

    public function init()
    {
        parent::init();
        $store_id = \Yii::$app->request->get('store_id') ? \Yii::$app->request->get('store_id') : 1;
        if ($store_id) {
            \Yii::$app->response->cookies->add(new Cookie([
                'name' => 'zjhj_mall_store_id',
                'value' => $store_id,
                'expire' => time() + 86400 * 365,
            ]));
        } else {
            $store_id = \Yii::$app->request->cookies->get('zjhj_mall_store_id');
        }
        if (!$store_id) {
            throw new HttpException(403, 'Store Id 不能为空，请重新访问商户登录口进行登录！');
        }
        $this->store = Store::findOne($store_id);
        $wechat_app = WechatApp::findOne($this->store->wechat_app_id);

        $cert_pem_file = null;
        if ($wechat_app->cert_pem) {
            $cert_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($wechat_app->cert_pem);
            if (!file_exists($cert_pem_file)) {
                file_put_contents($cert_pem_file, $wechat_app->cert_pem);
            }
        }

        $key_pem_file = null;
        if ($wechat_app->key_pem) {
            $key_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($wechat_app->key_pem);
            if (!file_exists($key_pem_file)) {
                file_put_contents($key_pem_file, $wechat_app->key_pem);
            }
        }

        $this->wechat = new Wechat([
            'appId' => $wechat_app->app_id,
            'appSecret' => $wechat_app->app_secret,
            'mchId' => $wechat_app->mch_id,
            'apiKey' => $wechat_app->key,
            'certPem' => $cert_pem_file,
            'keyPem' => $key_pem_file,
            'cachePath' => \Yii::$app->runtimePath . '/cache',
        ]);
    }

    public function getMenuList()
    {
        $m = new MenuForm();

        $res = $m->getList();

        return $res;
    }
}
