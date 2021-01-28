<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1
 * Time: 11:26
 */

namespace app\modules\user\models\mch;

use app\models\Printer;
use app\modules\user\models\UserModel;
use yii\data\Pagination;

class PrinterListForm extends UserModel
{
    public $store_id;

    public $page;
    public $limit;
    public $keyword;
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'],'integer'],
            [['page'],'default','value'=>1],
            [['page'],'default','value'=>20],
            [['keyword'],'string'],
            [['keyword'],'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = Printer::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'mch_id'=>$this->mch_id]);

        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>$this->limit]);
        $list = $query->offset($p->offset)->limit($p->limit)->orderBy(['addtime'=>SORT_DESC])->asArray()->all();

        return [
            'list'=>$list,
            'pagination'=>$p,
            'row_count'=>$count
        ];
    }
}
