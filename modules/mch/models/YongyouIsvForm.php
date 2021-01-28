<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 11:58
 */

namespace app\modules\mch\models;

/**
 * @property \app\models\MailSetting $list
 */
class YongyouIsvForm extends MchModel
{
    public $list;
    public $isv_appkey;
    public $isv_appsecret;
    public $cert;
    public $account_id;
    public $orgid;
    public $authmode;
    public $account;
    public $account_password;
    public $account_number;


    public function rules()
    {
        return [
            [['cert'], 'required'],
            [['cert'], 'string'],
            [['account_id'], 'integer'],
            [['isv_appkey', 'isv_appsecret', 'orgid', 'authmode', 'account', 'account_password', 'account_number'], 'string', 'max' => 255],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        if (!is_dir(\Yii::$app->runtimePath . '/pem')) {
            mkdir(\Yii::$app->runtimePath . '/pem');
            file_put_contents(\Yii::$app->runtimePath . '/pem/index.html', '');
        }
        $cert_pem_file = null;
        if ($this->cert) {
            $cert_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($this->cert);
            if (!file_exists($cert_pem_file)) {
                file_put_contents($cert_pem_file, $this->cert);
            }
            if (!file_exists($cert_pem_file)) {
                return [
                    'code'=>1,
                    'msg'=>'证书读取不到'
                ];
            }
        }

        $this->list->isv_appkey = $this->isv_appkey;
        $this->list->isv_appsecret = $this->isv_appsecret;
        $this->list->cert = $this->cert;
        $this->list->orgid = $this->orgid;
        $this->list->authmode = $this->authmode;
        $this->list->account_id = $this->account_id;
        $this->list->account_password = $this->account_password;
        $this->list->account_number = $this->account_number;
        if ($this->list->save()) {
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        } else {
            return $this->getErrorResponse($this->list);
        }
    }
}
