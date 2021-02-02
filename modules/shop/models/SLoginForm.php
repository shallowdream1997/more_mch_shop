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
 * @ClassName SLoginForm
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-02 15:59 星期二
 * @Version 1.0
 * @Description 登陆验证form
 */

namespace app\modules\shop\models;


use app\models\AccountMch;

class SLoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username'], 'trim'],
            [['username', 'password'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        $ac = AccountMch::findOne([
            'username' => $this->username,
            'is_delete' => 0,
        ]);
        if (!$ac) {
            return [
                'code' => 1,
                'msg' => '用户名或密码错误',
            ];
        }
        if (!\Yii::$app->security->validatePassword($this->password, $ac->password)) {
            return [
                'code' => 1,
                'msg' => '用户名或密码错误',
            ];
        }
        \Yii::$app->shop->login($ac);

        return [
            'code' => 0,
            'msg' => '登录成功',
        ];
    }
}
