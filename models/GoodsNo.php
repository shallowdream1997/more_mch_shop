<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_no}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property string $no
 */
class GoodsNo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_no}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['no'], 'required'],
            [['no'], 'string', 'max' => 255],
            [['no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品ID',
            'no' => 'sku编号（做查重使用）',
        ];
    }
}
