<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/4/27
 * Time: 14:23
 */


namespace app\modules\api\models\mch;

use app\models\Banner;
use app\models\Goods;
use app\models\HomeBlock;
use app\models\HomeNav;
use app\models\MchCat;
use app\models\Option;
use app\modules\api\models\ApiModel;
use app\modules\api\models\diy\DiyTemplateForm;
use app\modules\api\models\StoreConfigForm;

class ShopCatForm extends ApiModel
{
    public $mch_id;

    public function rules()
    {
        return [
            ['mch_id', 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $list = MchCat::find()->where([
            'mch_id' => $this->mch_id,
            'parent_id' => 0,
            'is_delete' => 0,
        ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
//        foreach ($list as &$item) {
//            $sub_list = MchCat::find()->where([
//                'mch_id' => $this->mch_id,
//                'parent_id' => $item['id'],
//                'is_delete' => 0,
//            ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
//            $item['sub_list'] = $sub_list; //二级分类
//        }
//        $item['sub_list'] = $list;
        $data = [
            'list'=>$list
        ];
        return [
            'code' => 0,
            'data' => $data
        ];
    }

    public function getIndexPage()
    {
        $banner_list = Banner::find()->where([
            'is_delete' => 0,
            'store_id' => $this->store->id,
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

        $nav_icon_list = HomeNav::find()->where([
            'is_delete' => 0,
            'is_hide' => 0,
            'store_id' => $this->store->id,
        ])->orderBy('sort ASC,addtime DESC')->select('name,pic_url,url,name,open_type')->asArray()->all();

        $arr = ['/pages/web/authorization/authorization'];
        foreach ($nav_icon_list as $k => &$value) {
            if ($value['open_type'] == 'wxapp') {
                $res = $this->getUrl($value['url']);
                $value['appId'] = $res[2];
                $value['path'] = urldecode($res[4]);
            }
        }
        $nav_icon_list = array_values($nav_icon_list);
        unset($value);

        $block_list = HomeBlock::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->all();
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

        $update_list['banner']['banner_list'] = $banner_list;
        $special_list = $this->getSpecialList();
        $recommend_list = $this->getRecommendList();
        $data = [
            'banner_list' => $banner_list,
            'nav_icon_list' => $nav_icon_list,
            'block_list' => $new_block_list,
            'special_list' => $special_list,
            'recommend_list' => $recommend_list,
        ];

        return $data;
    }

    //是否特价商品
    public function getSpecialList()
    {
        $query = Goods::find()->where([
            'store_id' => $this->store->id,
            'mch_id' => $this->mch_id,
            'is_special' => 1,
            'status' => 1,
            'is_delete' => 0,
            'type' => get_plugin_type()
        ])->select('id,name,price,cover_pic,service');

        $data = $query->limit(6)->orderBy('sort ASC')->all();

        foreach ($data as $goods){
            $service_list = explode(',', $goods->service);
            // 默认商品服务
            if (!$goods->service) {
                $option = Option::get('good_services', $this->store->id, 'admin', []);
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

    //是否推荐商品
    public function getRecommendList()
    {
        $query = Goods::find()->where([
            'store_id' => $this->store->id,
            'mch_id' => $this->mch_id,
            'is_recommend' => 1,
            'status' => 1,
            'is_delete' => 0,
            'type' => get_plugin_type()
        ])->select('id,name,price,cover_pic,service');

        $data = $query->limit(4)->orderBy('sort ASC')->all();

        foreach ($data as $goods){

            $service_list = explode(',', $goods->service);
            // 默认商品服务
            if (!$goods->service) {
                $option = Option::get('good_services', $this->store->id, 'admin', []);
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

    private function getUrl($url)
    {
        preg_match('/^[^\?+]\?([\w|\W]+)=([\w|\W]*?)&([\w|\W]+)=([\w|\W]*?)$/', $url, $res);
        return $res;
    }

}
