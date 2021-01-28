<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1
 * Time: 10:08
 */

namespace app\modules\user\controllers\mch;
use app\models\Printer;
use app\models\PrinterSetting;
use app\modules\user\behaviors\MchBehavior;
use app\modules\user\behaviors\PermissionRoleBehavior;
use app\modules\user\behaviors\UserLoginBehavior;
use app\modules\user\controllers\Controller;
use app\modules\user\models\mch\PrinterForm;
use app\modules\user\models\mch\PrinterListForm;
use app\modules\user\models\mch\PrinterSettingForm;

class PrinterController extends Controller
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

    public function actionList()
    {
        $form = new PrinterListForm();
        $form->store_id = $this->store->id;
        $form->mch_id = $this->mch->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('list', [
            'list' => $arr['list'],
            'pagination' => $arr['pagination'],
            'row_count' => $arr['row_count']
        ]);
    }

    /**
     * 打印机编辑
     */
    public function actionEdit($id = null)
    {
        $model = Printer::findOne(['id' => $id, 'store_id' => $this->store->id, 'is_delete' => 0,'mch_id'=>$this->mch->id]);
        if (!$model) {
            $model = new Printer();
        } else {
            $model->printer_setting = json_decode($model->printer_setting, true);
        }
        if (\Yii::$app->request->isPost) {
            $form = new PrinterForm();
            $form->store_id = $this->store->id;
            $form->model = $model;
            $form->mch_id = $this->mch->id;
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            return $this->render('edit', [
                'model' => $model
            ]);
        }
    }

    /**
     * 打印机删除
     */
    public function actionPrinterDel($id)
    {
        $model = Printer::findOne($id);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '打印机不存在，请刷新重试'
            ];
        }
        if ($model->is_delete == 1) {
            return [
                'code' => 1,
                'msg' => '打印机已删除，请刷新重试'
            ];
        }
        $model->is_delete = 1;
        if ($model->save()) {
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];
        }
    }

    /**
     * 打印设置
     */
    public function actionSetting()
    {
        $list = Printer::findAll(['store_id'=>$this->store->id,'is_delete'=>0,'mch_id'=>$this->mch->id]);
        $model = PrinterSetting::findOne(['store_id'=>$this->store->id,'mch_id'=>$this->mch->id]);
        if (!$model) {
            $model = new PrinterSetting();
            $model->big = 1;
        } else {
            $model->type = json_decode($model->type, true);
        }
        if (\Yii::$app->request->post()) {
            $form = new PrinterSettingForm();
            $form->store_id = $this->store->id;
            $form->model = $model;
            $form->mch_id = $this->mch->id;
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            return $this->render('setting', [
                'list'=>$list,
                'model'=>$model
            ]);
        }
    }
}
