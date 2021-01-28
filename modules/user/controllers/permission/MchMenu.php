<?php

namespace app\modules\user\controllers\permission;

class MchMenu
{
    public static function getMenu()
    {
        return [
            [
                'name' => '个人中心',
                'is_menu' => false,
                'icon' => 'icon-people',
                'route' => 'user/default/setting',
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
                'list' => [
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
}
