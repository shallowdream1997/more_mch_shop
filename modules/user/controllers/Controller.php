<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/9
 * Time: 10:21
 */

namespace app\modules\user\controllers;

use app\models\Mch;
use app\models\Store;
use app\models\WechatApp;

use app\modules\user\models\permission\permission\MchRoleMenuForm;
use luweiss\wechat\Wechat;
use yii\web\Cookie;
use yii\web\HttpException;

/**
 * @property Store $store
 * @property Wechat $wechat
 * @property Mch $mch
 */
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
        $m = new MchRoleMenuForm();

        $res = $m->getList();

        return $res;
    }
}
