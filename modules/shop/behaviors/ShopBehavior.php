<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/9
 * Time: 15:36
 */

namespace app\modules\shop\behaviors;

use app\models\AccountMch;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\MemberId;
use app\models\User;
use app\modules\admin\models\Permissions;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

class ShopBehavior extends Behavior
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
        /** @var AccountMch $shop */
        $shop = \Yii::$app->shop->identity;
        if (!$shop) {
            \Yii::$app->end();
        }

        $e->action->controller->shop = $shop;
    }
}
