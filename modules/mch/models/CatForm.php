<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 11:01
 */

namespace app\modules\mch\models;

use app\models\Cat;
use yii\data\Pagination;

class CatForm extends MchModel
{
    public $cat;

    public $store_id;
    public $parent_id;
    public $name;
    public $pic_url;
    public $big_pic_url;
    public $sort;
    public $advert_pic;
    public $advert_url;
    public $is_show;
    public $is_search;
    public $individual_share;
    public $share_type;
    public $share_commission_first;
    public $share_commission_second;
    public $share_commission_third;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'store_id', 'parent_id'], 'required'],
            [['share_commission_first','share_commission_second','share_commission_third'],'number'],
            [['store_id', 'is_show','is_search','individual_share','share_type'], 'integer'],
            [['pic_url', 'big_pic_url', 'advert_pic', 'advert_url'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['sort'],'default','value'=>100],
            [['share_commission_first'],'default','value'=>0.00],
            [['share_commission_second'],'default','value'=>0.00],
            [['share_commission_third'],'default','value'=>0.00],
            [['sort'],'integer','min'=>0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '分类名称',
            'pic_url' => '分类图片url',
            'sort' => '排序',
            'advert_pic' => '广告图片',
            'advert_url' => '广告链接',
            'is_show' => '是否显示',
            'is_search' => '是否展示搜索查询',
            'individual_share' => '单独设置分销',
            'share_commission_first' => '一级分销佣金比例',
            'share_commission_second' => '二级分销佣金比例',
            'share_commission_third' => '三级分销佣金比例',
            'share_type' => '佣金配比',
        ];
    }

    /**
     * @param $store_id
     * @return array
     * 获取列表数据
     */
    public function getList($store_id)
    {
        $query = Cat::find()->andWhere(['is_delete' => 0, 'store_id' => $store_id]);
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query
            ->orderBy('sort ASC')
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
            $parent_cat_exist = true;
            if ($this->parent_id) {
                $parent_cat_exist = Cat::find()->where([
                    'id' => $this->parent_id,
                    'store_id' => $this->store_id,
                    'is_delete' => 0,
                ])->exists();
            }
            if (!$parent_cat_exist) {
                return [
                    'code' => 1,
                    'msg' => '上级分类不存在，请重新选择'
                ];
            }
            $cat = $this->cat;
            if ($cat->isNewRecord) {
                $cat->is_delete = 0;
                $cat->addtime = time();
            }
            $cat->attributes = $this->attributes;
            return $cat->saveCat();
        } else {
            return $this->errorResponse;
        }
    }
}
