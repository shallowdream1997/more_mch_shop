<?php
namespace app\modules\api\models\mch;

use app\models\Cat;
use app\models\Coupon;
use app\models\Goods;
use app\models\MchCat;
use app\models\UserCoupon;
use app\modules\api\models\ApiModel;

class CouponListForm extends ApiModel
{
    public $mch_id;
    public $sort;
    public $page;
    public $limit;
    public $cat_id;
    public $store_id;

    public $user_id;

    public function rules()
    {
        return [
            ['mch_id', 'required'],
            [['mch_id', 'cat_id', 'store_id','user_id'], 'integer'],
            ['sort', 'safe'],
            ['page', 'default', 'value' => 1,],
            ['limit', 'default', 'value' => 20,],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mch_id' => 'mch_id',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $coupon_list = Coupon::find()->alias('c')->where([
            'c.is_delete' => 0, 'c.is_join' => 2, 'c.store_id' => $this->store->id, 'c.mch_id' => $this->mch_id
        ])
            ->andWhere(['!=', 'c.total_count', 0])
            ->leftJoin(UserCoupon::tableName() . ' uc', "uc.coupon_id=c.id and uc.user_id ={$this->user_id} and uc.type = 2 and uc.is_delete=0 and uc.mch_id ={$this->mch_id}")->select([
                'c.*', '(case when isnull(uc.id) then 0 else 1 end) as is_receive'
            ])
            ->orderBy('is_receive ASC,sort ASC,id DESC')->asArray()->all();

        $new_list = [];
        foreach ($coupon_list as $index => $value) {
            if ($value['min_price'] >= 100) {
                $coupon_list[$index]['min_price'] = (int)$value['min_price'];
            }
            if ($value['sub_price'] >= 100) {
                $coupon_list[$index]['sub_price'] = (int)$value['sub_price'];
            }
            $coupon_list[$index]['begintime'] = date('Y.m.d', $value['begin_time']);
            $coupon_list[$index]['endtime'] = date('Y.m.d', $value['end_time']);
            $coupon_list[$index]['content'] = "适用范围：全场通用";
            if ($value['appoint_type'] == 1 && $value['cat_id_list'] !== 'null') {
                $coupon_list[$index]['cat'] = MchCat::find()->select('id,name')->where(['is_delete'=>0,'mch_id'=>$this->mch_id,'id'=>json_decode($value['cat_id_list'])])->asArray()->all();
                $cat_list = [];
                foreach ($coupon_list[$index]['cat'] as $item) {
                    $cat_list[] = $item['name'];
                }
                $coupon_list[$index]['content'] = "适用范围：仅限分类：".implode('、', $cat_list)."使用";
                $coupon_list[$index]['goods'] = [];
            } elseif ($value['appoint_type'] == 2 && $value['goods_id_list'] !== 'null') {
                $coupon_list[$index]['goods'] = Goods::find()->select('id')->where(['store_id'=>$this->store_id,'is_delete'=>0,'id'=>json_decode($value['goods_id_list'])])->asArray()->all();
                $coupon_list[$index]['cat'] = [];
                $coupon_list[$index]['content'] = "指定商品使用 点击查看指定商品";
            } else {
                $coupon_list[$index]['goods'] = [];
                $coupon_list[$index]['cat'] = [];
            }
            if($value['is_receive'] == 0){
                $coupon_list[$index]['receive_content'] = '立即领取';
            }else{
                $coupon_list[$index]['receive_content'] = '已领取';
            }

            $coupon_count = UserCoupon::find()->where([
                'store_id'=>$this->store_id,'is_delete'=>0,'coupon_id'=>$value['id'],'type'=>2,'mch_id' => $this->mch_id
            ])->count();
            if ($value['total_count'] > $coupon_count || $value['total_count'] == -1) {
                if ($value['expire_type'] == 2) {
                    if ($value['end_time'] >= time()) {
                        $new_list[] = $coupon_list[$index];
                    }
                } else {
                    $new_list[] = $coupon_list[$index];
                }
            }
        }
        return [
            'code' => 0,
            'data' => [
                'mchcoupon_list' => $new_list,
            ],
        ];
    }


}
