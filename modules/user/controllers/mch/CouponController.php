<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/8/24
 * Time: 10:15
 */

namespace app\modules\user\controllers\mch;

use app\models\AttrModel;
use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\Goods;
use app\models\Mch;
use app\models\MchCat;
use app\models\MchGoodsCat;
use app\models\OrderMessage;
use app\models\tplmsg\AdminTplMsgSender;
use app\models\User;
use app\modules\mch\models\goods\Taobaocsv;
use app\modules\mch\models\ParametersForm;
use app\modules\mch\models\ParametersSearchForm;
use app\modules\user\behaviors\MchBehavior;
use app\modules\user\behaviors\PermissionRoleBehavior;
use app\modules\user\behaviors\UserLoginBehavior;
use app\modules\user\controllers\Controller;
use app\modules\user\models\mch\CatEditForm;
use app\modules\user\models\mch\CatListForm;
use app\modules\user\models\mch\CouponEditForm;
use app\modules\user\models\mch\CouponSendForm;
use app\modules\user\models\mch\GoodsDetailForm;
use app\modules\user\models\mch\GoodsEditForm;
use yii\data\Pagination;
class CouponController extends Controller
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

    //优惠券列表
    public function actionIndex()
    {
        $list = Coupon::find()->where(['store_id' => $this->store->id, 'is_delete' => 0,'mch_id'=>$this->mch->id])->orderBy('sort ASC')->all();
        return $this->render('index', [
            'list' => $list,
        ]);
    }

    //优惠券编辑
    public function actionEdit($id = null)
    {
        $model = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        $cat_id = json_decode($model->cat_id_list);
        $cat = MchCat::find()->where(['is_delete' => 0, 'id' => $cat_id,'mch_id'=>$this->mch->id])->all();
        $goods_id = json_decode($model->goods_id_list);
        $goods = Goods::find()->where(['store_id' => $this->store->id,'mch_id'=>$this->mch->id, 'is_delete' => 0, 'status' => 1, 'id' => $goods_id])->all();
        if (!$model) {
            $model = new Coupon();
        }
        if (\Yii::$app->request->isPost) {
            $form = new CouponEditForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->coupon = $model;
            $form->mch_id = $this->mch->id;
            return $form->save();
        } else {
            foreach ($model as $index => $value) {
                if (in_array($index, ['cat_id_list'])) {
                    continue;
                }
                $model[$index] = str_replace("\"", "&quot;", $value);
            }
            return $this->render('edit', [
                'model' => $model,
                'cat' => $cat,
                'goods' => $goods,
            ]);
        }
    }

    public function actionDeleteCat()
    {
        $cat_id = \Yii::$app->request->get();
        $coupon = Coupon::findOne([
            'id' => $cat_id['coupon_id'],
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'mch_id'=>$this->mch->id
        ]);
        if (!$coupon) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send();
            return;
        }
        $cat_id_list = json_decode($coupon->cat_id_list);

        foreach ($cat_id_list as $key => $value) {
            if ($value == $cat_id['cat_id']) {
                unset($cat_id_list[$key]);
            }
        }
        $coupon->cat_id_list = json_encode(array_values($cat_id_list), JSON_UNESCAPED_UNICODE);
        if ($coupon->save()) {
            return [
                'code' => 0,
            ];
        } else {
            return [
                'code' => 1,
            ];
        }
    }

    public function actionDeleteGoods()
    {
        $goods_id = \Yii::$app->request->get();
        $coupon = Coupon::findOne([
            'id' => $goods_id['coupon_id'],
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'mch_id'=>$this->mch->id
        ]);
        if (!$coupon) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send();
            return;
        }
        $goods_id_list = json_decode($coupon->goods_id_list);

        foreach ($goods_id_list as $key => $value) {
            if ($value == $goods_id['goods_id']) {
                unset($goods_id_list[$key]);
            }
        }
        $coupon->goods_id_list = json_encode(array_values($goods_id_list), JSON_UNESCAPED_UNICODE);
        if ($coupon->save()) {
            return [
                'code' => 0,
            ];
        } else {
            return [
                'code' => 1,
            ];
        }
    }

//    查找商品分类
    public function actionSearchCat($keyword)
    {
        $keyword = trim($keyword);
        $query = MchCat::find()->alias('c')->where([
            'AND',
            ['LIKE', 'c.name', $keyword],
            ['is_delete' => 0,'mch_id'=>$this->mch->id],
        ]);
        $list = $query->orderBy('c.name')->limit(30)->asArray()->select('id,icon,name')->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => (object)[
                'list' => $list,
            ],
        ];
    }

//    查找商品
    public function actionSearchGoods($keyword)
    {
        $keyword = trim($keyword);
        $query = Goods::find()->alias('c')->where([
            'AND',
            ['LIKE', 'c.name', $keyword],
            ['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1,'mch_id'=>$this->mch->id],
        ]);
        $list = $query->orderBy('c.name')->limit(30)->asArray()->select('id,cover_pic,name')->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => (object)[
                'list' => $list,
            ],
        ];
    }

    //优惠券删除
    public function actionDelete($id)
    {
        $model = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'mch_id'=>$this->mch->id
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
            CouponAutoSend::updateAll(['is_delete' => 1], ['coupon_id' => $model->id]);
        }
        return [
            'code' => 0,
            'msg' => '操作成功',
        ];
    }

    //优惠券发放
    public function actionSend($id)
    {
        $coupon = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'mch_id'=>$this->mch->id
        ]);
        if (!$coupon) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send();
            return;
        }
        if (\Yii::$app->request->isPost) {
            $form = new CouponSendForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->coupon_id = $coupon->id;
            $form->mch_id = $this->mch->id;
            return $form->save();
        } else {
            return $this->render('send', [
                'coupon' => $coupon,
            ]);
        }
    }

    //查找用户
    public function actionSearchUser($keyword)
    {
        $keyword = trim($keyword);
        $query = User::find()->alias('u')->where([
            'AND',
            ['or',['LIKE', 'u.nickname', $keyword],['u.id' => $keyword]],
            ['store_id' => $this->store->id, 'u.type' => 1,'belong_mch'=>$this->mch->id],
        ]);
        $list = $query->orderBy('u.nickname')->limit(30)->asArray()->select('id,nickname,avatar_url')->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => (object)[
                'list' => $list,
            ],
        ];
    }

    //自动发放
    public function actionAutoSend()
    {
        $list = CouponAutoSend::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ])->orderBy('addtime DESC')->all();
        return $this->render('auto-send', [
            'list' => $list,
        ]);
    }

    //自动发放编辑
    public function actionAutoSendEdit($id = null)
    {
        $model = CouponAutoSend::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            $model = new CouponAutoSend();
        }
        if (\Yii::$app->request->isPost) {
            $coupon = Coupon::findOne([
                'id' => \Yii::$app->request->post('coupon_id'),
                'store_id' => $this->store->id,
                'is_delete' => 0,
                'mch_id'=>$this->mch->id
            ]);
            if (!$coupon) {
                return [
                    'code' => 1,
                    'msg' => '优惠券不存在或已删除，请刷新页面后重试',
                ];
            }

            $model->event = \Yii::$app->request->post('event');
            $model->coupon_id = $coupon->id;
            $model->send_times = \Yii::$app->request->post('send_times');
            if ($model->send_times === '' || $model->send_times === null) {
                return [
                    'code' => 1,
                    'msg' => '最多发放次数不能为空',
                ];
            }
            if($model->send_times >99999999){
                return [
                    'code' => 1,
                    'msg' => '最多发放次数不能超过99999999'
                ];
            }
            if ($model->isNewRecord) {
                $model->store_id = $this->store->id;
                $model->addtime = time();
                $model->is_delete = 0;
            }
            if ($model->save()) {
                return [
                    'code' => 0,
                    'msg' => '保存成功',
                ];
            } else {
                return $model->errorResponse;
            }
        } else {
            $coupon_list = Coupon::find()->where(['store_id' => $this->store->id, 'is_delete' => 0,'mch_id'=>$this->mch->id])->all();
            return $this->render('auto-send-edit', [
                'model' => $model,
                'coupon_list' => $coupon_list,
            ]);
        }
    }

    //自动发放方案删除
    public function actionAutoSendDelete($id)
    {
        $model = CouponAutoSend::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
        }
        return [
            'code' => 0,
            'msg' => '操作成功',
        ];
    }
}
