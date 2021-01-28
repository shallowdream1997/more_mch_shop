<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%member_id}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $mch_id
 * @property integer $is_delete
 */
class MemberId extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_id}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'mch_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'mch_id' => 'Mch门店id',
            'is_delete' => '是否删除',
        ];
    }
}
