<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%yongyou_isv}}".
 *
 * @property integer $id
 * @property string $isv_appkey
 * @property string $isv_appsecret
 * @property string $cert
 * @property string $orgid
 * @property string $authmode
 * @property string $account
 * @property string $account_id
 * @property string $account_password
 * @property string $account_number
 */
class YongyouIsv extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yongyou_isv}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cert'], 'required'],
            [['cert'], 'string'],
            [['account_id'], 'string','max'=>45],
            [['isv_appkey', 'isv_appsecret', 'orgid', 'authmode', 'account', 'account_password', 'account_number'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'isv_appkey' => 'ISV账号的AppKey <必须>',
            'isv_appsecret' => 'ISV账号的AppSecret <必须>',
            'cert' => '申请ISV账号审核通过后下发的pem版证书，使用cjet_pri.pem文件 <必须>',
            'orgid' => '企业云账号 <非账套模式必须，即authmode=ecloud>',
            'authmode' => '认证模式 account-账套 ecloud-企业云账号模式',
            'account' => '账套账号配置 <account模式下必须>',
            'account_id' => '账套账号ID <account模式下必须>',
            'account_password' => '账套账号密码 <account模式下必须>',
            'account_number' => '账套编号 <account模式下必须>',
        ];
    }
}
