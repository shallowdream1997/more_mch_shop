<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\modules\mch\models\permission\permission;

use app\models\AuthRolePermission;
use app\models\User;
use app\modules\mch\models\MchMenu;
use app\modules\mch\models\MchModel;
use Yii;

class IndexPermissionForm extends MchModel
{

    public function getList()
    {
        $menuList = \Yii::$app->controller->getMenuList();
        $newMenuList = $this->deleteAdminMenu($menuList);
        return $newMenuList;
    }

    /**
     * @return mixed
     * 商户端员工
     */
    public function getRoleMchList()
    {
        $mchmenuList = self::getMenu();
        $newMenuList = $this->deleteAdminMenu($mchmenuList);
        return $newMenuList;
    }

    public static function getMenu()
    {
        return [
            [
                'name' => '个人中心',
                'is_menu' => false,
                'icon' => 'icon-people',
                'route' => 'user/default/setting',
                'children' => [
                    [
                        'name' => '个人信息',
                        'is_menu' => false,
                        'route' => 'user/default/setting',
                    ],
                ],
            ],
            [
                'name' => '用户管理',
                'is_menu' => false,
                'icon' => 'icon-manage',
                'route' => 'user/user/index/index',
                'children' => [
                    [
                        'name' => '用户管理',
                        'is_menu' => false,
                        'route' => 'user/user/index/index',
                    ],
                ],
            ],
            [
                'name' => '店员管理',
                'is_menu' => false,
                'icon' => 'icon-liebiao',
                'route' => 'user/clerk/index/index',
                'children' => [
                    [
                        'name' => '店员管理',
                        'is_menu' => false,
                        'route' => 'user/clerk/index/index',
                    ],
                ],
            ],
            [
                'name' => '商户管理',
                'is_menu' => false,
                'icon' => 'icon-shanghu',
                'route' => 'user/mch/index/index',
                'children' => [
                    [
                        'name' => '商户中心',
                        'is_menu' => false,
                        'route' => 'user/mch/index/index',
                    ],
                    [
                        'name' => '店铺设置',
                        'is_menu' => false,
                        'route' => 'user/mch/index/setting',
                    ],
                    [
                        'name' => '运费规则',
                        'is_menu' => false,
                        'route' => 'user/mch/index/postage-rules',
                    ],
                    [
                        'name' => '包邮管理',
                        'is_menu' => false,
                        'route' => 'user/mch/index/free-deliver-rules',
                    ],
                ],
            ],
            [
                'name' => '商品管理',
                'is_menu' => false,
                'icon' => 'icon-service',
                'route' => 'user/mch/goods/index',
                'children' => [
                    [
                        'name' => '商品管理',
                        'is_menu' => false,
                        'route' => 'user/mch/goods/index',
                        'sub' => [
                            [
                                'name' => '商品详情',
                                'is_menu' => false,
                                'route' => 'user/mch/goods/goods-detail'
                            ]
                        ]
                    ],
                    [
                        'name' => '商品分类',
                        'is_menu' => false,
                        'route' => 'user/mch/goods/cat',
                    ],
                    [
                        'name' => '产品模型',
                        'is_menu' => false,
                        'route' => 'user/mch/goods/parameters',
                        'sub' => [
                            [
                                'name' => '产品模型编辑',
                                'is_menu' => false,
                                'route' => 'user/mch/goods/parameters-new-edit'
                            ]
                        ]
                    ],
                    [
                        'name' => '添加商品',
                        'is_menu' => false,
                        'route' => 'user/mch/goods/edit',
                    ],
                    [
                        'name' => '淘宝CSV上传',
                        'is_menu' => false,
                        'route' => 'user/mch/goods/taobao-copy',
                    ],
                ],
            ],
            [
                'name' => '打印机管理',
                'is_menu' => false,
                'icon' => 'icon-setup',
                'route' => 'user/mch/printer/list',
                'children' => [
                    [
                        'name' => '小票打印',
                        'is_menu' => false,
                        'route' => 'user/mch/printer/list',
                        'sub' => [
                            [
                                'name' => '小票打印设置',
                                'is_menu' => false,
                                'route' => 'user/mch/printer/setting',
                            ],
                            [
                                'name' => '小票打印编辑',
                                'is_menu' => false,
                                'route' => 'user/mch/printer/edit',
                            ]
                        ],
                    ],
                ],
            ],
            [
                'name' => '订单管理',
                'is_menu' => false,
                'icon' => 'icon-activity',
                'route' => 'user/mch/order/index',
                'children' => [
                    [
                        'name' => '订单管理',
                        'is_menu' => false,
                        'route' => 'user/mch/order/index',
                        'sub'=>[
                            [
                                'name' => '订单详情',
                                'is_menu' => false,
                                'route' => 'user/mch/order/detail',
                            ]
                        ]
                    ],
                    [
                        'name' => '售后订单',
                        'is_menu' => false,
                        'route' => 'user/mch/order/refund',
                    ],
                    [
                        'name' => '分销订单',
                        'is_menu' => false,
                        'route' => 'user/mch/order/share',
                    ],
                    [
                        'name' => '平台订单',
                        'is_menu' => false,
                        'route' => 'user/mch/order/storeorder',
                        'sub' => [
                            [
                                'name' => '平台订单详情',
                                'is_menu' => false,
                                'route' => 'user/mch/order/storedetail',
                            ]
                        ]
                    ],
                ],
            ],
            [
                'name' => '营销管理',
                'is_menu' => false,
                'icon' => 'icon-coupons',
                'route' => 'user/mch/coupon/index',
                'children' => [
                    [
                        'name' => '优惠券管理',
                        'is_menu' => false,
                        'route' => 'user/mch/coupon/index',
                        'sub' => [
                            [
                                'name' => '优惠券编辑',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/edit',
                            ],
                            [
                                'name' => '优惠券删除分类',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/delete-cat',
                            ],
                            [
                                'name' => '优惠券删除',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/delete',
                            ],
                            [
                                'name' => '优惠券发放',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/send',
                            ],
                        ]
                    ],
                    [
                        'name' => '自动发放设置',
                        'is_menu' => false,
                        'route' => 'user/mch/coupon/auto-send',
                        'sub'=>[
                            [
                                'name' => '优惠券自动发放编辑',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/auto-send-edit',
                            ],
                            [
                                'name' => '优惠券自动发放删除',
                                'is_menu' => false,
                                'route' => 'user/mch/coupon/auto-send-delete',
                            ],
                        ]
                    ],
                ],
            ],
            [
                'name' => '账户资金',
                'is_menu' => false,
                'icon' => 'icon-qianbao',
                'route' => 'user/mch/account/cash',
                'children' => [
                    [
                        'name' => '提现',
                        'route' => 'user/mch/account/cash',
                    ],
                    [
                        'name' => '收支明细',
                        'route' => 'user/mch/account/log',
                    ],
                ],
            ],
        ];
    }

    /**
     * 获取当前登录用户所拥有的权限Route
     * @return array
     */
    public function getPermissionByUser()
    {
        $roles = [];
        //只有角色登录才去查权限列表
        if (!Yii::$app->mchRoleAdmin->isGuest) {
            $user = User::find()->where(['id' => $this->getCurrentUserId()])->with('roleUser')->one();

            foreach ($user->roleUser as $item) {
                $roles[] = $item->role->id;
            }
        }
        $permissions = AuthRolePermission::find()->where(['in', 'role_id', $roles])->all();

        $data = [];
        foreach ($permissions as $permission) {
            $data[] = $permission->permission_name;
        }
        return $data;
    }

    /**
     * 获取编辑时的权限列表
     */
    public function getPermissionMenuByUser($roleId)
    {
        $list = $this->getList();
        $permissions = AuthRolePermission::find()->where(['role_id' => $roleId])->all();

        $data = [];
        foreach ($permissions as $permission) {
            $data[] = $permission->permission_name;
        }
        $model = new MchMenu();
        $newList = $model->deleteEmptyList($list);

        $resetList = $this->resetPermissionMenu($newList, $data);

        $permissionsMenu = Yii::$app->serializer->encode($resetList);

        return $permissionsMenu;
    }

    /**
     * 给用户已有的权限加上show字段标识
     * @param $list
     * @param $permissions
     * @return mixed
     */
    public function resetPermissionMenu($list, $permissions)
    {
        foreach ($list as $key => $item) {
            if (in_array($item['route'], $permissions)) {
                $list[$key]['show'] = true;
            }
            if (isset($item['children'])) {
                //一级和二级菜单编辑时要设置为空，不然更新是会有bug
                $list[$key]['route'] = '';
                $list[$key]['children'] = $this->resetPermissionMenu($item['children'], $permissions);
            }
        }

        return $list;
    }

    /**
     * 去除总管理员独有的菜单，这些菜单子账号和操作员都不能使用
     * @param $list
     * @return mixed
     */
    public function deleteAdminMenu($list)
    {
        foreach ($list as $k => $item) {

            if ($item['admin'] === true) {
                unset($list[$k]);
                continue;
            }
            $removePermissions = ['permission'];
            if (isset($item['key']) && in_array($item['key'], $removePermissions)) {
                unset($list[$k]);
                continue;
            }

            if (isset($item['children']) && count($item['children']) > 0) {
                $list[$k]['children'] = $this->deleteAdminMenu($item['children']);
            }
        }
        $list = array_values($list);
        return $list;
    }
}
