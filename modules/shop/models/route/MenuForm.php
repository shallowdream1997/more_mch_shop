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
 * @ClassName MenuForm
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-03 14:53 星期三
 * @Version 1.0
 * @Description 菜单
 */

namespace app\modules\shop\models\route;


use app\modules\shop\models\Model;

class MenuForm extends Model
{
    public function getList()
    {
        $model = new RouteForm();
        $permissions = $model->getRoutePermission();

        $menuList = [];
        $list = $model->getPermissionMenu();

        $perlist = $this->getMenuList($list, $permissions);
        $menuList = array_merge($menuList, $perlist);
        $menuList = $this->deleteEmptyList($menuList);

        return $menuList;
    }

    public function getMenuList($permissions, $arr)
    {
        $mch = in_array(\Yii::$app->mch->identity->id,json_decode(\Yii::$app->shop->identity->mch_json,true));

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
