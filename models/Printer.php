<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%printer}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $name
 * @property string $printer_type
 * @property string $printer_setting
 * @property integer $is_delete
 * @property integer $addtime
 * @property integer $mch_id
 */
class Printer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%printer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'is_delete', 'addtime', 'mch_id'], 'integer'],
            [['printer_setting'], 'string'],
            [['name', 'printer_type'], 'string', 'max' => 255],
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
            'name' => '打印机名称',
            'printer_type' => '打印机类型',
            'printer_setting' => '打印机设置',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'mch_id' => '所属商户id',
        ];
    }

    public function beforeSave($insert)
    {
        $this->name = \yii\helpers\Html::encode($this->name);
        return parent::beforeSave($insert);
    }
}
