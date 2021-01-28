<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/6/23 17:36
 */


namespace app\modules\api\models;

use app\models\common\CommonFormId;
use app\models\MsOrderRefund;
use app\models\Order;
use app\models\OrderMessage;
use app\models\OrderRefund;
use app\models\PtOrderRefund;
use app\modules\api\models\mch\OrderSendForm;

class OrderSendMessageForm extends ApiModel
{
    public $user_id;
    public $order_id;
    public $store_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::findOne($this->order_id);

        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在。',
            ];
        }
        $order->is_remind = 1;
        $res = OrderMessage::set($this->order_id,$this->store_id,0,2);
        if ($res && $order->save()) {
            return [
                'code' => 0,
                'msg' => '提醒发货成功。',
            ];
        } else {
            return $this->getErrorResponse($order);
        }
    }
}
