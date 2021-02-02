<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/6/26
 * Time: 14:37
 */

namespace app\modules\shop\models;


use luweiss\wechat\Wechat;
use Yii;

class Model extends \app\models\Model
{
    /**
     * @return Wechat
     */
    public function getWechat()
    {
        return empty(\Yii::$app->controller->wechat) ? null : \Yii::$app->controller->wechat;
    }
}
