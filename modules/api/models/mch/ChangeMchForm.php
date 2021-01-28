<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/6
 * Time: 10:55
 */

namespace app\modules\api\models\mch;


use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\MchVisitLog;
use app\models\Order;
use app\models\OrderDetail;
use app\modules\api\models\ApiModel;

class ChangeMchForm extends ApiModel
{
    public $mch;

    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'],'integer'],
        ];
    }

    public function search()
    {
//        $query = Mch::find()->where(['user_id'=>$this->getCurrentUserId(),'is_delete' => 0,'is_open'=>Mch::IS_OPEN_TRUE,'review_status'=>1]);
//        $mch_list = $query->select('id,name,is_default')->orderBy('sort ASC')->all();

        $query = MchAuthLogin::find()->where(['user_id'=>$this->getCurrentUserId(),'store_id'=>$this->store->id]);
        $mch_list = $query->select('id,name,is_default')->orderBy('addtime ASC')->all();
        return [
            'code' => 0,
            'data' => [
                'mch_list' => $mch_list,
            ],
        ];
    }

    public function changemch()
    {
        if ($this->mch_id){
            $res = MchAuthLogin::updateAll(['is_default'=>0],['is_default'=>1,'store_id'=>$this->store->id,'user_id'=>$this->getCurrentUserId()]);
            if ($res){
                $result = MchAuthLogin::updateAll(['is_default'=>1],['store_id'=>$this->store->id,'id'=>$this->mch_id]);
                if ($result){
                    return [
                        'code' => 0,
                        'msg' => '切换商户账号成功'
                    ];
                }
            }else{
                return [
                    'code' => 1,
                    'msg' => '切换商户账号失败'
                ];
            }
        }else{
            return [
                'code' => 1,
                'msg' => '切换商户账号失败'
            ];
        }
    }

    /**
     * 获取当前登录用户 ID
     * @param boolean isGuest 是否未登录：false否|true是
     * @return int|string
     */
    public function getCurrentUserId()
    {
        if (\Yii::$app->mchRoleAdmin->isGuest == false) {
            return \Yii::$app->mchRoleAdmin->id;
        }

        if (\Yii::$app->user->isGuest == false) {
            return \Yii::$app->user->id;
        }

        if (\Yii::$app->admin->isGuest == false) {
            return \Yii::$app->admin->id;
        }
    }
}
