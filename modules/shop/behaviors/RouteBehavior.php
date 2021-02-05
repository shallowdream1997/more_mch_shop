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
 * @ClassName RouteBehavior
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-03 14:26 星期三
 * @Version 1.0
 * @Description 安全路由和菜单
 */

namespace app\modules\shop\behaviors;


use app\models\Mch;
use app\models\MchAuthLogin;
use app\modules\shop\controllers\route\Menu;
use app\modules\shop\models\route\RouteForm;
use yii\base\ActionFilter;

class RouteBehavior extends ActionFilter
{
    /**
     * 安全路由，权限验证时会排除这些路由
     * @var array
     */
    private $safeRoute = [
        'shop/account/index',
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
                'shop/account/setting',
            ];
            $this->safeRoute = array_merge($menuRoute,$this->safeRoute);
        }
        //排除安全路由
        if (in_array($route, $this->safeRoute)) {
            return true;
        }

        $menu = Menu::getMenu();

        //判断操作员权限
        $model = new RouteForm();
        $userPermissions = $model->getRoutePermission();

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
        $exist = in_array(\Yii::$app->mch->identity->id,json_decode(\Yii::$app->shop->identity->mch_json,true));
        return $exist;
    }

    public function permissionError()
    {
        $url = \Yii::$app->urlManager->createUrl('shop/error/permission-error');
        \Yii::$app->response->redirect($url)->send();
    }
}
