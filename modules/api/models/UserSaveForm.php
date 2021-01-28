<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/25
 * Time: 15:44
 */

namespace app\modules\api\models;

use app\hejiang\ApiCode;
use app\models\Address;
use app\models\curlanhour\CommonCurlAnhour;
use app\models\DistrictArr;
use app\models\Model;
use app\models\Option;
use app\models\User;

class UserSaveForm extends ApiModel
{
    public $store_id;
    public $user_id;
    public $birthday;
    public $nickname;
    public $mobile;
    public $sex;


    public function rules()
    {
        return [
            [['nickname', 'mobile', 'birthday'], 'trim'],
            [['nickname','mobile'], 'required'],
            [['sex'],'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nickname' => '用户昵称',
            'mobile' => '电话',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        if ($this->mobile){
            $option = Option::getList('mobile_verify', \Yii::$app->controller->store->id, 'admin', 1);
            if ($option['mobile_verify']) {
                if (!preg_match(Model::MOBILE_VERIFY, $this->mobile)) {
                    return [
                        'code' => 1,
                        'msg' => '请输入正确的手机号'
                    ];
                }
            }
        }

        if (empty($this->nickname) || $this->nickname === 'undefined'){
            return [
                'code' => 1,
                'msg' => '请输入昵称'
            ];
        }

        $user = User::findOne($this->user_id);

        if (!$user){
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '操作失败，请稍后重试',
            ];
        }
        $user->nickname = preg_replace('/[\xf0-\xf7].{3}/', '', $this->nickname);
        $user->binding = $this->mobile ? $this->mobile : '';
        $user->sex = $this->sex ? $this->sex : 0;
        $user->birthday = $this->birthday ? strtotime($this->birthday) : time();
        if ($user->save()) {

            //用户更新membership_info_id会员 start
            $user_data = [
                'phone' => $user->binding,
                'name' => $user->nickname,
            ];
            $um = new CommonCurlAnhour();
            $um->type = "GET";
            $um->url = "storemall/user/userWallet";
            $um->data = $user_data;
            $resD = $um->selectType();
            $user->membership_info_id = $resD->user_wallet->membership_info_id;
            $user->money = bcadd(bcadd($resD->user_wallet->recharge_money,$resD->user_wallet->extra_money,2),$resD->user_wallet->current_money,2);
            $user->save();
            \Yii::error("一小时对接{$user->nickname}用户更新");
            //用户更新membership_info_id会员 end

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功',
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg'  => '操作失败，请稍后重试',
            ];
        }
    }
}
