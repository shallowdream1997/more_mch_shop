<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/4/27
 * Time: 17:26
 */


namespace app\modules\api\behaviors;

use app\models\Mch;
use app\models\MchVisitLog;
use yii\base\Behavior;
use yii\helpers\VarDumper;
use yii\web\Controller;

class VisitBehavior extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($e)
    {
        if (\Yii::$app->requestedRoute == 'api/default/index') {
            $log = new MchVisitLog();
            $log->user_id = \Yii::$app->user->id;
            $log->mch_id = $this->getMchId();
            $log->addtime = time();
            $log->visit_date = date('Y-m-d');
            $log->save();
        }
    }

    /**
     * @return array|int|mixed
     * 获取MchId，记录浏览次数
     */
    private function getMchId()
    {
        if (!\Yii::$app->request->get('mch_id')){
            $mch = Mch::findOne(['is_store'=>1]);
            return $mch->id;
        }else{
            return \Yii::$app->request->get('mch_id');
        }
    }
}
