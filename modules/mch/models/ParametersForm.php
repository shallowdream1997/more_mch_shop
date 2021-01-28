<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/8/7
 * Time: 12:59
 */

namespace app\modules\mch\models;

use app\models\Attr;
use app\models\AttrGroup;
use app\models\Goods;
use app\modules\mch\events\goods\BaseAddGoodsEvent;
use Hejiang\Event\EventArgument;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ParametersForm extends MchModel
{
    public $goods;

    public $store_id;
    public $modelname;
    public $model_comment;
    public $sort;
    public $attr;
    public $model_group_name;
    public $model_name;
    public $is_use;
    public $models;
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['model_name'], 'trim'],
            [['store_id', 'modelname','model_group_name','model_name','is_use'], 'required'],
            [['model_comment'], 'string'],
            [['modelname'], 'string', 'max' => 255],
            [['sort'], 'default', 'value' => 1000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'name' => '商品名称',
            'detail' => '图文详情',
            'status' => '上架状态：0=下架，1=上架',
            'sort' => '排序',
        ];
    }

    /**
     *
     */
    public function getList($store_id)
    {
        $query = Goods::find()
            ->alias('g')
            ->andWhere(['g.is_delete' => 0, 'g.store_id' => $store_id]);
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);

        $list = $query
            ->select(['g.*', 'c.name AS cname'])
            ->leftJoin('{{%cat}} c', 'g.cat_id=c.id')
            ->orderBy('g.sort ASC')
            ->offset($p->offset)
            ->limit($p->limit)
            ->asArray()
            ->all();
        return [$list, $p];
    }

    /**
     * 编辑
     * @return array
     */
    public function save()
    {
        if ($this->validate()) {
            if (count($this->model_group_name) == 0){
                return [
                    'code' => 1,
                    'msg' => '规格模型不能为空'
                ];
            }
            if (count($this->model_name) == 0){
                return [
                    'code' => 1,
                    'msg' => '规格模型不能为空'
                ];
            }
            if (count($this->model_name) != count($this->model_group_name))
            {
                return [
                    'code' => 1,
                    'msg' => '有遗漏的二级规格未填写'
                ];
            }
//            dd($this->attr_name);
            $models = $this->models;
            $_this_attributes = $this->attributes;
            unset($_this_attributes['attr']);
            $models->attributes = $_this_attributes;
            $t = \Yii::$app->db->beginTransaction();
            $models->store_id = $this->store_id;
            $models->model_name = $this->modelname;
            $models->sort = $this->sort;
            $models->model_comment = $this->model_comment;
            $models->is_use = $this->is_use;

            $attr_group_list = [];
            foreach ($this->model_group_name as $i => $k){
                $attr_group_list[$i]['model_group_name'] = $k;
                $attr_group_list[$i]['model_list'] = $this->model_name[$i];
            }
//            dd($attr_group_list);
            $object = json_encode($attr_group_list);
            $models->mch_id = 0;
            $models->model_group_json = $object;
            $models->use_attr = 1;
            if ($models->save()) {
//                $results = $this->setAttr($goods);
//                if ($results){
//                    $t->commit();
//                    return [
//                        'code' => 0,
//                        'msg' => '保存成功'
//                    ];
//                }else{
//                    $t->rollBack();
//                    return [
//                        'code' => 1,
//                        'msg' => '保存失败'
//                    ];
//                }
                return [
                        'code' => 0,
                        'msg' => '保存成功'
                    ];
            } else {
//                $t->rollBack();
                return $this->getErrorResponse($models);
            }
        } else {
            return $this->errorResponse;
        }
    }

    /**
     * @param Goods $goods
     */
    private function setAttr($goods)
    {
        $new_attr = [];
        foreach ($this->attributes as $i => $item) {
            foreach ($item['attr_list'] as $a) {
                $attr_group_model = AttrGroup::findOne(['store_id' => $this->store_id, 'attr_group_name' => $a['attr_group_name'], 'is_delete' => 0]);
                if (!$attr_group_model) {
                    $attr_group_model = new AttrGroup();
                    $attr_group_model->attr_group_name = $a['attr_group_name'];
                    $attr_group_model->store_id = $this->store_id;
                    $attr_group_model->is_delete = 0;
                    $attr_group_model->save();
                }
                $attr_model = Attr::findOne(['attr_group_id' => $attr_group_model->id, 'attr_name' => $a['attr_name'], 'is_delete' => 0]);
                if (!$attr_model) {
                    $attr_model = new Attr();
                    $attr_model->attr_name = $a['attr_name'];
                    $attr_model->attr_group_id = $attr_group_model->id;
                    $attr_model->is_delete = 0;
                    $attr_model->save();
                }
                $new_attr_item['attr_list'][] = [
                    'attr_id' => $attr_model->id,
                    'attr_name' => $attr_model->attr_name,
                ];
            }
            $new_attr[] = $new_attr_item;
        }

        $goods->model_group_json = \Yii::$app->serializer->encode($new_attr);
        $goods->save();
    }


    /**
     * @return array
     */
    private function getDefaultAttr()
    {
        $default_attr_name = '默认';
        $default_attr_group_name = '规格';
        $attr = Attr::findOne([
            'attr_name' => $default_attr_name,
            'is_delete' => 0,
            'is_default' => 1,
        ]);
        $attr_group = null;
        if (!$attr) {
            $attr_group = AttrGroup::findOne([
                'attr_group_name' => $default_attr_group_name,
                'is_delete' => 0,
            ]);
            if (!$attr_group) {
                $attr_group = new AttrGroup();
                $attr_group->store_id = $this->store_id;
                $attr_group->attr_group_name = $default_attr_group_name;
                $attr_group->is_delete = 0;
                $attr_group->save(false);
            }
            $attr = new Attr();
            $attr->attr_group_id = $attr_group->id;
            $attr->attr_name = $default_attr_name;
            $attr->is_delete = 0;
            $attr->is_default = 1;
            $attr->save(false);
        } else {
            $attr_group = AttrGroup::findOne($attr->attr_group_id);
        }
        return [$attr, $attr_group];
    }
}
