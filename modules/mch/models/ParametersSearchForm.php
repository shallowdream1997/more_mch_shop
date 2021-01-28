<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/7/13
 * Time: 9:42
 */

namespace app\modules\mch\models;

use app\models\AttributeValue;
use app\models\AttrModel;
use app\models\Card;
use app\models\Cat;
use app\models\Goods;
use app\models\GoodsAttribute;
use app\models\GoodsCat;
use app\models\Model;
use app\models\PostageRules;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * app\models\Goods $goods
 * app\models\Store $store
 */
class ParametersSearchForm extends MchModel
{
    public $parameters;
    public $store;
    public $keyword;
    public $status;
    public $plugin;
    public $limit = 10;
    public $page = 1;

    public $models;
    // 编辑产品模型查询
    public function search()
    {
        $models = $this->models;
        return [
            'models' => $models,
        ];
    }


    // 产品模型列表数据
    public function getList()
    {
        $keyword = $this->keyword;
        $status = $this->status;
        $query = AttrModel::find()->where(['store_id' => $this->store->id,'is_delete' => 0,'mch_id'=>0]);

        $query->select(new Expression('*'));

        if (trim($keyword)) {
            $query->andWhere(['LIKE', 'model_name', $keyword]);
        }

        if (isset($status)) {
            $query->andWhere(['is_use' => $status]);
        }

        $count = $query->count();

        $pagination = new Pagination(['totalCount' => $count, 'route' => \Yii::$app->requestedRoute]);

        $list = $query->orderBy('sort ASC')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()
            ->all();

        return [
            'list' => $list,
//            'parameterList' => $parameterList,
            'pagination' => $pagination
        ];
    }


    public function goodsSearch()
    {
        $query = Goods::find()->where([
            'store_id' => $this->getCurrentStoreId(),
            'is_delete' => Model::IS_DELETE_FALSE,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $list = $query->asArray()->limit($pagination->limit)->offset($pagination->offset)->orderBy('id DESC')->all();

        return [
            'code' => 0,
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }
}