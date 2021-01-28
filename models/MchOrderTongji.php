<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mch_order_tongji}}".
 *
 * @property integer $id
 * @property integer $mch_id
 * @property integer $year
 * @property integer $month
 * @property string $month_order_sum
 */
class MchOrderTongji extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mch_order_tongji}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mch_id', 'year', 'month'], 'integer'],
            [['month_order_sum'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mch_id' => '门店id',
            'year' => '年',
            'month' => '月',
            'month_order_sum' => '月总额',
        ];
    }
}
