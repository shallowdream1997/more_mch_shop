<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\modules\user\controllers\permission;

class PermissionsMenu
{

    public function getPermissionMenu()
    {
        $permissionMenu = MchMenu::getMenu();
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
