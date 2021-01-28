<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/6
 * Time: 10:41
 */

namespace app\modules\api\behaviors;

use app\hejiang\ApiResponse;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\Model;
use yii\base\ActionFilter;
use yii\web\Controller;

class MchBehavior extends ActionFilter
{
    public $actions;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @param \yii\base\InlineAction $e
     */
    public function beforeAction($e)
    {
        if (\Yii::$app->user->isGuest) {
            \Yii::$app->response->data = new ApiResponse(-1, '请先登录。');
            return false;
        }

        //写入授权登陆表
        $mch = Mch::find()->alias('m')
            ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
            ->where(['mau.user_id'=>\Yii::$app->user->id,'mau.is_default'=>1])
            ->select('m.*')
            ->one();

        if (!$mch) {
            \Yii::$app->response->data = new ApiResponse(1, '请先申请商户入驻。');
            return false;
        }
        $e->controller->mch = $mch;
        return true;
    }
}
