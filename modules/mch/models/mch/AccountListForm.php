<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/2/28
 * Time: 16:26
 */

namespace app\modules\mch\models\mch;

use app\models\AccountMch;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\Model;
use app\models\User;
use app\modules\mch\models\MchModel;
use yii\data\Pagination;

class AccountListForm extends MchModel
{
    public $store_id;
    public $page;
    public $limit;
    public $keyword;
    public $mch_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'mch_id'], 'integer'],
            [['keyword',], 'trim'],
            [['page',], 'default', 'value' => 1,],
            [['limit',], 'default', 'value' => 20,],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $query = AccountMch::find()->where([
                'is_delete' => 0,
                'store_id' => $this->store_id,
            ]);
        if ($this->keyword) {
            $query->andWhere([
                'OR',
                ['LIKE', 'username', $this->keyword],
            ]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $list = $query->orderBy('update_time DESC')->limit($pagination->limit)->offset($pagination->offset)
            ->select('id,username,update_time,mch_json')->asArray()->all();

        foreach ($list as $key => &$item)
        {
            $mc = json_decode($item['mch_json'],true);
            $condition = ['and', ['in', 'id', $mc], ['store_id' => $this->store_id]];
            $mc = Mch::find()->where($condition)->select('realname')->asArray()->all();
            $list[$key]['mc'] = $mc;
        }
        unset($item);
        return [
            'code' => 0,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'adminUrl' => $this->getAdminUrl('shop')
            ],
        ];
    }

    public function delete()
    {
        $mch = AccountMch::findOne($this->mch_id);
        $mch->is_delete = Model::IS_DELETE_TRUE;

        if ($mch->save()) {
            return [
                'code' => 0,
                'msg' => '删除成功',
            ];
        }

        return [
            'code' => 1,
            'msg' => '删除失败',
        ];
    }
}
