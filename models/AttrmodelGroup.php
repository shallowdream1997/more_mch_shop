<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attrmodel_group}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $attr_group_name
 * @property integer $is_delete
 */
class AttrmodelGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attrmodel_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'attr_group_name'], 'required'],
            [['store_id', 'is_delete'], 'integer'],
            [['attr_group_name'], 'string', 'max' => 255],
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
            'attr_group_name' => 'Attr Group Name',
            'is_delete' => 'Is Delete',
        ];
    }
}
