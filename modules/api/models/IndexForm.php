<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/5
 * Time: 16:00
 */

namespace app\modules\api\models;


use app\hejiang\BaseApiResponse;
use app\models\MchCat;
use app\modules\api\models\diy\DiyTemplateForm;
use app\utils\GetInfo;
use app\hejiang\ApiResponse;
use app\models\Banner;
use app\models\Cat;
use app\models\Coupon;
use app\models\FxhbHongbao;
use app\models\FxhbSetting;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\HomeBlock;
use app\models\HomeNav;
use app\models\HomePageModule;
use app\models\Mch;
use app\models\Miaosha;
use app\models\MiaoshaGoods;
use app\models\MsGoods;
use app\models\Option;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Store;
use app\models\Topic;
use app\models\User;
use app\models\UserCoupon;
use app\models\YyGoods;
use yii\helpers\VarDumper;

class IndexForm extends ApiModel
{
    public $store_id;
    public $_platform;
    public $page_id;
    public $mch_id;
    public $longitude; //经度
    public $latitude; //纬度
    public function search()
    {
        $store = $this->store;
        if (!$store)
            return new ApiResponse(1, 'Store不存在');

        $mch_list = $this->getMchList(); //获取首页当前门店信息
        //获取首页一级分类，分类从平台分类获取
        $cat_list = Cat::find()->where([
            'store_id' => $this->store_id,
            'parent_id' => 0,
            'is_delete' => 0,
            'is_search'=>0,
        ])->orderBy('sort,addtime DESC')->select('id,name,pic_url icon')->asArray()->all();
        //读取二级分类
        foreach ($cat_list as &$item) {
            $sub_list = Cat::find()->where([
                'store_id' => $this->store_id,
                'parent_id' => $item['id'],
                'is_delete' => 0,
                'is_search'=>0,
            ])->orderBy('sort,addtime DESC')->select('id,name,pic_url icon')->asArray()->all();
            $item['list'] = $sub_list;
        }

        $IndexList = $this->getIndexPage($mch_list);

        $alllist = [
            'id' => -1,
            'parent_id' => 0,
            'name' => '首页',
            'sort' => -1,
            'list' => $IndexList ? $IndexList : []
        ];
        array_unshift($cat_list,$alllist); //拼接，首页和分类放一起
        $data = [
            'list'=>$cat_list
        ];

        return new ApiResponse(0, 'success', $data);
    }

    public function getCatList()
    {
        $list = MchCat::find()->where([
            'mch_id' => $this->mch_id,
            'parent_id' => 0,
            'is_delete' => 0,
        ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
        foreach ($list as &$item) {
            $sub_list = MchCat::find()->where([
                'mch_id' => $this->mch_id,
                'parent_id' => $item['id'],
                'is_delete' => 0,
            ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
            $item['list'] = $sub_list;
        }

        $data = [
            'list'=>$list
        ];
        return [
            'code' => 0,
            'data' => $data
        ];
    }

    /**
     * @param $mch
     * @return array
     * 获取首页的详情信息
     */
    public function getIndexPage($mch)
    {
        //轮播图
        $banner_list = Banner::find()->where([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'type' => 1,
        ])->orderBy('sort ASC')->asArray()->all();
        foreach ($banner_list as $i => $banner) {
            if (!$banner['open_type']) {
                $banner_list[$i]['open_type'] = 'navigate';
            }
            if ($banner['open_type'] == 'wxapp') {
                $res = $this->getUrl($banner['page_url']);
                $banner_list[$i]['appId'] = $res[2];
                $banner_list[$i]['path'] = urldecode($res[4]);
            }
        }
        //首页导航图标icon
        $nav_icon_list = HomeNav::find()->where([
            'is_delete' => 0,
            'is_hide' => 0,
            'store_id' => $this->store_id,
        ])->orderBy('sort ASC,addtime DESC')->select('name,pic_url,url,name,open_type')->asArray()->all();
        $arr = ['/pages/web/authorization/authorization'];
        foreach ($nav_icon_list as $k => &$value) {
            if ($value['open_type'] == 'wxapp') {
                $res = $this->getUrl($value['url']);
                $value['appId'] = $res[2];
                $value['path'] = urldecode($res[4]);
            }
            // 去除支付宝不需要的菜单
            if ($this->_platform === 'my' && in_array($value['url'], $arr)) {
                unset($nav_icon_list[$k]);
            }
        }
        $nav_icon_list = array_values($nav_icon_list);
        unset($value);
        $block_list = HomeBlock::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->all();
        $new_block_list = [];
        foreach ($block_list as $item) {
            $data = json_decode($item->data, true);
            foreach ($data['pic_list'] as &$value) {
                if ($value['open_type'] == 'wxapp') {
                    $res = $this->getUrl($value['url']);
                    $value['appId'] = $res[2];
                    $value['path'] = urldecode($res[4]);
                }
            }
            unset($value);
            $new_block_list[] = [
                'id' => $item->id,
                'name' => $item->name,
                'data' => $data,
                'style' => $item->style
            ];
        }
//        $notice = Option::get('notice', $this->store_id, 'admin');
        $update_list['banner']['banner_list'] = $banner_list;
        $special_list = $this->getSpecialList();//获取是否特价商品
        $recommend_list = $this->getRecommendList();//获取是否推荐商品
//        $icon = [
//            'positioning_icon' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/wxapp/images/positioning.png',
//            'member_icon' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/wxapp/images/member.png',
//            'chat_icon' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/wxapp/images/chat_icon.png',
//        ];
        $IndexData = [
            'store' => StoreConfigForm::getData($this->store),
            'banner_list' => $banner_list,
            'nav_icon_list' => $nav_icon_list,
            'cat_goods_cols' => $this->store->cat_goods_cols,
            'block_list' => $new_block_list,
            'nav_count' => $this->store->nav_count,
            'mch_list' => $mch,
            'template' => false,
            'special_list' => $special_list,
            'recommend_list' => $recommend_list,
        ];

        return $IndexData;
    }

    private function getBlockList()
    {

    }

    //获取商户分类
//    public function getCatList()
//    {
//        $cat_list = Cat::find()->where(['is_delete' => 0, 'parent_id' => 0])->orderBy('sort,addtime DESC')->asArray()->all();
//        foreach ($cat_list as $i => $item){
//            $sub_list = Cat::find()->where([
//                'is_delete' => 0,
//                'parent_id' => $item['id'],
//            ])->orderBy('sort ASC')->select('')->asArray()->all();
//            $cat_list[$i]['sub_list'] = $sub_list;
//        }
//        return $cat_list;
//    }

    //是否特价商品
    public function getSpecialList()
    {
        $query = Goods::find()->where([
            'store_id' => $this->store_id,
            'mch_id' => 0,
            'is_special' => 1,
            'status' => 1,
            'is_delete' => 0,
            'type' => get_plugin_type()
        ])->select('id,name,price,cover_pic,service,labels');

        $data = $query->limit(6)->orderBy('sort ASC')->all();

        foreach ($data as $goods){
            if ($goods->labels){
                $goods['labels'] = explode(',',$goods->labels);
            }else{
                $goods['labels'] = [];
            }
        }

        return $data;
    }

    //是否推荐商品
    public function getRecommendList()
    {
        $query = Goods::find()->where([
            'store_id' => $this->store_id,
            'mch_id' => 0,
            'is_recommend' => 1,
            'status' => 1,
            'is_delete' => 0,
            'type' => get_plugin_type()
        ])->select('id,name,price,cover_pic,service,labels');

        $data = $query->limit(20)->orderBy('sort ASC')->all();

        foreach ($data as $goods){
            if ($goods->labels){
                $goods['labels'] = explode(',',$goods->labels);
            }else{
                $goods['labels'] = [];
            }

            $service_list = explode(',', $goods->service);
            // 默认商品服务
            if (!$goods->service) {
                $option = Option::get('good_services', $this->store_id, 'admin', []);
                foreach ($option as $item) {
                    if ($item['is_default'] == 1) {
                        $service_list = explode(',', $item['service']);
                        break;
                    }
                }
            }
            $new_service_list = [];
            if (is_array($service_list)) {
                foreach ($service_list as $item1) {
                    $item1 = trim($item1);
                    if ($item1) {
                        $new_service_list[] = $item1;
                    }
                }
            }
            $goods['service'] = $new_service_list;
        }

        return $data;
    }

    /**
     * @param Store $store
     */
    private function getModuleList($store)
    {
        $list = json_decode($store->home_page_module, true);
        if (!$list) {
            $list = [
                [
                    'name' => 'notice',
                ],
                [
                    'name' => 'banner',
                ],
                [
                    'name' => 'search',
                ],
                [
                    'name' => 'nav',
                ],
                [
                    'name' => 'topic',
                ],
                [
                    'name' => 'coupon',
                ],
                [
                    'name' => 'cat',
                ],
            ];
        } else {
            $new_list = [];
            foreach ($list as $item) {
                if (stripos($item['name'], 'block-') !== false) {
                    $names = explode('-', $item['name']);
                    $new_list[] = [
                        'name' => $names[0],
                        'block_id' => $names[1],
                    ];
                } elseif (stripos($item['name'], 'single_cat-') !== false) {
                    $names = explode('-', $item['name']);
                    $new_list[] = [
                        'name' => $names[0],
                        'cat_id' => $names[1],
                    ];
                } elseif (stripos($item['name'], 'video-') !== false) {
                    $names = explode('-', $item['name']);
                    $new_list[] = [
                        'name' => $names[0],
                        'video_id' => $names[1],
                    ];
                } else {
                    $new_list[] = $item;
                }
            }
            $list = $new_list;
        }
        return $list;
    }

    public function getMiaoshaData()
    {
        $ms_next = false;
        $miaosha = Miaosha::findOne([
            'store_id' => $this->store_id,
        ]);
        if (!$miaosha) {
            return [
                'code' => 1,
                'msg' => '暂无秒杀安排',
            ];
        }
        $miaosha->open_time = json_decode($miaosha->open_time, true);

        $list = MiaoshaGoods::find()->alias('mg')
            ->select('mg.id,g.name,g.cover_pic AS pic,g.original_price AS price,mg.attr,mg.start_time')
            ->leftJoin(['g' => MsGoods::tableName()], 'mg.goods_id=g.id')
            ->where([
                'AND',
                [
                    'mg.is_delete' => 0,
                    'g.is_delete' => 0,
                    'mg.open_date' => date('Y-m-d'),
                    'g.status' => 1,
                    'mg.start_time' => date('H'),
                    'mg.store_id' => $this->store_id,
                ],
                ['in','mg.start_time', $miaosha->open_time],
            ])
            ->orderBy('g.sort ASC,g.addtime DESC')
            ->limit(10)
            ->asArray()->all();

        if (empty($list)) {
            $ms_next = true;
            $next = MiaoshaGoods::find()->alias('mg')
                ->where([
                    'AND',
                    [
                        'mg.is_delete' => 0,
                        'g.is_delete'  => 0,
                        'g.status' => 1,
                        'mg.store_id' => $this->store_id,
                    ],
                    ['=','mg.open_date',date('Y-m-d')],
                    ['>','mg.start_time', date('H')],
                ])->orWhere([
                    'AND',
                    [
                        'mg.is_delete' => 0,
                        'g.is_delete'  => 0,
                        'g.status' => 1,
                        'mg.store_id' => $this->store_id,
                    ],
                    ['>','mg.open_date',date('Y-m-d')],
                ])

                ->leftJoin(['g' => MsGoods::tableName()], 'mg.goods_id=g.id')
                ->orderBy('mg.open_date asc,mg.start_time asc')
                ->select('mg.start_time,mg.open_date,mg.id')->one();

            $list = MiaoshaGoods::find()->alias('mg')
                ->select('mg.id,g.name,g.cover_pic AS pic,g.original_price AS price,mg.attr,mg.start_time,mg.open_date')
                ->leftJoin(['g' => MsGoods::tableName()], 'mg.goods_id=g.id')
                ->where([
                    'AND',
                    [
                        'mg.is_delete' => 0,
                        'g.is_delete' => 0,
                        'mg.open_date' => $next->open_date,
                        'g.status' => 1,
                        'mg.start_time' => $next->start_time,
                        'mg.store_id' => $this->store_id,
                    ],
                    ['in','mg.start_time', $miaosha->open_time],
                ])
                ->orderBy('mg.open_date asc,mg.start_time asc')
                ->limit(10)
                ->asArray()
                ->all();

        }

        //////
        $startTime = intval(date('H'));
        $openDate = time();
        foreach ($list as $i => $item) {
            $item['attr'] = json_decode($item['attr'], true);
            $list[$i] = $item;
            $price_list = [];
            foreach ($item['attr'] as $attr) {
                if ($attr['miaosha_price'] <= 0) {
                    $price_list[] = doubleval($item['price']);
                } else {
                    $price_list[] = doubleval($attr['miaosha_price']);
                }
            }
            $list[$i]['price'] = number_format($list[$i]['price'], 2, '.', '');
            $list[$i]['miaosha_price'] = number_format(min($price_list), 2, '.', '');
            unset($list[$i]['attr']);
            $startTime = $item['start_time'];
            $openDate = strtotime($item['open_date']);
        };

        $openDate = $openDate>strtotime(date('Y-m-d',time()))?date('m.d',$openDate):'';
        if (count($list) == 0){
            return [
                'name' => '暂无秒杀活动',
                'rest_time' => 0,
                'goods_list' => null,
            ];
        }else{
            $name = $ms_next?'预告':'';
            return [
    //            'name' => intval(date('H')) . '点场',
                'name' => $startTime . '点场'.$name,
                'ms_next' => $ms_next,
                'date' => $openDate,
                'rest_time' => max(intval(strtotime(date('Y-m-d ' . $startTime . ':59:59')) - time()), 0),
                'goods_list' => $list,
            ];
        }
    }

    public function getPintuanData()
    {
        $num_query = PtOrderDetail::find()->alias('pod')
            ->select('pod.goods_id,SUM(pod.num) AS sale_num')
            ->leftJoin(['po' => PtOrder::tableName()], 'pod.order_id=po.id')
            ->where([
                'AND',
                [
                    'pod.is_delete' => 0,
                    'po.is_delete' => 0,
                    'po.is_pay' => 1,
                ],
            ])->groupBy('pod.goods_id');
        $list = PtGoods::find()->alias('pg')
            ->select('pg.*,pod.sale_num')
            ->leftJoin(['pod' => $num_query], 'pg.id=pod.goods_id')
            ->where([
                'AND',
                [
                    'pg.is_delete' => 0,
                    'pg.status' => 1,
                    'pg.store_id' => $this->store_id,
                ],
            ])->orderBy('pg.is_hot DESC,pg.sort ASC,pg.addtime DESC')
            ->limit(10)
            ->asArray()->all();
        $new_list = [];
        foreach ($list as $item) {
            $new_list[] = [
                'id' => $item['id'],
                'pic' => $item['cover_pic'],
                'name' => $item['name'],
                'price' => number_format($item['price'], 2, '.', ''),
                'sale_num' => intval($item['sale_num'] ? $item['sale_num'] : 0) + intval($item['virtual_sales'] ? $item['virtual_sales'] : 0),
            ];
        }
        return [
            'goods_list' => $new_list,
        ];
    }

    /**
     * 获取首页活动弹窗列表
     */
    public function getActModalList()
    {
        $act_list = [];
        $fxhb_act = $this->getFxhbAct();
        if ($fxhb_act) {
            $act_list[] = $fxhb_act;
        }
        foreach ($act_list as $i => $item) {
            if ($i == 0)
                $act_list[$i]['show'] = true;
            else
                $act_list[$i]['show'] = false;
            $act_list[$i]['list'][] = $item;
        }
        return $act_list;
    }

    private function getFxhbAct()
    {
        $act_data = [
            'name' => '一起拆红包',
            'pic_url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/images/fxhb/act_modal.png',
            'pic_width' => 750,
            'pic_height' => 696,
            'url' => '/pages/fxhb/open/open',
            'open_type' => 'navigate',
            'page_id' => 'fxhb'
        ];
        $fxhb_setting = FxhbSetting::findOne([
            'store_id' => $this->store_id,
        ]);
        if (!$fxhb_setting || $fxhb_setting->game_open != 1) {
            return null;
        }
        if ($user = \Yii::$app->user->isGuest) {
            return $act_data;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /** @var FxhbHongbao $hongbao */
        $hongbao = FxhbHongbao::find()->where([
            'user_id' => $user->id,
            'store_id' => $this->store_id,
            'parent_id' => 0,
            'is_finish' => 0,
            'is_expire' => 0,
        ])->one();
        if (!$hongbao)
            return $act_data;
        if (time() > $hongbao->expire_time) {
            $hongbao->is_expire = 1;
            $hongbao->save();
            return $act_data;
        }
        return null;
    }

    public function getYuyueData()
    {
        $list = YyGoods::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])
            ->select(['id', 'name', 'cover_pic', 'price'])
            ->limit(10)->orderBy(['sort' => SORT_ASC])->asArray()->all();
        return $list;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     * 获取首页当前门店信息
     */
    public function getMchList()
    {
        $belong_mch = \Yii::$app->user->identity->belong_mch; //获取当前用户的所属门店id
        if (!$this->mch_id){ //默认进入小程序无门店id
            if ($belong_mch == 0){ //如果用户无所属门店，则为新用户

                //定位经纬度
                if (!$this->longitude){
                    $longitude = 113.3172;
                }else{
                    $longitude = $this->longitude;
                }
                if (!$this->latitude){
                    $latitude = 23.08331;
                }else{
                    $latitude = $this->latitude;
                }
                //计算距离最近的门店距离
                $juli = Mch::find()
                    ->where(['store_id'=>$this->store_id,'is_delete'=>0,'is_open'=>1,'review_status'=>1])
                    ->select("`id`,`longitude`,`latitude`,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$latitude."*PI()/180-latitude*PI()/180)/2),2)+COS(".$latitude."*PI()/180)*COS(latitude*PI()/180)*POW(SIN((".$longitude."*PI()/180-longitude*PI()/180)/2),2)))*1000) `juli`")
                    ->orderBy('juli ASC');

                //选出门店中距离用户最近的
                $query = Mch::find()->alias('m')
                    ->leftJoin(['j'=>$juli],'j.id = m.id')
                    ->select('m.id,m.name,m.service_tel')
                    ->where(['m.store_id'=>$this->store_id,'m.is_delete'=>0,'m.is_open'=>1,'m.review_status'=>1])
                    ->orderBy('j.juli ASC')
                    ->limit(1);
                $list = $query->asArray()->one();
            }else{
                //如果用户有所属门店，则为老用户，获取该用户第一次下单所在的门店
                $query = Mch::find()->where([
                    'id' => $belong_mch,
                    'store_id' => $this->store_id,
                    'is_delete' => 0,
                    'is_open' => 1,
                    'is_lock' => 0,
                ]);
                $list = $query->select('id,name,service_tel')->asArray()->one();
            }
        }else{
            //获取用户选择的门店
            $query = Mch::find()->where([
                'id' => $this->mch_id,
                'store_id' => $this->store_id,
                'is_delete' => 0,
                'is_open' => 1,
                'is_lock' => 0,
            ]);
            $list = $query->select('id,name,service_tel')->asArray()->one();
        }

        return $list ? $list : [];
    }

    private function getUrl($url)
    {
        preg_match('/^[^\?+]\?([\w|\W]+)=([\w|\W]*?)&([\w|\W]+)=([\w|\W]*?)$/', $url, $res);
        return $res;
    }

}
