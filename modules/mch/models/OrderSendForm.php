<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/24
 * Time: 18:42
 */

namespace app\modules\mch\models;

use app\models\AnhourDistrict;
use app\models\common\api\CommonShoppingList;
use app\models\Express;
use app\models\FormId;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\models\WechatTemplateMessage;
use app\models\WechatTplMsgSender;
use app\utils\CurlHelper;
use app\utils\TaskCreate;
use Curl\Curl;

class OrderSendForm extends MchModel
{
    public $store_id;
    public $order_id;
    public $express;
    public $express_no;
    public $words;

    public $anhour; //是否对接anhour ERP系统
    public $account_shop_id; //一小时门店系统id
//    public $mch_id;
    public $province_id;
    public $city_id;
    public $area_id;
    public $address;

    public $depot_id; //仓库id
    public $batch_no; //仓库批次号
    public $batch_number; //批次号查询
    public $sales;

    public function rules()
    {
        return [
            [['express', 'express_no', 'words','batch_no','batch_number'], 'trim'],
            [['express', 'express_no',], 'required', 'on' => 'EXPRESS'],
            [['order_id'], 'required'],
            [['express', 'express_no',], 'string',],
            [['express', 'express_no',], 'default', 'value' => ''],
            [['anhour','account_shop_id','province_id','city_id','area_id','address','depot_id'],'integer'],
            [['sales'],'safe']
        ];
    }

    public function batch($arrCSV)
    {

        $empty = [];  //是否存在
        $error = [];   //操作失败
        $cancel = [];  //是否取消
        $offline = []; //到店自提
        $send = [];  //是否发货
        $success = []; //是否成功

        foreach ($arrCSV as $v) {
            $order = Order::findOne([
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'order_no' => $v[1],
                'mch_id' => 0,
            ]);
            if (!$order) {
                $empty[] = $v[1];
                continue;
            }
            if ($order->is_cancel) {
                $cancel[] = $v[1];
                continue;
            }
            if ($order->is_send) {
                $send[] = $v[1];
                continue;
            }
            if ($order->is_offline) {
                $offline[] = $v[1];
                continue;
            }
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                $pay[] = $v[1];
            }

            $order->express_no = $v[2];
            $order->is_send = 1;
            $order->send_time = time();
            $order->express = $this->express;

            if (!$order->save()) {
                $error[] = $v[1];
            } else {
                $success[] = $v[1];
                try {
                    $wechat_tpl_meg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
                    $wechat_tpl_meg_sender->sendMsg();
                    TaskCreate::orderConfirm($order->id, 'STORE');
                } catch (\Exception $e) {
                    \Yii::warning($e->getMessage());
                }
            }
        };
        $data = [];
        $max = max(count($empty), count($error), count($cancel), count($send), count($offline), count($pay), count($success));
        for ($i = 0, $k = 0; $i < $max; $k++, $i++) {
            $data[$k][] = $empty[$k];
            $data[$k][] = $cancel[$k];
            $data[$k][] = $send[$k];
            $data[$k][] = $offline[$k];
            $data[$k][] = $pay[$k];
            $data[$k][] = $error[$k];
            $data[$k][] = $success[$k];
        }
        return $data;
    }

    public function attributeLabels()
    {
        return [
            'express' => '快递公司',
            'express_no' => '快递单号',
            'words' => '商家留言',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        foreach ($this->sales as &$v){
            unset($v['depot_name']);
        }
        $order = Order::findOne([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'id' => $this->order_id,
            'mch_id' => 0,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在或已删除',
            ];
        }
        if ($order->is_pay == 0 && $order->pay_type != 2) {
            return [
                'code' => 1,
                'msg' => '订单未支付'
            ];
        }

        if ($order->apply_delete == 1) {
            return [
                'code' => 1,
                'msg' => '该订单正在申请取消操作，请先处理'
            ];
        }

        $expressList = Express::getExpressList();
        $ok = false;
        foreach ($expressList as $value) {
            if ($value['name'] == $this->express) {
                $ok = true;
                break;
            }
        }
        if (!$ok && $this->scenario == "EXPRESS") {
            return [
                'code' => 1,
                'msg' => '快递公司不正确'
            ];
        }

        $order->express = $this->express;
        $order->express_no = $this->express_no;
        $order->words = $this->words;
        $order->is_send = 1;
        $order->send_time = time();

        #####对接一小时结单接口 start ########
        if ($this->anhour){
            $sendorder = $this->getPositioning($order->address);
            $user = User::findOne($order->user_id);
            $anhour_data = [
                'order_sn' => $order->order_no,
                'store_id' => $this->account_shop_id,
                'membership_info_id' => $user->membership_info_id,
                'base_data' => [
                    'deliver_type' => $order->is_offline == 1 ? 1 : 2,
                    'province_id' => $sendorder['province_id'] ? $sendorder['province_id'] : 0,
                    'city_id' => $sendorder['city_id'] ? $sendorder['city_id'] : 0,
                    'area_id' => $sendorder['district_id'] ? $sendorder['district_id'] : 0,
                    'address' => $order->address,
                ],
                'sales_goods_data' => $this->sales
            ];
//            dd($anhour_data);
            $res = json_decode(CurlHelper::post('storemall/order/end',$anhour_data));
            if ($res->error_code == 1){
                return [
                    'code' => 1,
                    'msg' => $res->error_msg.$anhour_data['base_data']['province_id'],
                ];
            }else{
                $order->anhour_api_text = '订单编号：'.$order->order_no.' 会员ID：'.$user->membership_info_id.' 状态值：3 是否结单： 是';
            }
        }
        #####对接一小时结单接口 end ########
        if ($order->save()) {
            try {
                $wechat_tpl_meg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
                $wechat_tpl_meg_sender->sendMsg();
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage());
            }
            // 创建订单自动收货定时任务
            TaskCreate::orderConfirm($order->id, 'STORE');
            $wechatAccessToken = $this->getWechat()->getAccessToken();
            $res = CommonShoppingList::updateBuyGood($wechatAccessToken, $order, 0, 4);
            return [
                'code' => 0,
                'msg' => '发货成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '操作失败',
            ];
        }
    }

    /**
     * @deprecated 已废弃
     */
    private function sendMessage($order)
    {
        $user = User::findOne($order->user_id);
        if (!$user) {
            return;
        }
        /* @var FormId $form_id */
        $form_id = FormId::find()->where(['order_no' => $order->order_no])->orderBy('addtime DESC')->one();
        $wechat = $this->getWechat();
        if (!$wechat) {
            return;
        }
        if (!$form_id) {
            return;
        }
        $store = Store::findOne($this->store_id);
        if (!$store || !$store->order_send_tpl) {
            return;
        }

        $goods_list = OrderDetail::find()
            ->select('g.name,od.num')
            ->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where(['od.order_id' => $order->id, 'od.is_delete' => 0])->asArray()->all();

        $msg_title = '';
        foreach ($goods_list as $goods) {
            $msg_title .= $goods['name'];
        }


        $access_token = $this->wechat->getAccessToken();
        $api = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
        $data = (object)[
            'touser' => $user->wechat_open_id,
            'template_id' => $store->order_send_tpl,
            'form_id' => $form_id->form_id,
            'page' => 'pages/order/order?status=2',
            'data' => (object)[
                'keyword1' => (object)[
                    'value' => $msg_title,
                    'color' => '#333333',
                ],
                'keyword2' => (object)[
                    'value' => $order->express,
                    'color' => '#333333',
                ],
                'keyword3' => (object)[
                    'value' => $order->express_no,
                    'color' => '#333333',
                ],
            ],
        ];
        $data = \Yii::$app->serializer->encode($data);
        $wechat->curl->post($api, $data);
        $res = json_decode($wechat->curl->response, true);
        if (!empty($res['errcode']) && $res['errcode'] != 0) {
            \Yii::warning("模板消息发送失败：\r\ndata=>{$data}\r\nresponse=>" . \Yii::$app->serializer->encode($res));
        }
    }


    /**
     * @return array
     * 批次号对接
     */
    public function getBatchNumber()
    {
        $data = [
            'batch_number' => $this->batch_number,
            'store_id' => $this->account_shop_id
        ];
        $res = CurlHelper::get('storemall/order/existBatchNumber',$data);
        $batch = json_decode($res,true);
        $modal = [];
        foreach ($batch['depot_store_depot_batch'] as $i => $k)
        {
            $modal[$i]['batch_number'] = $k['batch_number'];
            $modal[$i]['num'] = $k['num'];
            $modal[$i]['frozen_num'] = $k['frozen_num'];
            $modal[$i]['cost'] = $k['cost'];
            $modal[$i]['total_cost'] = $k['total_cost'];
            $modal[$i]['classify_name'] = $k['classify_type']['name'];
            $modal[$i]['batch_number'] = $k['batch_number'];
            $modal[$i]['goods_id'] = $k['goods_detail']['id'];
            $modal[$i]['goods_name'] = $k['goods_detail']['goods_name'];
            $modal[$i]['goods_sn'] = $k['goods_detail']['goods_sn'];
            $modal[$i]['depot_id'] = $k['depot']['id'];
            $modal[$i]['depot_name'] = $k['depot']['name'];
        }
        return $modal;
    }

    //地图定位,根据地址通过腾讯地图api，精确返回省市区
    public function getPositioning($address)
    {
        $store = Store::findOne(1);
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/?address=='.$address.'&key='.$store->tencent_map;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_MAXREDIRS,2);
        $response = curl_exec($ch);
        curl_close($ch);
        $address_data = json_decode($response);
        $data = [];
        if ($address_data->status == 0){
            $province = AnhourDistrict::find()->where(['type'=>1])->andWhere(['like','name',$address_data->result->address_components->province])->select('did')->one();
            $city = AnhourDistrict::find()->where(['type'=>2,'parent_id'=>$province->did])->andWhere(['like','name',$address_data->result->address_components->city])->select('did')->one();
            $dis = AnhourDistrict::find()->where(['type'=>3,'parent_id'=>$city->did])->andWhere(['like','name',$address_data->result->address_components->district])->select('did')->one();
            $data = [
                'province_id' => $province->did,
                'city_id' => $city->did,
                'district_id' => $dis->did,
            ];
            return $data;
        }else{
            return $data;
        }
    }
}
