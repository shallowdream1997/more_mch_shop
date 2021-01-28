<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\modules\user\behaviors;



use app\models\Mch;
use app\models\MchAuthLogin;
use app\modules\user\controllers\Controller;
use app\modules\user\controllers\permission\MchMenu;
use app\modules\user\models\permission\permission\IndexPermissionForm;
use yii\base\ActionFilter;
use Yii;
class PermissionRoleBehavior extends ActionFilter
{
    /**
     * 安全路由，权限验证时会排除这些路由
     * @var array
     */
    private $safeRoute = [
        'user/default/setting',
        'user/mch/index/index',
    ];

    public function beforeAction($action)
    {
        //路由名称
        $route = \Yii::$app->requestedRoute;
        if (\Yii::$app->request->isAjax) {
            return true;
        }

        $is_role = $this->getMchIsRole();
        if ($is_role){
            $menuRoute = [
                'user/default/setting',
                'user/default/setting',
                'user/user/index/index',
                'user/user/index/index',
                'user/clerk/index/index',
                'user/clerk/index/index',
                'user/mch/index/index',
                'user/mch/index/index',
                'user/mch/index/setting',
                'user/mch/index/postage-rules',
                'user/mch/index/free-deliver-rules',
                'user/mch/goods/index',
                'user/mch/goods/index',
                'user/mch/goods/goods-detail',
                'user/mch/goods/cat',
                'user/mch/goods/cat-edit',
                'user/mch/goods/parameters',
                'user/mch/goods/parameters-new-edit',
                'user/mch/goods/edit',
                'user/mch/goods/taobao-copy',
                'user/mch/printer/list',
                'user/mch/printer/list',
                'user/mch/printer/setting',
                'user/mch/printer/edit',
                'user/mch/order/index',
                'user/mch/order/index',
                'user/mch/order/detail',
                'user/mch/order/refund',
                'user/mch/order/share',
                'user/mch/order/storeorder',
                'user/mch/order/storedetail',
                'user/mch/coupon/index',
                'user/mch/coupon/index',
                'user/mch/coupon/edit',
                'user/mch/coupon/delete-cat',
                'user/mch/coupon/delete',
                'user/mch/coupon/send',
                'user/mch/coupon/auto-send',
                'user/mch/coupon/auto-send-edit',
                'user/mch/coupon/auto-send-delete',
                'user/mch/account/cash',
                'user/mch/account/cash',
                'user/mch/account/log',
            ];
            $this->safeRoute = array_merge($menuRoute,$this->safeRoute);
        }
        //排除安全路由
        if (in_array($route, $this->safeRoute)) {
            return true;
        }

        $menu = MchMenu::getMenu();

        //判断操作员权限
        $model = new IndexPermissionForm();
        $userPermissions = $model->getPermissionByUser();

        $permissions = $this->getUserPermissions($menu, $userPermissions);

        if (!in_array($route, $permissions)) {
            $this->permissionError();
        }

        return true;
    }

    /**
     * 获取角色所拥有的权限
     * @param $menu
     * @param $pList
     * @return array
     */
    public function getUserPermissions($menu, $pList)
    {
        $arr = [];
        foreach ($menu as $k => $item) {
            //TODO in_array() item['route']为空字符串，也是true
            if (in_array($item['route'], $pList) || isset($item['list'])) {
                if (isset($item['list']) && is_array($item['list'])) {
                    $arr = array_merge($arr, $this->getUserPermissions($item['list'], $pList));
                } else {
                    $arr[] = $item['route'];
                }

                if (isset($item['sub']) && is_array($item['sub'])) {
                    foreach ($item['sub'] as $i) {
                        $arr[] = $i['route'];
                    }
                }
            }
        }

        return $arr;
    }
    public function getMchIsRole()
    {
        $mch = Mch::find()->alias('m')
            ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
            ->where(['mau.user_id'=>Yii::$app->user->id,'mau.is_default'=>1])
            ->exists();

        return $mch;
    }

    public function permissionError()
    {
        $url = Yii::$app->urlManager->createUrl('user/error/permission-error');
        Yii::$app->response->redirect($url)->send();
    }
}
