<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/4/29
 * Time: 18:34
 */


namespace app\modules\api\models\mch;

use app\models\Mch;
use app\models\MchAccountLog;
use app\models\MchCash;
use app\models\Option;
use app\models\User;
use app\modules\api\models\ApiModel;
use app\modules\api\models\StoreFrom;
use app\modules\api\models\UserCommentForm;
use app\utils\Cash;

class CashSubmitForm extends ApiModel
{
    public $mch_id;
    public $cash_val;
    public $store_id;
    public $account;
    public $nickname;
    public $bank_name;
    public $type;
    public $form_id;

    public function rules()
    {
        return [
            [['cash_val'], 'required'],
            [['cash_val'], 'number', 'min' => 1],
            [['nickname', 'account', 'form_id',], 'trim'],
            [['type'], 'in', 'range' => [0, 1, 2, 3, 4]],
            [['nickname', 'account','bank_name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cash_val' => '提现金额',
            'account'=>'账号',
            'nickname'=>'昵称',
            'bank_name'=>'开户银行',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mch = Mch::findOne($this->mch_id);
        if (!$mch) {
            return [
                'code' => 0,
                'msg' => '商户不存在。',
            ];
        }
        $this->cash_val = floatval(sprintf('%.2f', $this->cash_val));
        if ($this->cash_val > $mch->account_money) {
            return [
                'code' => 1,
                'msg' => '账户余额不足。',
            ];
        }
        $mch->account_money = $mch->account_money - $this->cash_val;
        $cash = new MchCash();
        $cash->store_id = $mch->store_id;
        $cash->mch_id = $this->mch_id;
        $cash->money = $this->cash_val;
        $cash->addtime = time();
        $cash->status = 0;
        $cash->type = $this->type;
        $cash->type_data = \Yii::$app->serializer->encode([
            'account'=>$this->account,
            'nickname'=>$this->nickname,
            'bank_name'=>$this->bank_name
        ]);
        $cash->order_no = 'MC' . date('YmdHis') . mt_rand(1000, 9999);
        $t = \Yii::$app->db->beginTransaction();
        $r1 = $cash->save();
        $r2 = $mch->save();
        if ($r1 && $r2) {
            $log = new MchAccountLog();
            $log->store_id = $mch->store_id;
            $log->mch_id = $mch->id;
            $log->price = $this->cash_val;
            $log->type = 2;
            $log->desc = '提现';
            $log->addtime = time();
            $log->save();

            $t->commit();
            return [
                'code' => 0,
                'msg' => '提现已提交，请等待管理员审核。',
            ];
        } else {
            $t->rollBack();
            return $this->getErrorResponse(!$r1 ? $cash : $mch);
        }
    }

    public function search()
    {
        $mch = Mch::findOne(['id' => $this->mch_id]);
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'type_list' => $this->getSetting(),
                'money' => $mch->account_money
            ]
        ];
    }

    private function getSetting()
    {
        $default = [
            'entry_rules' => '',
            'type' => []
        ];
        $data = Option::get('mch_setting', $this->store_id, 'mch', $default);
        $wxappForm = new StoreFrom();
        $wxappImg = $wxappForm->search();
        $newList = [];
        if (is_array($data['type'])) {
            foreach ($data['type'] as $item) {
                $newItem = [];
                switch ($item) {
                    case 1:
                        $newItem['id'] = 1;
                        $newItem['name'] = "微信";
                        $newItem['icon'] = $wxappImg['share']['wechat']['url'];
                        break;
                    case 2:
                        $newItem['id'] = 2;
                        $newItem['name'] = "支付宝";
                        $newItem['icon'] = $wxappImg['share']['ant']['url'];
                        break;
                    case 3:
                        $newItem['id'] = 3;
                        $newItem['name'] = "银行卡";
                        $newItem['icon'] = $wxappImg['share']['bank']['url'];
                        break;
                    case 4:
                        $newItem['id'] = 4;
                        $newItem['name'] = "余额";
                        $newItem['icon'] = $wxappImg['share']['money']['url'];
                        break;
                    default:
                        $newItem = [];
                        break;
                }
                $newList[] = $newItem;
            }
        }
        return $newList;
    }

    //微信提现
    public function savenew()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mch = Mch::findOne($this->mch_id);
        if (!$mch) {
            return [
                'code' => 0,
                'msg' => '商户不存在。',
            ];
        }
        $this->cash_val = floatval(sprintf('%.2f', $this->cash_val));
        if ($this->cash_val > $mch->account_money) {
            return [
                'code' => 1,
                'msg' => '账户余额不足。',
            ];
        }
        $mch->account_money = $mch->account_money - $this->cash_val;
        $cash = new MchCash();
        $cash->store_id = $mch->store_id;
        $cash->mch_id = $this->mch_id;
        $cash->money = $this->cash_val;
        $cash->addtime = time();
        $cash->status = 1;
        $cash->type = $this->type;
        $cash->type_data = \Yii::$app->serializer->encode([
            'account'=>$this->account,
            'nickname'=>$this->nickname,
            'bank_name'=>$this->bank_name
        ]);
        $cash->virtual_type = 0;
        $cash->order_no = 'MC' . date('YmdHis') . mt_rand(1000, 9999);
        $t = \Yii::$app->db->beginTransaction();
        $r1 = $cash->save();
        $r2 = $mch->save();
        if ($r1 && $r2) {
            $log = new MchAccountLog();
            $log->store_id = $mch->store_id;
            $log->mch_id = $mch->id;
            $log->price = $this->cash_val;
            $log->type = 2;
            $log->desc = '提现';
            $log->addtime = time();
            $log->save();

            $res = $this->tixian($cash);
            if ($res['code'] == 0){
                $t->commit();
                return [
                    'code' => 0,
                    'msg' => '提现已申请，两日内到账',
                ];
            }else{
                $t->rollBack();
                dd(1);
                return [
                    'code' => 1,
                    'msg' => $res['msg'],
                ];
            }

        } else {
            $t->rollBack();
            return $this->getErrorResponse(!$r1 ? $cash : $mch);
        }
    }

    public function tixian($cash)
    {
        $user = User::findOne(\Yii::$app->user->id);
        $wechat = $this->getWechat();
        $res = $wechat->pay->transfers([
            'partner_trade_no' => $cash->order_no,
            'openid' => $user->wechat_open_id,
            'amount' => $cash->money * 100,
            'desc' => '入驻商提现',
        ]);
        if (!$res) {
            return [
                'code' => 1,
                'msg' => '提现失败，请检查微信配置是否正确。'
            ];
        }
        if ($res['return_code'] != 'SUCCESS') {
            return [
                'code' => 1,
                'msg' => '提现失败：' . $res['return_msg'],
                'res' => $res,
            ];
        }
        if ($res['result_code'] != 'SUCCESS') {
            return [
                'code' => 1,
                'msg' => '提现失败：' . $res['err_code_des'],
                'res' => $res,
            ];
        }
        if ($res['result_code'] == 'SUCCESS') {
            return [
                'code' => 0,
                'msg' => '提现成功。',
                'res' => $res,
            ];
        }
    }

}
