<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/2/28
 * Time: 10:22
 */

namespace app\modules\mch\controllers\mch;

use app\hejiang\ApiCode;
use app\models\AccountMch;
use app\models\DistrictArr;
use app\models\Mch;
use app\models\MchCommonCat;
use app\models\Shop;
use app\models\User;
use app\modules\mch\controllers\Controller;
use app\modules\mch\models\mch\AccountAddForm;
use app\modules\mch\models\mch\AccountEditForm;
use app\modules\mch\models\mch\AccountListForm;
use app\modules\mch\models\mch\CashConfirmForm;
use app\modules\mch\models\mch\CashListForm;
use app\modules\mch\models\mch\CommonCatEditForm;
use app\modules\mch\models\mch\MchAddForm;
use app\modules\mch\models\mch\MchEditForm;
use app\modules\mch\models\mch\MchListForm;
use app\modules\mch\models\mch\MchSettingForm;
use app\modules\mch\models\mch\OneMchSettingForm;
use app\modules\mch\models\mch\ReportFormsForm;
use yii\data\Pagination;

class AccountController extends Controller
{
    public function actionIndex()
    {
        $form = new AccountListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $res = $form->search();
        return $this->render('index', [
            'adminUrl' => $res['data']['adminUrl'],
            'list' => $res['data']['list'],
            'pagination' => $res['data']['pagination'],
            'get' => \Yii::$app->request->get(),
        ]);
    }

    public function actionEdit($id)
    {
        $model = AccountMch::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send();
            return;
        }
        if (\Yii::$app->request->isPost) {
            $form = new AccountEditForm();
            $form->model = $model;
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionStoreMchList()
    {
        $keyword = trim(\Yii::$app->request->get('keyword'));
        $query = Mch::find()->where(['store_id'=>$this->store->id,'is_delete'=>0,'review_status'=>1,'is_lock' => 0]);
        if ($keyword) {
            $query->andWhere([
                'or',
                ['LIKE', 'realname', $keyword],
            ]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')->asArray()->all();
        return \Yii::$app->serializer->encode($list);
    }

    public function actionAdd()
    {
        if (\Yii::$app->request->isPost) {
            $form = new AccountAddForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            return $form->save();
        } else {
            return $this->render('add');
        }
    }

    public function actionMchDel()
    {
        $form = new AccountListForm();
        $form->mch_id = \Yii::$app->request->get('id');
        $res = $form->delete();

        return $res;
    }
}
