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
 * @ClassName RouteForm
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-03 14:40 星期三
 * @Version 1.0
 * @Description 安全路由和权限分配models
 */

namespace app\modules\shop\models\route;


use app\models\AuthRolePermission;
use app\modules\shop\controllers\route\Menu;
use app\modules\shop\models\Model;
use app\modules\user\controllers\permission\MchMenu;

class RouteForm extends Model
{
    public function getRoutePermission()
    {
        $roles = [];

        $permissions = AuthRolePermission::find()->where(['in', 'role_id', $roles])->all();

        $data = [];
        foreach ($permissions as $permission) {
            $data[] = $permission->permission_name;
        }

        return $data;
    }


    public function getPermissionMenu()
    {
        $permissionMenu = Menu::getMenu();
        $permissions = self::resetPermissionMenu($permissionMenu);

        return $permissions;
    }

    /**
     * 给自定义路由列表 追加ID 及 PID
     * @param array $list 自定义的多维路由数组
     * @param int $id 权限ID
     * @param int $pid 权限PID
     * @return mixed
     */
    public function resetPermissionMenu(array $list, &$id = 1, $pid = 0)
    {
        foreach ($list as $key => $item) {
            $list[$key]['id'] = $id;
            $list[$key]['pid'] = $pid;

            if (isset($item['list'])) {
                $id++;
                $list[$key]['list'] = $this->resetPermissionMenu($item['list'], $id, $id - 1);
            }

            if (isset($item['sub'])) {
                $id++;
                $list[$key]['sub'] = $this->resetPermissionMenu($item['sub'], $id, $id - 1);
            }

            isset($item['list']) == false && isset($item['sub']) == false ? $id++ : $id;
        }

        return $list;
    }

}
