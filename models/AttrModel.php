<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attr_model}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $model_name
 * @property integer $sort
 * @property string $model_comment
 * @property integer $is_use
 * @property string $model_group_json
 * @property integer $mch_id
 * @property integer $use_attr
 * @property integer $is_delete
 */
class AttrModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attr_model}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort', 'is_use', 'mch_id','store_id','use_attr','is_delete'], 'integer'],
            [['model_comment', 'model_group_json'], 'string'],
            [['model_name'], 'string', 'max' => 255],
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
            'model_name' => '模型名称',
            'sort' => '排序',
            'model_comment' => '模型说明',
            'is_use' => '是否开启（0-否 1-是）',
            'model_group_json' => '一级模型组对象json串',
            'mch_id' => '商户id',
            'use_attr' => '默认1，选择模型规格',
            'is_delete' => '是否删除 (0-否 1-是)',
        ];
    }

    // Model模型组
    public function getAttrData()
    {
        if ($this->isNewRecord) {
            return [];
        }
        if (!$this->model_group_json) {
            return [];
        }
        $attr_data = json_decode($this->model_group_json, true);

        return $attr_data;
    }

    public function getCheckedAttrData()
    {
//        if ($this->isNewRecord) {
//            return [];
//        }
//        if (!$this->attr) {
//            return [];
//        }
//        $attr_data = json_decode($this->attr, true);
//        foreach ($attr_data as $i => $attr_data_item) {
//            if (!isset($attr_data[$i]['no'])) {
//                $attr_data[$i]['no'] = '';
//            }
//            if (!isset($attr_data[$i]['pic'])) {
//                $attr_data[$i]['pic'] = '';
//            }
//            foreach ($attr_data[$i]['attr_list'] as $j => $attr_list) {
//                $attr_group = $this->getAttrGroupByAttId($attr_data[$i]['attr_list'][$j]['attr_id']);
//                $attr_data[$i]['attr_list'][$j]['attr_group_name'] = $attr_group ? $attr_group->attr_group_name : null;
//            }
//        }

//        return $attr_data;
    }

    private function getAttrGroupByAttId($att_id)
    {
        $cache_key = 'get_attr_group_by_attr_id_' . $att_id;
        $attr_group = Yii::$app->cache->get($cache_key);
        if ($attr_group) {
            return $attr_group;
        }
        $attr_group = AttrmodelGroup::find()->alias('ag')
            ->where(['ag.id' => AttrErModel::find()->select('attr_group_id')->distinct()->where(['id' => $att_id])])
            ->limit(1)->one();
        if (!$attr_group) {
            return $attr_group;
        }
        Yii::$app->cache->set($cache_key, $attr_group, 10);
        return $attr_group;
    }
}
