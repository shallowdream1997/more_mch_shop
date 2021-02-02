<?php
/**
 * more_mch_shop
 * ==================================================================
 * CopyRight © 2017-2099 广州米袋软件有限公司
 * 官网地址：http://www.mdsoftware.cn
 * 售后技术支持：15017566075
 * ------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下
 * 对本程序代码进行修改和使用；不允许对本程序代码以任何目的的再发布。
 * ==================================================================
 *
 * @ClassName MchLoginForm
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-02 18:34 星期二
 * @Version 1.0
 * @Description
 */

namespace app\modules\shop\models;


use app\models\Mch;

class MchLoginForm extends Model
{
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mch_id' => '店铺ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $mch = Mch::findOne($this->mch_id);

        if (!$mch || $mch->is_delete == 1) {
            return [
                'code' => 1,
                'msg' => '门店店铺不存在',
            ];
        }

        if ($mch->is_lock == 1){
            return [
                'code' => 1,
                'msg' => '门店店铺已被关停',
            ];
        }

        \Yii::$app->mch->login($mch);

        return [
            'code' => 0,
            'msg' => '进入成功',
        ];
    }

}
