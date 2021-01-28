<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/8/3
 * Time: 13:52
 */

namespace app\modules\mch\models;

use app\hejiang\ApiResponse;
use app\models\IntegralOrder;
use app\models\Level;
use app\models\Mch;
use app\models\MsOrder;
use app\models\Order;
use app\models\PtOrder;
use app\models\Shop;
use app\models\UserCoupon;
use app\models\User;
use app\models\UserCard;
use app\models\YyOrder;
use app\modules\mch\extensions\Export;
use yii\data\Pagination;

class MemberListForm extends MchModel
{
    public $store_id;
    public $page;
    public $keyword;
    public $level;
    public $user_id;
    public $mobile;
    public $platform;
    public $fields;
    public $flag;
    public $uid;

    public function rules()
    {
        return [
            [['keyword', 'user_id', 'mobile', 'flag','uid'], 'trim'],
            [['page','uid'], 'integer'],
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
        ])->leftJoin(Shop::tableName() . ' s', 's.id=u.shop_id')
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

        if ($this->flag === Export::EXPORT) {
            $newQuery = clone $query;
            $newQuery->select([
                'u.*', 's.name shop_name'
            ])->orderBy('u.addtime DESC');
            $export = new ExportList();
            $export->fields = $this->fields;
            $export->UserExportData($newQuery);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->select([
            'u.*', 's.name shop_name'
        ])->limit($pagination->limit)->offset($pagination->offset)->orderBy('u.addtime DESC')->asArray()->all();

        $mch_list = Mch::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'review_status'=>1,'is_open'=>Mch::IS_OPEN_TRUE,'is_lock'=>0])
            ->groupBy('user_id')
            ->orderBy('addtime DESC')
            ->select('id,user_id,name')
            ->asArray()->all();
        return [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'pagination' => $pagination,
            'list' => $list,
            'mch_list' => $mch_list,
        ];
    }


    public function getUser()
    {
        $query = User::find()->where([
            'type' => 1,
            'store_id' => $this->store_id,
            'is_clerk' => 0,
            'is_delete' => 0
        ]);
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['LIKE', 'nickname', $this->keyword],
                ['LIKE', 'wechat_open_id', $this->keyword]
            ]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')->asArray()->all();
//        $list = $query->orderBy('addtime DESC')->asArray()->all();

        return $list;
    }

    public function getMch()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = Mch::find()->alias('m')->leftJoin(['u'=>User::tableName()],'u.id=m.user_id')
            ->where(['m.store_id'=>$this->store_id,'m.is_delete'=>0,'m.review_status'=>1,'m.is_open'=>Mch::IS_OPEN_TRUE,'m.is_lock'=>0,'u.is_delete'=>0])
            ->andWhere(['!=','u.id',$this->user_id]);
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'm.name', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        $list = $query->select('u.id,m.header_bg,u.nickname,m.name')->limit(10)->asArray()->all();
        return new ApiResponse(0, '', [
            'list' => $list
        ]);
    }


    public function excelFields()
    {
        $list = [
            [
                'key' => 'id',
                'value' => '用户ID',
                'selected' => 0,
            ],
            [
                'key' => 'open_id',
                'value' => '用户open_id',
                'selected' => 0,
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
                'selected' => 0,
            ],
            [
                'key' => 'binding',
                'value' => '绑定手机号',
                'selected' => 0,
            ],
            [
                'key' => 'contact_way',
                'value' => '联系方式',
                'selected' => 0,
            ],
            [
                'key' => 'comments',
                'value' => '备注',
                'selected' => 0,
            ],
            [
                'key' => 'addtime',
                'value' => '加入时间',
                'selected' => 0,
            ],
            [
                'key' => 'identity',
                'value' => '会员身份',
                'selected' => 0,
            ],
            [
                'key' => 'order_count',
                'value' => '订单数',
                'selected' => 0,
            ],
            [
                'key' => 'consume_count',
                'value' => '总消费',
                'selected' => 0,
            ],
            [
                'key' => 'coupon_count',
                'value' => '优惠券总数',
                'selected' => 0,
            ],
            [
                'key' => 'card_num',
                'value' => '卡券总数',
                'selected' => 0,
            ],
            [
                'key' => 'integral',
                'value' => '积分',
                'selected' => 0,
            ],
            [
                'key' => 'money',
                'value' => '余额',
                'selected' => 0,
            ],
        ];

        return $list;
    }
}
