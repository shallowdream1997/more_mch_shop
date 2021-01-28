<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/9
 * Time: 17:57
 */

namespace app\modules\user\controllers\clerk;

use app\models\Level;
use app\modules\user\behaviors\MchBehavior;
use app\modules\user\behaviors\PermissionRoleBehavior;
use app\modules\user\behaviors\UserLoginBehavior;
use app\modules\user\controllers\Controller;
use app\modules\user\models\clerk\ClerkListForm;


class IndexController extends Controller
{
    public function behaviors()
    {
        return [
            'login' => [
                'class' => UserLoginBehavior::className(),
            ],
            'mch' => [
                'class' => MchBehavior::className(),
            ],
            'permission' => [
                'class' => PermissionRoleBehavior::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $form = new ClerkListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->mch_id = $this->mch->id;
        $form->is_clerk = 0;
        $data = $form->search();
        $level_list = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->orderBy(['level' => SORT_ASC])->asArray()->all();
        return $this->render('index', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            'level_list' => $level_list
        ]);
    }

}
