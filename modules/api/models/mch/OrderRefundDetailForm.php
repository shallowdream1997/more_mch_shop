<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/5/17
 * Time: 15:21
 */


namespace app\modules\api\models\mch;

use app\models\Goods;
use app\models\Mch;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\RefundAddress;
use app\modules\api\models\ApiModel;

class OrderRefundDetailForm extends ApiModel
{
    public $id;
    public $mch_id;
    public static $status_text_list = [
        '0' => '待处理',
        '1' => '已同意退货退款',
        '2' => '已同意换货',
        '3' => '已拒绝',
        '4' => '已退款',
    ];

    public static $status_text_list_one = [
        '0' => '已同意退货等待退款',
        '1' => '已同意退货退款',
        '3' => '已拒绝'
    ];

    public function rules()
    {
        return [
            ['id', 'required',],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $query = OrderRefund::find()->alias('or')
            ->leftJoin(['o' => Order::tableName()], 'or.order_id=o.id')
            ->leftJoin([
                'od' => OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')->select('od.id,od.total_price,od.goods_id,od.attr,od.num,g.name,g.cover_pic')
            ], 'or.order_detail_id=od.id')
            ->where([
                'or.id' => $this->id,
                'o.mch_id' => $this->mch_id,
            ]);
        $item = $query
            ->select([
                'o.order_no', 'o.addtime order_time', 'o.name username', 'o.mobile', 'o.pay_price', 'o.address', 'o.mch_id',
                'od.name', 'od.cover_pic', 'od.attr', 'od.num', 'od.total_price',
                'or.id', 'or.order_refund_no', 'or.refund_price', 'or.addtime order_refund_time', 'or.desc', 'or.type', 'or.pic_list', 'or.status', 'or.refuse_desc', 'or.refund_reson' ,'or.refund_status','or.is_agree','or.is_user_send','or.user_send_express','or.user_send_express_no'
            ])
            ->asArray()->one();
        if (!$item) {
            return [
                'code' => 1,
                'msg' => '订单不存在。',
            ];
        }
        $item['refund_order'] = true;
        $item['attr'] = json_decode($item['attr'], true);
        $item['pic_list'] = json_decode($item['pic_list'], true);
        $item['order_time'] = date('Y-m-d H:i', $item['order_time']);
        $item['order_refund_time'] = date('Y-m-d H:i', $item['order_refund_time']);
        if ($item['type'] == 1){
            $item['refund_type'] = '退货退款';
        }elseif($item['type'] == 2){
            $item['refund_type'] = '换货';
        }elseif ($item['type'] == 3){
            $item['refund_type'] = '退款';
        }
        if ($item['is_agree'] == 1){
            $item['status_text'] = self::$status_text_list_one[$item['status']];
        }else{
            $item['status_text'] = self::$status_text_list[$item['status']];
        }

        $mch_list = Mch::getMchName($this->mch_id);
        $item['mch_name'] = $mch_list->name;
        $refund_address = RefundAddress::find()->where(['is_delete'=>0,'store_id'=>\Yii::$app->store->id])->select('id,name,address,mobile')->asArray()->all();
        return [
            'code' => 0,
            'refund_address_list' => $refund_address,
            'data' => $item,
        ];
    }
}
