<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%anhour_district}}".
 *
 * @property integer $id
 * @property integer $did
 * @property string $name
 * @property integer $type
 * @property integer $parent_id
 */
class AnhourDistrict extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%anhour_district}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['did', 'type', 'parent_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'did' => '地区id',
            'name' => '地区名称',
            'type' => '类别(0-无 1-省 2-市 3-区)',
            'parent_id' => '父类',
        ];
    }
}
