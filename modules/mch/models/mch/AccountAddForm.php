<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/6/8
 * Time: 15:41
 */


namespace app\modules\mch\models\mch;

use app\models\AccountMch;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\User;
use app\modules\mch\models\MchModel;

class AccountAddForm extends MchModel
{
    public $store_id;
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
            return $this->getErrorResponse();
        }

        $exist_mch = AccountMch::findOne([
            'store_id' => $this->store_id,
            'username' => $this->username,
            'is_delete' => 0,
        ]);
        if ($exist_mch) {
            return [
                'code' => 1,
                'msg' => '该账户已经创建。',
            ];
        }

        $ML = explode(',',$this->mchlist);
        $json = json_encode($ML,JSON_UNESCAPED_UNICODE);

        $mchC = new AccountMch();
        $mchC->store_id = $this->store_id;
        $mchC->username = $this->username;
        $mchC->password = \Yii::$app->security->generatePasswordHash($this->password);
        $mchC->auth_key = \Yii::$app->security->generateRandomString();
        $mchC->access_token = \Yii::$app->security->generateRandomString();
        $mchC->update_time = date('Y-m-d H:i:s');
        $mchC->mch_json = $json;

        if ($mchC->save()) {
            return [
                'code' => 0,
                'msg' => '账户添加成功。',
            ];
        } else {
            return $this->getErrorResponse($mchC);
        }
    }
}
