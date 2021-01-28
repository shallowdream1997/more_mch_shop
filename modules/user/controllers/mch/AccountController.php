<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/5/4
 * Time: 16:43
 */


namespace app\modules\user\controllers\mch;

use app\models\Mch;
use app\modules\mch\models\UserListForm;
use app\modules\user\behaviors\MchBehavior;
use app\modules\user\behaviors\PermissionRoleBehavior;
use app\modules\user\behaviors\UserLoginBehavior;
use app\modules\user\controllers\Controller;
use app\modules\user\models\mch\CashListForm;
use app\modules\user\models\mch\CashSubmitForm;
use app\modules\user\models\mch\LogListForm;

class AccountController extends Controller
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

    public function actionCash()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CashSubmitForm();
            $form->attributes = \Yii::$app->request->post();
            $form->mch_id = $this->mch->id;
            return $form->save();
        } else {
            $form = new CashListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->mch_id = $this->mch->id;
            $form->cash_user_id = $this->mch->cash_user_id;
            $form->store_id = $this->store->id;
            $res = $form->search();
            return $this->render('cash', [
                'list' => $res['data']['list'],
                'pagination' => $res['data']['pagination'],
                'account_money' => $this->mch->account_money,
                'account_shop_money' => $this->mch->account_shop_money,
                'cash_user' => $res['data']['cash_user'],
                'type_list'=>\Yii::$app->serializer->encode($form->getSetting()),
                'user_list' => $res['data']['user_list']
            ]);
        }
    }

    public function actionGetUser()
    {
        $form = new CashListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $data_list = $form->getUser();
        return \Yii::$app->serializer->encode($data_list);
    }

    public function actionLog()
    {
        $form = new LogListForm();
        $form->store_id = $this->store->id;
        $form->mch_id = $this->mch->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('log', [
            'list' => $arr['list'],
            'pagination' => $arr['pagination']
        ]);
    }

    /**
     * @param null $id
     * @param int $status
     * @return array
     * 绑定默认微信提现人id
     */
    public function actionCashuserEdit($id = null,$status = 0)
    {

        $mch = Mch::findOne(['id' => $this->mch->id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$mch) {
            return [
                'code' => 1,
                'msg' => '网络异常',
            ];
        }
        if ($status == 1) {
            $mch->cash_user_id = $id;
        }else{
            $mch->cash_user_id = 0;
        }
        if ($mch->save()) {
            return [
                'code' => 0,
                'msg' => '成功',
            ];
        }else {
            return [
                'code' => 1,
                'msg' => '网络异常1'.var_dump($mch->getErrors()),
            ];
        }

    }
}
