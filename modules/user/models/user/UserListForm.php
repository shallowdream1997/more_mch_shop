<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/3
 * Time: 13:59
 */

namespace app\modules\user\models\user;

use app\models\Goods;
use app\models\IntegralOrder;
use app\models\Level;
use app\models\Mch;
use app\models\MsOrder;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PtOrder;
use app\models\Recharge;
use app\models\ReOrder;
use app\models\Shop;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\models\YyOrder;
use app\modules\mch\extensions\Export;
use app\modules\user\models\UserModel;
use yii\data\Pagination;

class UserListForm extends UserModel
{
    public $store_id;
    public $page;
    public $keyword;
    public $is_clerk;
    public $level;
    public $user_id;
    public $mobile;
    public $platform;
    public $fields;
    public $flag;

    public $mch_id;
    public function rules()
    {
        return [
            [['keyword', 'level', 'user_id', 'mobile', 'flag'], 'trim'],
            [['page', 'is_clerk'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['platform','fields'], 'safe']
        ];
    }

    public function search()
    {
        $query = User::find()->alias('u')->where([
            'u.type' => 1,
            'u.store_id' => $this->store_id,
            'u.is_delete' => 0,
//            'u.is_clerk' => 0,
            'u.belong_mch' => $this->mch_id,
        ])->leftJoin(Mch::tableName() . ' m', 'm.id=u.belong_mch')
            ->leftJoin(Level::tableName() . ' l', 'l.level=u.level and l.store_id=' . $this->store_id)
            ->andWhere(['OR', [
                'l.is_delete' => 0], 'l.id IS NULL']);
        if ($this->keyword) {
            $query->andWhere(['LIKE', 'u.nickname', $this->keyword]);
        }
        if (isset($this->platform)) {
            $query->andWhere(['platform' => $this->platform]);
        }
        if ($this->mobile) {
            $query->andWhere([
                'or',
                ['LIKE', 'u.binding', $this->mobile],
                ['like', 'u.contact_way', $this->mobile]
            ]);
        }

        $orderQuery = Order::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'is_cancel' => 0, 'is_recycle' => 0, 'mch_id' => 0])->andWhere('user_id = u.id')->select('count(1)');
        $cardQuery = UserCard::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');

        if ($this->level || $this->level === '0' || $this->level === 0) {
            $query->andWhere(['l.level' => $this->level]);
        }

        $couponQuery = UserCoupon::find()->where(['is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->select([
            'u.*', 'm.name shop_name', 'l.name l_name', 'card_count' => $cardQuery, 'order_count' => $orderQuery, 'coupon_count' => $couponQuery
        ])->limit($pagination->limit)->offset($pagination->offset)->orderBy('u.addtime DESC')->asArray()->all();

        foreach ($list as &$item){
            $item['p_name'] = $this->getParentName($item['parent_id']);
        }
        return [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'pagination' => $pagination,
            'list' => $list,
        ];
    }

    /**
     * @param $id
     * @return string
     * 查询归属上级
     */
    public function getParentName($id)
    {
        if ($id == 0){
            $parent_name = '无归属店员';
        }else {
            $parent = User::findOne($id);
            $parent_name = $parent->nickname;
        }
        return $parent_name;
    }

}
