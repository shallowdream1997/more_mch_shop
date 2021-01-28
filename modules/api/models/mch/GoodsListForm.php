<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/22
 * Time: 14:18
 */


namespace app\modules\api\models\mch;

use app\models\Cat;
use app\models\Goods;
use app\models\GoodsCat;
use app\models\Mch;
use app\models\MchCat;
use app\models\MchGoodsCat;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use app\modules\api\models\ApiModel;
use yii\data\Pagination;

class GoodsListForm extends ApiModel
{
    public $mch_id;
    public $status;
    public $keyword;
    public $page;

    public $link;

    public $cat_id;

    public function rules()
    {
        return [
            ['keyword', 'trim'],
            [['link','cat_id','mch_id'], 'integer'],
//            ['mch_id', 'required'],
            ['status', 'default', 'value' => 1,],
            ['page', 'default', 'value' => 1,],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = Goods::find()->alias('g')->where(['g.mch_id' => $this->mch_id, 'g.is_delete' => 0,'g.status'=>1]);
        if ($this->link == 1){
            $query->andWhere(['!=','g.goods_num',0]);
        }
        if ($this->status == 1) {
            $query->andWhere(['g.status' => 1,])->andWhere(['!=','g.goods_num',0])->orderBy('g.addtime DESC');
        }
        if ($this->status == 2) {
            $query->andWhere(['g.goods_num' => 0,])->orderBy('g.mch_sort,g.addtime DESC');
        }
        if ($this->status == 3) {
            $query->andWhere(['g.status' => 0,])->orderBy('g.mch_sort,g.addtime DESC');
        }
        if ($this->status == 4) {
            $query->orderBy('g.addtime DESC');
        }
        if ($this->keyword) {
            $query->andWhere(['LIKE', 'g.name', $this->keyword,]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query
            ->leftJoin([
                'od' => OrderDetail::find()->alias('od')
                    ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                    ->select('SUM(od.num) AS sale_num,od.goods_id')->where(['od.is_delete' => 0, 'o.is_pay' => 1,])->groupBy('od.goods_id')
            ], 'od.goods_id=g.id')
            ->limit($pagination->limit)->offset($pagination->offset)
            ->select(['g.id', 'g.name', 'g.cover_pic', 'g.price', 'g.status', 'g.attr', 'IF(od.sale_num,od.sale_num,0) sale_num','g.service','g.labels'])->asArray()->all();
        foreach ($list as $i => $item) {
            if ($item['labels']){
                $list[$i]['labels'] = explode(',',$item['labels']);
            }else{
                $list[$i]['labels'] = [];
            }

            $m = new Goods();
            $m->id = $item['id'];
            $m->attr = $item['attr'];
            $list[$i]['goods_num'] = $m->getNum();
            unset($list[$i]['attr']);

            $service_list = explode(",",$item['service']);
            if (!$item['service']){
                $option = Option::get('good_services', $this->store->id, 'admin', []);
                foreach ($option as $i) {
                    if ($i['is_default'] == 1) {
                        $service_list = explode(',', $i['service']);
                        break;
                    }
                }
            }
            $new_service_list = [];
            if (is_array($service_list)) {
                foreach ($service_list as $i2) {
                    $i2 = trim($i2);
                    if ($i2) {
                        $new_service_list[] = $i2;
                    }
                }
            }

            $list[$i]['service_list'] = $new_service_list;
        }
        return [
            'code' => 0,
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }

    public function catsgoodslist()
    {
        if (!$this->validate())
            return $this->errorResponse;
        $query = Goods::find()->alias('g')->where([
            'g.status' => 1,
            'g.is_delete' => 0,
            'g.type' => get_plugin_type(),
            'g.mch_id' => 0,
        ])->leftJoin(['m' => Mch::tableName()], 'm.id=g.mch_id')
            ->andWhere([
                'or',
                ['g.mch_id' => 0],
                ['m.is_delete' => 0]
            ]);
        if ($this->cat_id) {
            $cat = Cat::find()->select('id')->where(['is_delete' => 0])->andWhere(['OR', ['parent_id' => $this->cat_id], ['id' => $this->cat_id]])->column();
            $query->leftJoin(['gc' => GoodsCat::tableName()], 'gc.goods_id=g.id');
            $query->andWhere(
                [
                    'OR',
                    ['g.cat_id' => $cat],
                    ['gc.cat_id' => $cat],
                ]
            );
        }

        if ($this->keyword)
            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $count = $query->count();

        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 8, 'page' => $this->page - 1]);
        $query->orderBy('g.addtime DESC,mch_sort ASC');
        $list = $query
            ->select('g.id,g.name,g.price,g.cover_pic,g.service,g.labels')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->groupBy('g.id')->all();

//        dd($list->createCommand()->getRawSql());
        foreach ($list as $i => $goods) {
            if ($goods['labels']){
                $list[$i]['labels'] = explode(',',$goods['labels']);
            }else{
                $list[$i]['labels'] = [];
            }

            $service_list = explode(',', $goods['service']);
            // 默认商品服务
            if (!$goods->service) {
                $option = Option::get('good_services', $this->store->id, 'admin', []);
                foreach ($option as $item) {
                    if ($item['is_default'] == 1) {
                        $service_list = explode(',', $item['service']);
                        break;
                    }
                }
            }
            $new_service_list = [];
            if (is_array($service_list)) {
                foreach ($service_list as $item1) {
                    $item1 = trim($item1);
                    if ($item1) {
                        $new_service_list[] = $item1;
                    }
                }
            }
            $goods['service'] = $new_service_list;
        }
        $data = [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'list' => $list,
        ];

        return $data;
    }


    /**
     * @return mixed
     * 点击门店现货一级分类下所展示的布局 0-无 1-二级分类 2-分类广告
     */
    public function shopcatgoods()
    {
        if (!$this->validate())
            return $this->errorResponse;

        $query = Goods::find()->alias('g')->where(['g.mch_id' => $this->mch_id, 'g.is_delete' => 0,'g.status'=>1]);
        if ($this->keyword) {
            $query->andWhere(['LIKE', 'g.name', $this->keyword,]);
        }

        if ($this->cat_id) {
            $cat = MchCat::find()->select('id')->where(['is_delete' => 0,'mch_id' => $this->mch_id])->andWhere(['OR', ['parent_id' => $this->cat_id], ['id' => $this->cat_id]])->column();
            $query->leftJoin(['gc' => MchGoodsCat::tableName()], 'gc.goods_id=g.id');
            $query->andWhere(
                [
                    'OR',
                    ['g.cat_id' => $cat],
                    ['gc.cat_id' => $cat],
                ]
            );
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query
            ->leftJoin([
                'od' => OrderDetail::find()->alias('od')
                    ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                    ->select('SUM(od.num) AS sale_num,od.goods_id')->where(['od.is_delete' => 0, 'o.is_pay' => 1,])->groupBy('od.goods_id')
            ], 'od.goods_id=g.id')
            ->limit($pagination->limit)->offset($pagination->offset)
            ->select(['g.id', 'g.name', 'g.cover_pic', 'g.price', 'g.status', 'g.attr', 'IF(od.sale_num,od.sale_num,0) sale_num','g.service','g.labels'])->asArray()->all();

        foreach ($list as $i => $item) {
            if ($item['labels']){
                $list[$i]['labels'] = explode(',',$item['labels']);
            }else{
                $list[$i]['labels'] = [];
            }

            $m = new Goods();
            $m->id = $item['id'];
            $m->attr = $item['attr'];
            $list[$i]['goods_num'] = $m->getNum();
            unset($list[$i]['attr']);

            $service_list = explode(",",$item['service']);
            if (!$item['service']){
                $option = Option::get('good_services', $this->store->id, 'admin', []);
                foreach ($option as $i) {
                    if ($i['is_default'] == 1) {
                        $service_list = explode(',', $i['service']);
                        break;
                    }
                }
            }
            $new_service_list = [];
            if (is_array($service_list)) {
                foreach ($service_list as $i2) {
                    $i2 = trim($i2);
                    if ($i2) {
                        $new_service_list[] = $i2;
                    }
                }
            }

            $list[$i]['service_list'] = $new_service_list;
        }

        return [
            'code' => 0,
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }
}
