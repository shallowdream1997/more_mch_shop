<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%refund_reson}}".
 *
 * @property integer $id
 * @property string $refund_reason
 * @property string $refund_status
 * @property integer $is_delete
 */
class RefundReson extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%refund_reson}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_delete'], 'integer'],
            [['refund_reason', 'refund_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'refund_reason' => '退款原因',
            'refund_status' => '货物状态',
            'is_delete' => '是否删除 （0-否 1-是）',
        ];
    }

    public static function getRefundReason()
    {
        $reason = RefundReson::find()->where(['is_delete'=>0])->select('refund_reason')->asArray()->all();
        if (!$reason){
            return [];
        }
        return $reason;
    }

}
