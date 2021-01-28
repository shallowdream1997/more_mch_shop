<?php

namespace app\models;

use app\models\common\admin\log\CommonActionLog;
use Yii;

/**
 * This is the model class for table "{{%mch_cat}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $mch_id
 * @property string $name
 * @property string $icon
 * @property integer $sort
 * @property string $advert_pic
 * @property string $advert_url
 * @property integer $type
 * @property integer $is_delete
 * @property integer $addtime
 */
class MchCat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mch_cat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'mch_id', 'sort', 'type', 'is_delete', 'addtime'], 'integer'],
            [['icon', 'advert_pic', 'advert_url'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'mch_id' => 'Mch ID',
            'name' => '分类名称',
            'icon' => '分类图标',
            'sort' => 'Sort',
            'advert_pic' => '广告图片',
            'advert_url' => '广告链接',
            'type' => '分类页面展示方式 0-无 1-带二级分类 2-带广告',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }

    public function getChildrenList()
    {
        return $this->hasMany(MchCat::className(), ['parent_id' => 'id'])->where(['is_delete'=>0,]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $data = $insert ? json_encode($this->attributes) : json_encode($changedAttributes);
        CommonActionLog::storeActionLog('', $insert, $this->is_delete, $data, $this->id);
    }
}
