<?php

namespace app\models\task\order;


use app\hejiang\task\TaskRunnable;
use app\models\ActionLog;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Register;
use app\models\Store;
use app\models\User;
use app\utils\TplusOption;

/**
 * @property Store $store
 */
class OrderIsv extends TaskRunnable
{
    const STORE = 'STORE';

    public $store;
    public $time;
    public $params = [];


    public function run($params = [])
    {
        $this->store = Store::findOne($params['store_id']);
        $this->time = time();
        $this->params = $params;
        switch ($params['order_type']) {
            case self::STORE:
                $res = $this->storeOrder();
                break;
            default:
                $res = true;
                break;
        }

        return true;
    }

    /**
     * 商城订单自动取消
     * @return bool
     * @throws \Exception
     */
    public function storeOrder()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $Order = [
                'order_id' => $this->params['order_id'],
                'order_no' => $this->params['order_no'],
                'address' => $this->params['address'],
                'name' => $this->params['name'],
                'mobile' => $this->params['mobile'],
                'remark' => $this->params['remark'],
            ];
            if ($this->store->is_open_isv){
                //用友---下单接口
                $this->YongyouOSaleOrderCreate($Order);
                //用友--销货单
                $this->YongyouOSaleDeliveryCreate($Order);
            }
            $o = Order::findOne($Order['order_id']);
            $o->anhour_api_text = "队列开启";
            if ($o->save()){
                $transaction->commit();
                return true;
            }else{
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->saveActionLog($e);
            throw $e;
        }
    }

    /**
     * 存储错误日志
     * @param $e
     * @return bool
     */
    public function saveActionLog($e)
    {
        // 记录错误信息
        $errorInfo = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
        ];

        $actionLog = new ActionLog();
        $actionLog->store_id = $this->params['store_id'];
        $actionLog->title = '定时任务';
        $actionLog->addtime = time();
        $actionLog->admin_name = '系统自身';
        $actionLog->admin_id = 0;
        $actionLog->admin_ip = '';
        $actionLog->route = '';
        $actionLog->type = 1;
        $actionLog->action_type = '下单ISV和订货单';
        $actionLog->obj_id = $this->order->id;
        $actionLog->result = json_encode($errorInfo);
        $res = $actionLog->save();

        return $res;
    }


    //下单销售订单新增
    private function YongyouOSaleOrderCreate($order)
    {
        $yyisv = new TplusOption();
        $od = OrderDetail::find()->alias('od')->leftJoin(['g'=>Goods::tableName()],'g.id=od.goods_id')
            ->where(['od.order_id'=>$order['id'],'od.is_delete'=>OrderDetail::ORDER_IS_SHOW])->select('od.*,g.unit')->asArray()->all();
        //商品价格 total_price
        $SaleOrderDetails = self::getSaleOrderDetails($od);
        $apiParm = [
            '_args' => json_encode([
                'dto' => [
                    'VoucherDate'      => date("Y-m-d",time()),
                    'ExternalCode'     => $order['order_no'], // 外部系统单据编码，编码必须唯一，且此字段不为空
                    'Customer'         => ['Code' => 'AH08003-1'], // 客户编码 此编码要与T+系统客户编码一致 AH08003-1 AH08005-1 AH08006-1 AH10001-1
                    'Address'          => $order['address'],
                    'LinkMan'          => $order['name'],
                    'ContactPhone'     => $order['mobile'],
                    'Memo'             => $order['remark'],
                    'ExchangeRate'     => (5 / 100),//汇率，decimal类型
                    'SaleOrderDetails' => $SaleOrderDetails,
                ],
            ], JSON_UNESCAPED_UNICODE),
        ];
        $argList = $yyisv::Options('/saleOrder/Create',$apiParm);
    }

    private function getSaleOrderDetails($od)
    {
        $SaleOrderDetails = [];
        foreach ($od as $i => $k){
            //获取存货编码，这里写死一个测试
            $SaleOrderDetails[$i]['Inventory']        = ['Code'=>'100145'];
            //计量单位信息
            $SaleOrderDetails[$i]['Unit']             = ['Name'=>$k['unit']];
            //数量，decimal类型
            $SaleOrderDetails[$i]['Quantity']         = $k['num'];
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
        return $SaleOrderDetails;
    }
    /*
     *
     * 新增销货单 TPlus/api/v1/saleDelivery/Create
     *
     * */
    private function YongyouOSaleDeliveryCreate($order)
    {
        $yyisv = new TplusOption();
        $od = OrderDetail::find()->alias('od')->leftJoin(['g'=>Goods::tableName()],'g.id=od.goods_id')
            ->where(['od.order_id'=>$order['id'],'od.is_delete'=>OrderDetail::ORDER_IS_SHOW])->select('od.*,g.unit')->asArray()->all();
        //商品价格 total_price
        $SaleDeliveryDetails = self::getSaleDeliveryDetails($od);
        $apiParm = [
            '_args' => json_encode([
                'dto' => [
                    'VoucherDate'      => date("Y-m-d",time()),
                    'ExternalCode'     => $order['order_no'], // 外部系统单据编码，编码必须唯一，且此字段不为空
                    'Customer'         => ['Code' => 'AH08003-1'], // 客户编码 此编码要与T+系统客户编码一致 AH08003-1 AH08005-1 AH08006-1 AH10001-1
                    'InvoiceType'      => ['Code' => '00'],
                    'Address'          => $order['address'],
                    'LinkMan'          => $order['name'],
                    'ContactPhone'     => $order['mobile'],
                    'Memo'             => $order['remark'],
                    'SaleDeliveryDetails' => $SaleDeliveryDetails,
                ],
            ], JSON_UNESCAPED_UNICODE),
        ];
        $argList = $yyisv::Options('/saleDelivery/Create',$apiParm);
    }

    private function getSaleDeliveryDetails($od)
    {
        $SaleDeliveryDetails = [];
        foreach ($od as $i => $k){
            //获取存货编码，这里写死一个测试
            $SaleDeliveryDetails[$i]['Inventory']        = ['Code'=>'100145'];
            //计量单位信息
            $SaleDeliveryDetails[$i]['Unit']             = ['Name'=>$k['unit']];
            //数量，decimal类型
            $SaleDeliveryDetails[$i]['Quantity']         = $k['num'];
            //价格
            $SaleDeliveryDetails[$i]['OrigPrice']        = $k['total_price'];//商品价格 total_price
            $SaleDeliveryDetails[$i]['OrigTaxAmount']    = $k['total_price'];//商品价格 total_price
            // $SaleDeliveryDetails[$i]['OrigDiscountPrice'] = $k['total_price'];
            // $SaleDeliveryDetails[$i]['OrigDiscountAmount'] = $k['total_price'];
            // $SaleDeliveryDetails[$i]['OrigDiscount']     = $k['total_price'];

        }
        return $SaleDeliveryDetails;
    }

}
