<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%account_mch}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $username
 * @property string $password
 * @property string $auth_key
 * @property string $access_token
 * @property string $update_time
 * @property integer $is_delete
 * @property string $mch_json
 */
class AccountMch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_mch}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'is_delete'], 'integer'],
            [['username', 'password', 'auth_key', 'access_token'], 'required'],
            [['update_time'], 'safe'],
            [['mch_json'], 'string'],
            [['username', 'password', 'auth_key', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => '商城id',
            'username' => 'Username',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'update_time' => 'Update Time',
            'is_delete' => 'Is Delete',
            'mch_json' => '账户所属门店json列表',
        ];
    }
}
