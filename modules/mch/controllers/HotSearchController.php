<?php
namespace app\modules\mch\controllers;

use app\models\RefundAddress;
use app\models\RefundReson;
use app\models\SearchKeywords;
use app\modules\mch\models\HotSearchForm;
use app\modules\mch\models\RefundAddressForm;
use app\modules\mch\models\RefundResonForm;
use yii\data\Pagination;

class HotSearchController extends Controller
{
    public function actionIndex()
    {
        $query = SearchKeywords::find()->where(['is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $list = $query->limit($pagination->limit)->orderBy('id DESC')->offset($pagination->offset)->all();

        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionEdit($id = null)
    {
        $model = SearchKeywords::findOne(['id' => $id, 'is_delete' => 0]);
        if (!$model) {
            $model = new SearchKeywords();
        }
        if (\Yii::$app->request->isPost) {
            $form = new HotSearchForm();
            $form->model = $model;
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
        return $this->render('edit', [
            'model' => $model
        ]);
    }

    public function actionDel($id = null)
    {
        $model = SearchKeywords::findOne(['id' => $id, 'is_delete' => 0]);
        if (!$model) {
            return [
                'code'=> 1 ,
                'msg'=>'请刷新重试'
            ];
        }
        $model->is_delete = 1;
        if ($model->save()) {
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        } else {
            foreach ($model->errors as $errors) {
                return [
                    'code' => 1,
                    'msg' => $errors[0],
                ];
            }
        }
    }

}
