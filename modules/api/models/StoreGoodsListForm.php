<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/1
 * Time: 23:33
 */

namespace app\modules\api\models;


use app\hejiang\ApiResponse;
use app\models\Cat;
use app\models\Goods;
use app\models\GoodsCat;
use app\models\GoodsPic;
use app\models\Mch;
use app\models\MchCat;
use app\models\MchGoodsCat;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use yii\data\Pagination;

class StoreGoodsListForm extends ApiModel
{
    public $store_id;
    public $keyword;
    public $cat_id;
    public $page;
    public $limit;

    public $goods_id;
    public $pic_url;
    public $recommend_count;

    public $mch_id;

    public function rules()
    {
        return [
            [['keyword','mch_id'], 'trim'],
            [['store_id', 'cat_id', 'page', 'limit'], 'integer'],
            [['limit'], 'integer',],
            [['limit',], 'default', 'value' => 12],
            [['goods_id',], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->errorResponse;
        $query = Goods::find()->alias('g')->where([
            'g.status' => 1,
            'g.is_delete' => 0,
            'g.type' => get_plugin_type(),
            'g.mch_id' => $this->mch_id,
        ]);
        if ($this->cat_id) {
            $cat = MchCat::find()->select('id')->where(['is_delete' => 0,'mch_id'=>$this->mch_id])->andWhere(['OR', ['parent_id' => $this->cat_id], ['id' => $this->cat_id]])->column();
            $query->leftJoin(['gc' => MchGoodsCat::tableName()], 'gc.goods_id=g.id');
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

        return new ApiResponse(0, 'success', $data);
    }

    public function recommend()
    {
        if (!$this->validate())
            return $this->errorResponse;
        $goods_id = $this->goods_id;
        if (!$goods_id) {
            return new ApiResponse(1, 'error');
        }
        $cat_ids = [];

        $goods = Goods::find()->select('*')->where(['store_id' => $this->store_id, 'is_delete' => 0, 'type' => get_plugin_type()])->andWhere('id=:id', [':id' => $goods_id])->one();
        $cat_id = $goods->cat_id;

        if ($cat_id == 0) {
            $goodsCat = GoodsCat::find()->select('cat_id')->where(['store_id' => $this->store_id, 'goods_id' => $goods_id, 'is_delete' => 0])->all();
            $goods_cat = [];
            foreach ($goodsCat as $v) {
                $goods_cat[] = $v->cat_id;
            }
        } else {
            $goods_cat = array(intval($cat_id));
        }
        $cat_ids = $goods_cat;
        // $cat1 = Cat::find()->select(['id','parent_id'])->where(['store_id' =>$this->store_id,'is_delete' => 0])->andWhere(['in','id',$goods_cat])->all();
        // $parents=[];
        // foreach($cat1 as $v){
        //     if($v->parent_id===0){
        //         $cat_ids[] = $v->id;
        //     }else{
        //         $parents[] = $v->parent_id;
        //     }
        // };
        // $cat2 = Cat::find()->select('id')->where(['store_id' =>$this->store_id,'is_delete' => 0])->andWhere(['in','id',$parents])->all();
        // foreach($cat2 as $v){
        //     $cat_ids[] = $v->id;
        // }

        // $cat_list = Cat::find()->select('id')->where(['store_id'=>$this->store_id,'is_delete'=>0])->andWhere(['in','parent_id',$cat_ids])->all();
        // foreach($cat_list as $v){
        //     $cat_ids[] =$v->id;
        // }
        //查询
        $goodscat_list = GoodsCat::find()->select(['goods_id'])->where(['store_id' => $this->store_id, 'is_delete' => 0])->andWhere(['in', 'cat_id', $cat_ids])->all();

        $cats = [];
        foreach ($goodscat_list as $v) {
            $cats[] = $v->goods_id;
        }

        $query = Goods::find()->alias('g')
            ->where(['and', "g.id!=$goods_id", 'cat_id=0', "g.store_id=$this->store_id", 'g.is_delete=0', 'g.status=1', ['in', 'g.id', $cats]])
            ->orWhere(['and', "g.id!=$goods_id", "g.store_id=$this->store_id", 'g.is_delete=0', 'g.status=1', ['in', 'g.cat_id', $cat_ids]])
            ->andWhere(['g.type' => get_plugin_type()])
            ->leftJoin(['m' => Mch::tableName()], 'm.id=g.mch_id')
            ->andWhere([
                'or',
                ['g.mch_id' => 0],
                ['m.is_delete' => 0]
            ]);

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);

        $query->orderBy('g.sort ASC');

        $od_query = OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where(['od.is_delete' => 0, 'o.store_id' => $this->store_id, 'o.is_pay' => 1, 'o.is_delete' => 0])->groupBy('od.goods_id')->select('SUM(od.num) num,od.goods_id');


        $limit = $pagination->limit;
        $offset = $pagination->offset;
        $recommend_count = $this->recommend_count;
        if ($offset > $recommend_count) {
            return new ApiResponse(1, 'error');
        } else if ($offset + $limit > $recommend_count) {
            $limit = $recommend_count - $offset;
        }

        $list = $query
            ->leftJoin(['gn' => $od_query], 'gn.goods_id=g.id')
            ->select('g.id,g.name,g.price,g.original_price,g.cover_pic pic_url,gn.num,g.virtual_sales,g.unit,g.is_negotiable')
            ->limit($limit)
            ->offset($pagination->offset)
            ->asArray()->groupBy('g.id')->all();

        foreach ($list as $i => $item) {
            if (!$item['pic_url']) {
                $list[$i]['pic_url'] = Goods::getGoodsPicStatic($item['id'])->pic_url;
            }
            $list[$i]['sales'] = $this->numToW($item['num'] + $item['virtual_sales']) . $item['unit'];

        }
        $data = [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'list' => $list,
        ];
        return new ApiResponse(0, 'success', $data);
    }

    private function numToW($sales)
    {
        if ($sales < 10000) {
            return $sales;
        } else {
            return round($sales / 10000, 2) . 'W';
        }
    }

    public function couponSearch()
    {
//        ,'name','price','original_price','pic_url','num','virtual_sales','unit'
        $arr = explode(",", $this->goods_id);

        $query = Goods::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])->andWhere(['in', 'id', $arr]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);

        if ($this->sort == 0) {
            //综合，自定义排序+时间最新
            $query->orderBy('sort ASC,addtime DESC');
        }
        if ($this->sort == 1) {
            //时间最新
            $query->orderBy('addtime DESC');
        }
        if ($this->sort == 2) {
            //价格
            if ($this->sort_type == 0) {
                $query->orderBy('price ASC');
            } else {
                $query->orderBy('price DESC');
            }
        }
        if ($this->sort == 3) {
            //销量
            $query->orderBy([
                'virtual_sales' => SORT_DESC,
                'addtime' => SORT_DESC,
            ]);
        }
        $list = $query
            ->select(['id', 'name', 'cover_pic as pic_url', 'price', 'original_price', 'virtual_sales as sales', 'unit'])
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }

}
