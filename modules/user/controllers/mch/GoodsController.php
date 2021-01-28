<?php

/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/12/28
 * Time: 15:53
 */

namespace app\modules\user\controllers\mch;

use app\models\AttrModel;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\Mch;
use app\models\MchCat;
use app\models\MchGoodsCat;
use app\models\OrderMessage;
use app\models\tplmsg\AdminTplMsgSender;
use app\modules\mch\models\goods\Taobaocsv;
use app\modules\mch\models\ParametersForm;
use app\modules\mch\models\ParametersSearchForm;
use app\modules\user\behaviors\MchBehavior;
use app\modules\user\behaviors\PermissionRoleBehavior;
use app\modules\user\behaviors\UserLoginBehavior;
use app\modules\user\controllers\Controller;
use app\modules\user\models\mch\CatEditForm;
use app\modules\user\models\mch\CatListForm;
use app\modules\user\models\mch\GoodsDetailForm;
use app\modules\user\models\mch\GoodsEditForm;
use app\utils\CurlHelper;
use yii\data\Pagination;

class GoodsController extends Controller
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

    public function actionIndex($keyword = null, $cat_id = null)
    {
        $keyword = trim($keyword);
        $query = Goods::find()->where(['mch_id' => $this->mch->id, 'is_delete' => 0]);
        if ($keyword) {
            $query->andWhere(['LIKE', 'name', $keyword]);
        }
        if ($cat_id) {
            $sub_query = MchGoodsCat::find()->select('goods_id')->where(['cat_id' => $cat_id]);
            $query->andWhere(['id' => $sub_query]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $list = $query->select('id,name,cover_pic,status,attr,mch_sort')
            ->orderBy('mch_sort,addtime DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
            'keyword' => $keyword,
            'cat_list' => MchCat::find()->where(['mch_id' => $this->mch->id, 'is_delete' => 0, 'parent_id' => 0])->orderBy('sort,addtime DESC')->all(),
            'get' => \Yii::$app->request->get(),
            'is_store' => $this->mch->is_store,
        ]);
    }

    /**
     * @return false|string
     * 获取总平台商品列表
     */
    public function actionStoreGoodsList()
    {
        $keyword = trim(\Yii::$app->request->get('keyword'));
        $query = Goods::find()->where(['store_id'=>$this->store->id,'mch_id'=>0,'is_delete'=>0,'status'=>1]);
        if ($keyword) {
            $query->andWhere([
                'or',
                ['LIKE', 'name', $keyword],
            ]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')->asArray()->all();
        return \Yii::$app->serializer->encode($list);
    }

    /**
     * @return array
     * 从总平台导入商品
     */
    public function actionImportGoodsList()
    {
        $goods_list_id = \Yii::$app->request->get('goods_list_id');
        $condition = ['and', ['in', 'id', $goods_list_id], ['store_id' => $this->store->id,'mch_id'=>0]];

        $isExist = Goods::find()->where(['and',['in','parent_id',$goods_list_id],['store_id' => $this->store->id,'mch_id' => $this->mch->id]])->asArray()->exists();
        if ($isExist){
            return [
                'code' => 1,
                'msg' => '导入的商品有已经存在的商品'
            ];
        }
        $query = Goods::find()->where($condition)->asArray()->all();
        foreach ($query as &$item){
            $item['status'] = 0;
            $item['mch_id'] = $this->mch->id;
            $GPic[] = GoodsPic::find()->where(['goods_id'=>$item['id'],'is_delete'=>0])->select('pic_url')->asArray()->all();
            $item['parent_id'] = $item['id'];
            unset($item['id']);
        }
        $queryInsert = \Yii::$app->db->createCommand()->batchInsert(Goods::tableName(),['parent_id', 'store_id', 'name', 'price', 'original_price', 'detail', 'cat_id', 'status', 'addtime', 'is_delete', 'attr', 'service', 'sort', 'virtual_sales', 'cover_pic', 'video_url', 'unit', 'individual_share', 'share_commission_first', 'share_commission_second', 'share_commission_third', 'weight', 'freight', 'full_cut', 'integral', 'use_attr', 'share_type', 'quick_purchase', 'hot_cakes', 'cost_price', 'member_discount', 'rebate', 'mch_id', 'goods_num', 'mch_sort', 'confine_count', 'is_level', 'type', 'is_negotiable', 'attr_setting_type', 'is_attrbute_use', 'attrbute_id', 'attrbute_json', 'is_special', 'is_recommend', 'labels'],$query)->execute();

        //获取批量插入的第一个主键ID
        $firstId = \Yii::$app->db->getLastInsertID();
        //获取批量插入的最后一个主键ID
        $lastId = bcadd($firstId,$queryInsert,0) - 1;
        $j = 0;
        for ($i = $firstId;$i <= $lastId;$i++)
        {
            //循环遍历每个主键的goods_pic
            foreach ($GPic[$j] as $k => $it){
                $GPic[$j][$k]['goods_id'] = $i;
            }
            //批量插入图片
            \Yii::$app->db->createCommand()->batchInsert(GoodsPic::tableName(),['pic_url','goods_id'],$GPic[$j])->execute();
            $j++;
        }
        if ($queryInsert){
            return [
                'code' => 0,
                'msg' => '导入成功'
            ];
        }else{
            return [
                'code' => 1,
                'msg' => $queryInsert->getErrors()
            ];
        }
    }

    public function actionEdit($id = null)
    {
        $model = Goods::findOne(['id' => $id, 'mch_id' => $this->mch->id, 'is_delete' => 0]);
        if (!$model) {
            $model = new Goods();
            $model->mch_id = $this->mch->id;
            $model->store_id = $this->mch->store_id;
        }
        $form = new GoodsEditForm();
        $form->model = $model;
        $form->mch = $this->mch;
        if (\Yii::$app->request->isPost) {
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        } else {
            if (\Yii::$app->request->isAjax) {
                return $form->search();
            } else {
                $cat_list_form = new CatListForm();
                $cat_list_form->mch_id = $this->mch->id;
                return $this->render('edit');
            }
        }
    }

    //自动加载获取模型参数
    public function actionGetModelsList($goods_id = 0,$models_id = 0)
    {
        $goods = Goods::findOne($goods_id);
        if ($goods->attrbute_id == $models_id){
            $models = json_decode($goods->attrbute_json,true);
        }else{
            $list = AttrModel::find()->select('model_group_json')
                ->where(['mch_id' => $this->mch->id,'is_use' => 1,'store_id' => $this->store->id,'id' => $models_id])->one();
            $models = json_decode($list->model_group_json,true);
        }

        return [
            'code' => 0,
            'data' => $models,
        ];
    }

    /**
     * @param null $id
     * @return string
     * 商品信息详情页
     */
    public function actionGoodsDetail()
    {
        $form = new GoodsDetailForm();
        $form->store_id = $this->store->id;
        $form->goods_id = \Yii::$app->request->get('goods_id');
        $arr = $form->search();
        return $this->render('goods-detail', $arr);
    }

    /**
     * 商品删除
     */
    public function actionDelete($id)
    {
        $model = Goods::findOne([
            'id' => $id,
            'mch_id' => $this->mch->id,
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '商品不存在',
            ];
        }
        $model->is_delete = 1;
        if ($model->save()) {
            return [
                'code' => 0,
                'msg' => '删除成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '删除失败',
            ];
        }
    }

    /**
     * 分类列表
     */
    public function actionCat()
    {
        $cat_list = MchCat::find()->where(['mch_id' => $this->mch->id, 'is_delete' => 0, 'parent_id' => 0])->orderBy('sort,addtime DESC')->all();
        return $this->render('cat', [
            'cat_list' => $cat_list,
        ]);
    }

    /**
     * 分类编辑
     */
    public function actionCatEdit($id = null)
    {
        $cat = MchCat::findOne(['id' => $id, 'mch_id' => $this->mch->id, 'is_delete' => 0]);
        if (!$cat) {
            $cat = new MchCat();
            $cat->mch_id = $this->mch->id;
        }
        if (\Yii::$app->request->isPost) {
            $form = new CatEditForm();
            $form->attributes = \Yii::$app->request->post('model');
            $form->model = $cat;
            return $form->save();
        }
        $parent_list_query = MchCat::find()->where([
            'mch_id' => $this->mch->id,
            'is_delete' => 0,
            'parent_id' => 0,
        ]);
        if (!$cat->isNewRecord && $cat->parent_id == 0) {
            $parent_list_query->andWhere([
                'id' => -1,
            ]);
        }
        $parent_list = $parent_list_query->all();
        return $this->render('cat-edit', [
            'parent_list' => $parent_list,
            'list' => $cat,
        ]);
    }

    /**
     * 分类删除
     */
    public function actionCatDel($id)
    {
        $model = MchCat::findOne([
            'id' => $id,
            'mch_id' => $this->mch->id,
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '分类不存在',
            ];
        }
        $model->is_delete = 1;
        if ($model->save()) {
            return [
                'code' => 0,
                'msg' => '删除成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '删除失败',
            ];
        }
    }

    /**
     * 改变商品上下架状态
     */
    public function actionSetStatus($id, $status)
    {
        $model = Goods::findOne([
            'id' => $id,
            'mch_id' => $this->mch->id,
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '商品不存在',
            ];
        }
        if ($this->mch->is_store != 1){
            if ($status != 0) {
                return [
                    'code' => 1,
                    'msg' => '系统错误，请刷新重试',
                ];
            }
        }
        $model->status = $status == 1 ? 1 : 0;
        if ($model->status == 1 && !$model->getNum()) {
            return [
                'code' => 1,
                'msg' => '商品库存为0上架失败，请先设置商品库存',
            ];
        }
        if ($model->save()) {
            return [
                'code' => 0,
                'msg' => $model->status == 1 ? '上架成功' : '下架成功',
            ];
        } else {
            return [
                'code' => 0,
                'msg' => $status == 1 ? '上架失败' : '下架失败',
            ];
        }
    }

    public function actionUpdateGoodsNum($offset)
    {
        /** @var Goods[] $list */
        $list = Goods::find()->select('id,attr,goods_num')->where(['mch_id' => $this->mch->id, 'is_delete' => 0])
            ->offset($offset)->limit(10)->all();
        foreach ($list as $item) {
            $item->updateAttributes([
                'goods_num' => $item->getNum(),
            ]);
        }
        if (!is_array($list) || !count($list)) {
            return [
                'code' => 0,
                'msg' => '更新完成',
            ];
        } else {
            return [
                'code' => 0,
                'msg' => 'success',
                'continue' => 1,
            ];
        }
    }

    public function actionApply($id)
    {
        $model = Goods::findOne([
            'id' => $id,
            'mch_id' => $this->mch->id,
            'store_id' => $this->store->id,
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '商品不存在',
            ];
        }
        OrderMessage::set($model->id, $model->store_id, 4, 1);
        AdminTplMsgSender::sendMchUploadGoods($this->store->id, [
            'goods' => $model->name,
        ]);
        return [
            'code' => 0,
            'msg' => '提交成功',
        ];
    }

    /**
     * @param int $mall_id
     * 拉取商城商品数据
     */
    public function actionGoodsCopy($mall_id = 0)
    {
        $goods = Goods::findOne(['id' => $mall_id, 'is_delete' => 0, 'store_id' => $this->store->id,'mch_id'=>$this->mch->id]);
        if (!$goods) {
            return [
                'code' => 1,
                'msg' => '商品不存在,或已删除',
            ];
        }

        $goodsPic = GoodsPic::find()->select('pic_url')->andWhere(['goods_id' => $goods->id, 'is_delete' => 0])->asArray()->column();

        return [
            'code' => 0,
            'msg' => '成功',
            'data' => [
                'name' => $goods->name,
                'virtual_sales' => $goods->virtual_sales,
                'original_price' => $goods->original_price,
                'price' => $goods->price,
                'pic' => $goodsPic,
                'cover_pic' => $goods->cover_pic,
                'unit' => $goods->unit,
                'weight' => $goods->weight,
                'detail' => $goods->detail,
                'service' => $goods->service,
                'mch_sort' => $goods->mch_sort,
                'freight' => $goods->freight,
                'attr_group_list' => \Yii::$app->serializer->encode($goods->getAttrData()),
                'checked_attr_list' => \Yii::$app->serializer->encode($goods->getCheckedAttrData()),
                'use_attr' => $goods->use_attr,
                'attr' => $goods->attr,
            ],
        ];
    }

    // 淘宝CSV上传
    public function actionTaobaoCopy()
    {
        if(\Yii::$app->request->isPost){
            $form = new Taobaocsv();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->mch_id = $this->mch->id;
            $res = $form->search();
            return $res;
        }
        return $this->render('@app/modules/mch/views/goods/taobao-copy');
    }

    /**
     * @return string
     * 产品参数列表
     */
    public function actionParameters($keyword = null,$status = null)
    {
        $form = new \app\modules\user\models\mch\ParametersSearchForm();
        $form->store = $this->store;
        $form->keyword = $keyword;
        $form->status = $status;
        $form->plugin = get_plugin_type();
        $form->mch_id = $this->mch->id;
        $res = $form->getList();

        return $this->render('parameters',[
            'list' => $res['list'],
            'pagination' => $res['pagination'],
        ]);
    }

    /**
     * @param int $id
     * 删除模型
     */
    public function actionParametersDel($id = 0)
    {

        $goods = Goods::findOne(['attrbute_id' => $id,'mch_id'=>$this->mch->id]);

        if ($goods)
        {
            return [
                'code' => 1,
                'msg' => '有商品正在使用该模型，无法删除'
            ];
        }else{
            AttrModel::updateAll(['is_delete' => 1],['id' => $id]);
            return [
                'code' =>0,
                'msg' => '删除成功',
            ];
        }
    }

    public function actionParametersNewEdit($id = 0)
    {
        $models = AttrModel::findOne(['id' => $id, 'store_id' => $this->store->id, 'mch_id' => $this->mch->id]);
        if (!$models) {
            $models = new AttrModel();
        }
        $form = new \app\modules\user\models\mch\ParametersForm();
        if (\Yii::$app->request->isPost) {
            $model = \Yii::$app->request->post('model');
            $model['store_id'] = $this->store->id;
            $form->attributes = $model;
            $form->attr = \Yii::$app->request->post('attr');

            $form->mch_id = $this->mch->id;
            $form->models = $models;
            return $form->save();
        }

        $searchForm = new ParametersSearchForm();
        $searchForm->models = $models;
        $searchForm->store = $this->store;
        $list = $searchForm->search();

        return $this->render('parameters-new-edit', [
            'models' => $list['models'],
        ]);
    }

    //模型启动和关闭
    public function actionAttrbuteUpDown($id = 0, $type = 'down')
    {
        if ($type == 'down') {
            $goods = Goods::findOne(['attrbute_id' => $id,'mch_id'=>$this->mch->id]);
            if ($goods) {
                return [
                    'code' => 1,
                    'msg' => '有商品正在使用该模型，无法关闭',
                ];
            }
            $attr = AttrModel::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id,'mch_id'=>$this->mch->id]);
            $attr->is_use = 0;
        } elseif ($type == 'up') {
            $attr = AttrModel::findOne(['id' => $id, 'is_delete' => 0, 'is_use' => 0, 'store_id' => $this->store->id,'mch_id'=>$this->mch->id]);

            if (!$attr) {
                return [
                    'code' => 1,
                    'msg' => '商品已删除或已上架',
                ];
            }
            $attr->is_use = 1;
        } else {
            return [
                'code' => 1,
                'msg' => '参数错误',
            ];
        }
        if ($attr->save()) {
            return [
                'code' => 0,
                'msg' => '成功',
            ];
        } else {
            foreach ($attr->errors as $errors) {
                return [
                    'code' => 1,
                    'msg' => $errors[0],
                ];
            }
        }
    }


    /**
     * 批量设置
     */
    public function actionBatch()
    {
        $get = \Yii::$app->request->get();
        $res = 0;
        $goods_group = $get['goods_group'];
        $goods_id_group = [];
        foreach ($goods_group as $index => $value) {
            if ($get['type'] == 0) {
                if ($value['num'] != 0) {
                    array_push($goods_id_group, $value['id']);
                }
            } else {
                array_push($goods_id_group, $value['id']);
            }
        }

        $condition = ['and', ['in', 'id', $goods_id_group], ['store_id' => $this->store->id,'mch_id'=>$this->mch->id]];
        $msg = '请刷新重试';
        if ($get['type'] == 5) { //批量启动模型
            $res = AttrModel::updateAll(['is_use' => 1], $condition);
        } elseif ($get['type'] == 6) { //批量关闭模型
            $goods = Goods::findAll(['attrbute_id'=>$goods_id_group,'store_id' => $this->store->id,'mch_id'=>$this->mch->id]);
            if ($goods) {
                return [
                    'code' => 0,
                    'msg' => '已有商品在使用模型，无法关闭',
                ];
            }else{
                $res = AttrModel::updateAll(['is_use' => 0], $condition);
            }

        } elseif ($get['type'] == 7) { //批量删除模型
            $goods = Goods::findAll(['attrbute_id'=>$goods_id_group,'store_id' => $this->store->id,'mch_id'=>$this->mch->id]);
            if ($goods) {
                return [
                    'code' => 0,
                    'msg' => '已有商品在使用模型，无法删除',
                ];
            }else{
                $res = AttrModel::updateAll(['is_delete' => 1], $condition);
            }
        }
        if ($res > 0) {
            return [
                'code' => 0,
                'msg' => '设置成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $msg
            ];
        }
    }

}
