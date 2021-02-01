<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/1
 * Time: 10:45
 */

namespace app\modules\mch\models\mch;

use Alipay\AlipayRequestFactory;
use app\models\AccountMch;
use app\models\alipay\MpConfig;
use app\models\alipay\TplMsgForm;
use app\models\common\CommonFormId;
use app\models\FormId;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\Option;
use app\models\User;
use app\modules\mch\models\MchModel;
use app\models\common\admin\log\CommonActionLog;
use app\utils\CurlHelper;

class AccountEditForm extends MchModel
{
    /** @var AccountMch $model */
    public $model;

    public $username;
    public $mchlist;
    public $password;

    public function attributeLabels()
    {
        return [
            'username' => '联系人',
            'mchlist' => '所选门店',
            'password' => '密码',
        ];
    }

    public function rules()
    {
        return [
            [['mchlist', 'username', 'password'], 'trim'],
            [['mchlist', 'username', 'password'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $ML = explode(',',$this->mchlist);
        $json = json_encode($ML,JSON_UNESCAPED_UNICODE);

        $this->model->username = $this->username;
        $this->model->password = \Yii::$app->security->generatePasswordHash($this->password);
        $this->model->auth_key = \Yii::$app->security->generateRandomString();
        $this->model->access_token = \Yii::$app->security->generateRandomString();
        $this->model->update_time = date('Y-m-d H:i:s');

        $this->model->mch_json = $json;

        if ($this->model->save()) {
            return [
                'code' => 0,
                'msg' => '操作成功',
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }

}
