<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mch_auth_login}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $mch_id
 * @property integer $account_id
 * @property integer $account_shop_id
 * @property string $binding
 * @property integer $is_default
 * @property integer $addtime
 * @property string $name
 * @property string $update_at
 */
class MchAuthLogin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mch_auth_login}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'user_id', 'mch_id', 'account_id', 'account_shop_id', 'is_default', 'addtime'], 'integer'],
            [['name'], 'required'],
            [['binding'], 'string', 'max' => 45],
            [['name'], 'string', 'max' => 255],
            [['update_at'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'user_id' => 'User ID',
            'mch_id' => '门店id',
            'account_id' => '账户id',
            'account_shop_id' => '一小时门店id',
            'binding' => '手机号',
            'is_default' => '是否已确认登录：0=未登陆，1=已确认登录',
            'addtime' => 'Addtime',
            'name' => 'Name',
            'update_at' => '时间更新',
        ];
    }

    public function getMch()
    {
        return $this->hasOne(Mch::className(),['id' => 'mch_id']);
    }
}
