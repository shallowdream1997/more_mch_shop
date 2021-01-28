<?php
namespace app\modules\user\models\permission\permission;

use app\models\Mch;
use app\models\MchAuthLogin;
use app\modules\user\controllers\permission\PermissionsMenu;
use app\modules\user\models\Model;
use Yii;

class MchRoleMenuForm extends Model
{
    public function getList()
    {
        $model = new IndexPermissionForm();
        $permissions = $model->getPermissionByUser();

        $menuList = [];
        $permissionsMenu = new PermissionsMenu();
        $list = $permissionsMenu->getPermissionMenu();

        $perlist = $this->getMenuList($list, $permissions);
        $menuList = array_merge($menuList, $perlist);
        $menuList = $this->deleteEmptyList($menuList);

        return $menuList;
    }

    public function getMenuList($permissions, $arr)
    {
        $mch = Mch::find()->alias('m')
            ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
            ->where(['mau.user_id'=>Yii::$app->user->id,'mau.is_default'=>1])
            ->exists();

        foreach ($permissions as $k => $item) {

            if ($mch) {
                $permissions[$k]['is_show'] = true;
            } else {
                if (in_array($item['route'], $arr) && $item['route']) {
                    $permissions[$k]['is_show'] = true;
                } else {
                    $permissions[$k]['is_show'] = false;
                }
            }

            if (isset($item['list'])) {
                $permissions[$k]['list'] = $this->getMenuList($item['list'], $arr);
                foreach ($permissions[$k]['list'] as $i) {
                    if ($i['is_show'] == true) {
                        $permissions[$k]['route'] = $i['route'];
                        $permissions[$k]['is_show'] = true;
                        break;
                    }
                }
            }

            if (isset($item['sub'])) {
                $permissions[$k]['sub'] = $this->getMenuList($item['sub'], $arr);
            }
        }

        return $permissions;
    }

    public function deleteEmptyList($menuList)
    {
        foreach ($menuList as $i => $item) {
            if (is_array($item['list']) && count($item['list']) == 0 || $item['is_show'] === false) {
                unset($menuList[$i]);
                continue;
            }
            if (is_array($item['list'])) {
                $menuList[$i]['route'] = $item['list'][0]['route'];
            }
        }
        $menuList = array_values($menuList);

        return $menuList;
    }


}
