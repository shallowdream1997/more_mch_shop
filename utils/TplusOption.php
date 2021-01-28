<?php


namespace app\utils;


use app\models\YongyouIsv;

class TplusOption
{
    public static function Options($url,$apiParm)
    {
        $yongyouisv = YongyouIsv::findOne(1);
        $options = [
            'appkey' => $yongyouisv->isv_appkey, // ISV账号的AppKey <必须>
            'appsecret' => $yongyouisv->isv_appsecret, // ISV账号的AppSecret <必须>
            'cert' => \Yii::$app->runtimePath .'/pem/'. md5($yongyouisv->cert), // 申请ISV账号审核通过后下发的pem版证书，使用cjet_pri.pem文件 <必须>
            'orgid' => '', // 企业云账号 <非账套模式必须，即authmode=ecloud>
            'authmode' => $yongyouisv->authmode, // 认证模式 account-账套 ecloud-企业云账号模式
            'account' => [ // 账套账号配置 <account模式下必须>
                'id' => $yongyouisv->account_id, // 账套账号ID <account模式下必须> 账号名
                'password' => $yongyouisv->account_password, // 账套账号密码 <account模式下必须>
                'number' => $yongyouisv->account_number, // 账套编号 <account模式下必须>
            ],
        ];
        # 实例化
        $tplusApi = new TplusApi($options);
        # 创建授权报头(鉴权)
        $tplusApi::createAuthorizationHeader($authorizationHeader);
        # 创建访问令牌
        $tplusApi::createAccessToken($authorizationHeader);
        # 创建授权报头(业务)
        $tplusApi::createAuthorizationHeader($authorizationHeader);
        # 业务演示
        $tplusApi::setAPIUrl($url);
        $tplusApi::post($authorizationHeader,$inventoryList,$apiParm);
        return $inventoryList;
    }
}
