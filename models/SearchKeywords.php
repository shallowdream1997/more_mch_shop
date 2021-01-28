<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%search_keywords}}".
 *
 * @property integer $id
 * @property string $keywords
 * @property integer $is_show
 * @property integer $is_delete
 */
class SearchKeywords extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%search_keywords}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_show', 'is_delete'], 'integer'],
            [['keywords'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keywords' => '关键词',
            'is_show' => '是否显示',
            'is_delete' => '是否删除',
        ];
    }
}
