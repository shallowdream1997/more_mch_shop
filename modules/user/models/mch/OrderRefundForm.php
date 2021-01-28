<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/3
 * Time: 15:49
 */

namespace app\modules\user\models\mch;

use app\models\Goods;
use app\models\MsWechatTplMsgSender;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PtNoticeSender;
use app\models\User;
use app\models\UserAccountLog;
use app\models\WechatTplMsgSender;
use app\modules\user\models\UserModel;
use app\utils\Refund;
use app\utils\TplusApi;
use app\utils\TplusOption;
use yii\helpers\VarDumper;

class OrderRefundForm extends UserModel
{
    public $mch_id;
    public $store_id;
    public $order_refund_id;
    public $type;
    public $action;

    public $address_id;
    public $refund_price;
    public $refund;//是否退款
    public $orderType; //退款订单类型
    public $remark;

    public function rules()
    {
        return [
            [['store_id', 'order_refund_id', 'type', 'action'], 'required'],
            [['refund'], 'safe'],
            [['refund_price',], 'number', 'min' => 0.01,],
            [['address_id'], 'integer'],
            [['remark'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $order_refund = OrderRefund::findOne([
            'id' => $this->order_refund_id,
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ]);

        if (!$order_refund) {
            return [
                'code' => 1,
                'msg' => '售后订单不存在，请刷新页面'
            ];
        }
        if ($order_refund->status != 0) {
            return [
                'code' => 1,
                'msg' => '售后订单已经处理过了，请刷新页面',
                'id' => $order_refund->id,
            ];
        }
        if ($this->type == 1) {
            return $this->submit1($order_refund);
        }
        if ($this->type == 2) {
            return $this->submit2($order_refund);
        }
        if ($this->type == 3){
            return $this->submit3($order_refund);
        }
    }

    /**
     * 处理退货退款
     * @param OrderRefund $order_refund
     */
    private function submit1($order_refund)
    {
        $order = Order::findOne($order_refund->order_id);
        if ($this->action == 1) {//同意
            if ($this->refund != 1){ //仅同意还未退款
                if ($this->refund_price) {
                    if ($this->refund_price > $order_refund->refund_price) {
                        return [
                            'code' => 1,
                            'msg' => '退款金额不能大于' . $order_refund->refund_price,
                        ];
                    }
                    $order_refund->refund_price = $this->refund_price;
                }
                if (!$this->address_id) {
                    return [
                        'code' => 1,
                        'msg' => '退货地址不能为空',
                    ];
                };
                $order_refund->address_id = $this->address_id;
                $order_refund->is_agree = 1;
                $order_refund->save();

                if ($order_refund->save()) {

                    $msg_sender = new WechatTplMsgSender($this->store_id, $order_refund->order_id, $this->getWechat());
                    $msg_sender->refundMsg('0.00', $order_refund->goods->name, '卖家同意了您的退货请求,请尽快发货');

                    return [
                        'code' => 0,
                        'msg' => '已同意退货。',
                    ];
                }

                return $this->getErrorResponse($order_refund);
            }else{
                $order_refund->status = 1;
                $order_refund->response_time = time();
                if ($order_refund->refund_price > 0 && $order->pay_type == 1) {
                    $res = Refund::refund($order,$order_refund->order_refund_no,$order_refund->refund_price);
                    if($res !== true){
                        return $res;
                    }
                }
                // 用户积分恢复
                $integral = json_decode($order->integral)->forehead_integral;
                $user = User::findOne(['id' => $order->user_id]);
                if ($integral > 0) {
                    $user->integral += $integral;
                }
                if ($order_refund->refund_price > 0 && $order->pay_type == 3) {
                    $user = User::findOne(['id'=>$order->user_id]);
                    $user->money += floatval($order_refund->refund_price);
                    $log = new UserAccountLog();
                    $log->user_id = $user->id;
                    $log->price = $order_refund->refund_price;
                    $log->type = 1;
                    $log->desc = "商户商城售后订单退款：订单号（{$order_refund->order_refund_no}）";
                    $log->addtime = time();
                    $log->order_id = $order->id;
                    $log->order_type = 4;
                    $log->save();
                }
                if (!$user->save()) {
                    return [
                        'code'=>1,
                        'msg'=>$this->getErrorResponse($user)
                    ];
                }
                if ($order_refund->save()) {
                    $order->is_new_sale = 1;
                    $order->save();
                    $msg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
                    $msg_sender->refundMsg($order_refund->refund_price, $order_refund->desc, '退款已完成');


                    if ($this->store->is_open_isv){
                        /*
                         *
                         * 销售订单退货退款
                         *
                         * */
                        $yyisv = new TplusOption();
                        $od = OrderDetail::find()->alias('od')->leftJoin(['g'=>Goods::tableName()],'g.id=od.goods_id')
                            ->where(['od.order_id'=>$order->id,'od.is_delete'=>0])->select('goods_id,num')->asArray()->all();
                        //商品价格 total_price
                        $SaleOrderDetails = [];
                        foreach ($od as $i => $k){
                            //获取存货编码，这里写死一个测试
                            $SaleOrderDetails[$i]['Inventory']        = ['Code'=>'100145'];
                            //计量单位信息
                            $SaleOrderDetails[$i]['Unit']             = ['Name'=>$k['unit']];
                            //数量，decimal类型
                            $SaleOrderDetails[$i]['Quantity']         = '-'.$k['num'];
                            //价格
                            $SaleOrderDetails[$i]['OrigPrice']        = $k['total_price'];//商品价格 total_price
                            //换算率，decimal类型
                            $SaleOrderDetails[$i]['UnitExchangeRate'] = (5 / 100) ;
                            //税率,decimal类型 OrigTaxPrice
                            $SaleOrderDetails[$i]['TaxRate']          = (5 / 100);
                            $SaleOrderDetails[$i]['OrigTaxPrice']     = (5 / 100) * $k['total_price'];
                            $SaleOrderDetails[$i]['OrigDiscountAmount'] = (5 / 100) * $k['total_price'];
                            $SaleOrderDetails[$i]['OrigTax'] = (5 / 100) * $k['total_price'];
                            $SaleOrderDetails[$i]['OrigTaxAmount'] = (5 / 100) * $k['total_price'];
                            //折扣率,decimal类型 OrigDiscountPrice
                            $SaleOrderDetails[$i]['DiscountRate']     = (5 / 100);
                            $SaleOrderDetails[$i]['OrigDiscountPrice']= (5 / 100) * $k['total_price'];
                        }
                        $apiParm = [
                            '_args' => json_encode([
                                'dto' => [
                                    'VoucherDate'      => date("Y-m-d",time()),
                                    'ExternalCode'     => $order->order_no, // 外部系统单据编码，编码必须唯一，且此字段不为空
                                    'Customer'         => ['Code' => 'AH08003-1'], // 客户编码 此编码要与T+系统客户编码一致 AH08003-1 AH08005-1 AH08006-1 AH10001-1
                                    'Address'          => $order->address,
                                    'LinkMan'          => $order->name,
                                    'ContactPhone'     => $order->mobile,
                                    'Memo'             => $order->remark,
                                    'ExchangeRate'     => (5 / 100),//汇率，decimal类型
                                    'SaleOrderDetails' => $SaleOrderDetails,
                                ],
                            ], JSON_UNESCAPED_UNICODE),
                        ];
                        $argList = $yyisv::Options('/saleOrder/Create',$apiParm);
                    }


                    return [
                        'code' => 0,
                        'msg' => '处理成功，已完成退款退货。',
                    ];
                }
                return $this->getErrorResponse($order_refund);
            }
        }
        if ($this->action == 2) {//拒绝
            $order_refund->status = 3;
            $order_refund->response_time = time();
            if ($order_refund->save()) {
                $msg_sender = new WechatTplMsgSender($this->store_id, $order_refund->order_id, $this->getWechat());
                $msg_sender->refundMsg('0.00', $order_refund->desc, '卖家拒绝了您的退货请求');
                return [
                    'code' => 0,
                    'msg' => '处理成功，已拒绝该退货退款订单。',
                ];
            }
            return $this->getErrorResponse($order_refund);
        }
    }

    /**
     * 处理换货
     * @param OrderRefund $order_refund
     */
    private function submit2($order_refund)
    {
        if ($this->action == 1) {//同意
            $order_refund->status = 2;
            $order_refund->response_time = time();
            if ($order_refund->save()) {
                $msg_sender = new WechatTplMsgSender($this->store_id, $order_refund->order_id, $this->getWechat());
                $msg_sender->refundMsg('0.00', $order_refund->desc, '卖家已同意换货，换货无退款金额');
                return [
                    'code' => 0,
                    'msg' => '处理成功，已同意换货。',
                ];
            }
            return $this->getErrorResponse($order_refund);
        }
        if ($this->action == 2) {//拒绝
            $order_refund->status = 3;
            $order_refund->response_time = time();
            if ($order_refund->save()) {
                $msg_sender = new WechatTplMsgSender($this->store_id, $order_refund->order_id, $this->getWechat());
                $msg_sender->refundMsg('0.00', $order_refund->desc, '卖家已拒绝您的换货请求');
                return [
                    'code' => 0,
                    'msg' => '处理成功，已拒绝换货请求。',
                ];
            }
            return $this->getErrorResponse($order_refund);
        }
    }

    /**
     * 处理退款
     * @param OrderRefund $order_refund
     */
    private function submit3($order_refund)
    {
        $order = Order::findOne($order_refund->order_id);
        if ($this->action == 1) {//同意
            $order_refund->status = 4; //已同意退款
            $order_refund->response_time = time();
            if ($order_refund->refund_price > 0 && $order->pay_type == 1) {
                $res = Refund::refund($order,$order_refund->order_refund_no,$order_refund->refund_price);
                if($res !== true){
                    return $res;
                }
            }

            // 用户积分恢复
            $integral = json_decode($order->integral)->forehead_integral;
            $user = User::findOne(['id' => $order->user_id]);
            if ($integral > 0) {
                $user->integral += $integral;
            }
            if ($order_refund->refund_price > 0 && $order->pay_type == 3) {
                $user = User::findOne(['id'=>$order->user_id]);
                $user->money += floatval($order_refund->refund_price);
                $log = new UserAccountLog();
                $log->user_id = $user->id;
                $log->price = $order_refund->refund_price;
                $log->type = 1;
                $log->desc = "商户商城售后订单退款：订单号（{$order_refund->order_refund_no}）";
                $log->addtime = time();
                $log->order_id = $order->id;
                $log->order_type = 4;
                $log->save();
            }
            if (!$user->save()) {
                return [
                    'code'=>1,
                    'msg'=>$this->getErrorResponse($user)
                ];
            }
            if ($order_refund->save()) {
                $order->is_new_sale = 1;
                $order->save();
                $msg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
                $msg_sender->refundMsg($order_refund->refund_price, $order_refund->desc, '退款已完成');
                return [
                    'code' => 0,
                    'msg' => '处理成功，已完成退款。',
                ];
            }
            return $this->getErrorResponse($order_refund);
        }
        if ($this->action == 2) {//拒绝
            $order_refund->status = 3;
            $order_refund->response_time = time();
            if ($order_refund->save()) {
                $msg_sender = new WechatTplMsgSender($this->store_id, $order_refund->order_id, $this->getWechat());
                $msg_sender->refundMsg('0.00', $order_refund->desc, '卖家拒绝了您的退货请求');
                return [
                    'code' => 0,
                    'msg' => '处理成功，已拒绝该退款订单。',
                ];
            }
            return $this->getErrorResponse($order_refund);
        }
    }

}
