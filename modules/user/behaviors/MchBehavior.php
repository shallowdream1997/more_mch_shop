<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/9
 * Time: 15:36
 */

namespace app\modules\user\behaviors;

use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\MemberId;
use app\models\User;
use app\modules\admin\models\Permissions;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

class MchBehavior extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @param ActionEvent $e
     */
    public function beforeAction($e)
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        if (!$user) {
            \Yii::$app->end();
        }

        $mch = Mch::find()->alias('m')
            ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
            ->where(['mau.user_id'=>$user->id,'mau.is_default'=>1])
            ->select('m.*')
            ->one();
        if (!$mch) {
            //如果扫码用户没有商户，则判断是否是员工
            $mch = Mch::find()->alias('m')
                ->innerJoin(['mem'=>MemberId::tableName()],'mem.mch_id=m.id')
                ->where(['mem.user_id'=>$user->id,'mem.is_delete'=>0])
                ->select('m.*')
                ->one();
            if (!$mch){
                \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['user/default/setting',]));
                \Yii::$app->end();
            }

        }
        if ($mch->review_status != 1) {
            \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['user/default/setting',]));
            \Yii::$app->end();
        }
        $e->action->controller->mch = $mch;
    }
}
