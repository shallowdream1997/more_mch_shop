<?php

namespace app\models;

use app\models\common\admin\log\CommonActionLog;
use Yii;

/**
 * This is the model class for table "{{%mch}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $cash_user_id
 * @property integer $addtime
 * @property integer $is_delete
 * @property integer $is_open
 * @property integer $is_lock
 * @property integer $review_status
 * @property string $review_result
 * @property integer $review_time
 * @property string $realname
 * @property string $tel
 * @property string $name
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $district_id
 * @property string $address
 * @property integer $mch_common_cat_id
 * @property string $service_tel
 * @property string $logo
 * @property string $header_bg
 * @property integer $transfer_rate
 * @property string $account_money
 * @property integer $sort
 * @property string $wechat_name
 * @property integer $is_recommend
 * @property string $longitude
 * @property string $latitude
 * @property string $main_content
 * @property string $summary
 * @property integer $score
 * @property string $shop_time
 * @property integer $is_default
 * @property string $business_type_text
 * @property string $code
 * @property integer $account_shop_id
 * @property integer $is_store
 * @property integer $province
 * @property integer $city
 * @property integer $district
 * @property integer $account_id
 * @property string $account_shop_money
 * @property integer $account_shop_province_id
 * @property integer $account_shop_city_id
 * @property integer $account_shop_area_id
 * @property string $update_time
 */
class Mch extends \yii\db\ActiveRecord
{
    /**
     * 商户店铺状态：开启
     */
    const IS_OPEN_TRUE = 1;

    /**
     * 商户店铺状态：关闭
     */
    const IS_OPEN_FALSE = 0;

    /**
     * 好店推荐：推荐
     */
    const IS_RECOMMEND_TRUE = 1;

    /**
     * 好店推荐：不推荐
     */
    const IS_RECOMMEND_FALSE = 0;

    /**
     * 默认一小时总店门店ID
     */
    const IS_ACCOUNT_SHOP_ID = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mch}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'realname', 'name', 'province_id', 'city_id', 'district_id'], 'required'],
            [['store_id', 'user_id', 'cash_user_id', 'addtime', 'is_delete', 'is_open', 'is_lock', 'review_status', 'review_time', 'province_id', 'city_id', 'district_id', 'mch_common_cat_id', 'transfer_rate', 'sort', 'is_recommend', 'score', 'is_default', 'account_shop_id','is_store','account_id','account_shop_province_id','account_shop_city_id','account_shop_area_id'], 'integer'],
            [['review_result', 'logo', 'header_bg'], 'string'],
            [['account_money','account_shop_money','score','longitude', 'latitude'], 'number'],
            [['realname', 'tel', 'name', 'wechat_name', 'main_content', 'summary', 'shop_time', 'business_type_text', 'code','province','city','district','address'], 'string', 'max' => 255],
            [['address', 'service_tel','update_time'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'user_id' => 'User ID',
            'cash_user_id' => '首次绑定门店微信提现用户',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
            'is_open' => '是否营业：0=否，1=是',
            'is_lock' => '是否被系统关闭：0=否，1=是',
            'review_status' => '审核状态：0=待审核，1=审核通过，2=审核不通过',
            'review_result' => '审核结果',
            'review_time' => '审核时间',
            'realname' => 'Realname',
            'tel' => 'Tel',
            'name' => 'Name',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
            'address' => 'Address',
            'mch_common_cat_id' => '所售类目',
            'service_tel' => '客服电话',
            'logo' => 'logo',
            'header_bg' => '背景图',
            'transfer_rate' => '商户手续费',
            'account_money' => '商户余额',
            'sort' => '排序：升序',
            'wechat_name' => '微信号',
            'is_recommend' => '好店推荐：0.不推荐|1.推荐',
            'longitude' => '经度',
            'latitude' => '纬度',
            'main_content' => '主营范围',
            'summary' => 'Summary',
            'score' => '评分 1~5',
            'shop_time' => '营业时间',
            'is_default' => '是否设为单商户主门店 0--否 1--是 （单商户只能设置一个门店为主门店）',
            'business_type_text' => '经营模式',
            'code' => '门店编码',
            'account_shop_id' => '一小时系统门店id',
            'is_store' => '商城进入是否是默认门店 （0-否 1-是）',
            'province' => '省名称',
            'city' => '市名称',
            'district' => '区名称',
            'account_id' => '一小时系统 账户id',
            'account_shop_money' => '一小时对接账户余额',
            'update_time' => '更新字段',
        ];
    }

    public function getSetting()
    {
        return $this->hasOne(MchSetting::className(), ['mch_id' => 'id']);
    }

    public function getPlugin()
    {
        return $this->hasOne(MchPlugin::className(), ['mch_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return $this->hasMany(Order::className(), ['mch_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $data = $insert ? json_encode($this->attributes) : json_encode($changedAttributes);
        CommonActionLog::storeActionLog('', $insert, $this->is_delete, $data, $this->id);
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(), ['mch_id' => 'id']);
    }
    public static function getMchName($mch_id)
    {
        return Mch::findOne(['id'=>$mch_id]);
    }
}
