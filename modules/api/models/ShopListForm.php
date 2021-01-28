<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/24
 * Time: 10:37
 */

namespace app\modules\api\models;

use app\hejiang\ApiResponse;
use app\models\DistrictArr;
use app\models\Mch;
use app\utils\CurlHelper;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ShopListForm extends ApiModel
{
    public $store_id;
    public $user;

    public $longitude;
    public $latitude;
    public $page;
    public $limit;
    public $keyword;

    public $province_id;
    public $city_id;
    public $area_id;

    public function rules()
    {
        return [
            [['longitude', 'latitude','keyword'], 'trim'],
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 0],
            [['limit','province_id','city_id','area_id'], 'integer'],
            [['limit'], 'default', 'value' => 20],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        if ($this->keyword) {
            $query = Mch::find()->where([
                'store_id' => $this->store_id,
                'is_delete' => 0
            ]);
            $query->andWhere(['like','name',$this->keyword]);
            $count = $query->count();
            $p = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page]);

            $list = $query->all();
            $list = ArrayHelper::toArray($list);
            $distance = array();
            foreach ($list as $index => $item) {
                $list[$index]['distance'] = -1;
                if ($item['longitude'] && $this->longitude) {
                    $from = [$this->longitude, $this->latitude];
                    $to = [$item['longitude'], $item['latitude']];
                    $list[$index]['distance'] = $this->get_distance($from, $to, false, 2);
                }
                $distance[] = $list[$index]['distance'];
            }
            array_multisort($distance, SORT_ASC, $list);
            $min = min(count($list), $this->limit);
            $offset = $this->page * $this->limit;
            $list_arr = array();
            $data_count = 1;
            foreach ($list as $index => $item) {
                $list[$index]['score'] = (int)$item['score'];
                $list[$index]['id'] = $item['account_shop_id'] ? $item['account_shop_id'] : $item['id'].'@';
                if ($index < $offset) {
                    continue;
                }
                if ($data_count <= $this->limit) {
                    $list[$index]['distance'] = $this->distance($item['distance']);
                    array_push($list_arr, $list[$index]);
                    $data_count++;
                } else {
                    break;
                }
            }
            $data = [
                'list' => $list_arr,
                'province_id' => $this->province_id,
                'city_id' => $this->city_id,
                'page_count' => $p->pageCount,
                'row_count' => $count
            ];
            return new ApiResponse(0, 'success', $data);
        }

        $query = Mch::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0
        ]);
        if ($this->province_id){
            $query->andWhere(['province_id'=>$this->province_id]);
        }
        if ($this->city_id){
            $query->andWhere(['city_id'=>$this->city_id]);
        }

        $shopdata = [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'province_id' => $this->province_id ? $this->province_id : '',
            'city_id' => $this->city_id ? $this->city_id : '',
            'area_id' => $this->area_id ? $this->area_id : '',
            'limit' => $this->limit,
            'page' => $this->page,
        ];
        $res = CurlHelper::get('storemall/store/storeList',$shopdata);
        $shop_data = json_decode($res,true);
//        dd($shop_data);
        $list = [];
        if ($shop_data['error_code'] == 0){
            foreach ($shop_data['list'] as $i => $k){
                $list[$i]['id'] = $k['id'];
                $list[$i]['name'] = $k['name'] ? $k['name'] : '';
                $list[$i]['realname'] = $k['manager'];
                $list[$i]['tel'] = $k['phone'];
                $list[$i]['address'] = $k['address'];
                $list[$i]['service_tel'] = $k['phone'];
                $list[$i]['logo'] = $k['store_photo'] ? $k['store_photo'] : '';
                $list[$i]['header_bg'] = $k['store_photo'] ? $k['store_photo'] : '';
                $list[$i]['longitude'] = $k['wechat_lng'];
                $list[$i]['latitude'] = $k['wechat_lat'];
                $list[$i]['score'] = 5;
                $list[$i]['distance'] = $k['distance_text'];
            }
            $t = \Yii::$app->db->beginTransaction();
            foreach($shop_data['list'] as $i => $key)
            {
                $res = Mch::updateAll([
                    'store_id' => $this->store_id,
                    'realname' => $key['name'],
                    'province_id' => DistrictArr::getProCityDisId('province',$key['province']),
                    'city_id' => DistrictArr::getProCityDisId('city',$key['city']),
                    'district_id' => DistrictArr::getProCityDisId('district',$key['area']),
                    'name' => $key['name'] ? $key['name'] : '',
                    'tel' => $key['phone'] ? $key['phone'] : '',
                    'service_tel' => $key['store_info']['service_phone'] ? $key['store_info']['service_phone'] : '',
                    'account_money' => $key['store_wallet']['total_money'] ? $key['store_wallet']['total_money'] : 0,
                    'business_type_text' => $key['business_type_text'] ? $key['business_type_text'] : '',
                    'logo' => "https://anhour.gzmidai.com/statics/shop/img/shop-logo.png",
                    'code' => $key['code'] ? $key['code'] : '',
                    'account_shop_id' => $key['id'],
                    'province' => $key['province'] ? $key['province'] : '',
                    'city' => $key['city'] ? $key['city'] : '',
                    'district' => $key['area'] ? $key['area'] : '',
                    'address' => $key['address'],
                    'shop_time' => $key['business_work_day'].$key['business_time'],
                    'longitude' => $key['wechat_lng'],
                    'latitude' => $key['wechat_lat'],
                    'account_shop_province_id' => $key['province_id'],
                    'account_shop_city_id' => $key['city_id'],
                    'account_shop_area_id' => $key['area_id'],
                    'update_time' => date("Y-m-d H:i:s"),
                ],['store_id'=> $this->store_id,'is_delete'=>0,'account_shop_id' => $key['id']]);
                if ($res)
                {
                    continue;//如果已经更新,则跳过此次循环,到下一次
                }
                if (!is_null($key)){
                    $data[] = [
                        'store_id' => $this->store_id,
                        'user_id' => 0,
                        'addtime' => time(),
                        'is_delete' => 0,
                        'is_open' => 1,
                        'is_lock' => 0,
                        'review_status' => 1,
                        'review_result' => '一小时对接',
                        'review_time' => time(),
                        'realname' => $key['name'],
                        'province_id' => DistrictArr::getProCityDisId('province',$key['province']),
                        'city_id' => DistrictArr::getProCityDisId('city',$key['city']),
                        'district_id' => DistrictArr::getProCityDisId('district',$key['area']),
                        'name' => $key['name'] ? $key['name'] : '',
                        'tel' => $key['phone'] ? $key['phone'] : '',
                        'service_tel' => $key['store_info']['service_phone'] ? $key['store_info']['service_phone'] : '',
                        'account_money' => $key['store_wallet']['total_money'] ? $key['store_wallet']['total_money'] : 0,
                        'business_type_text' => $key['business_type_text'] ? $key['business_type_text'] : '',
                        'logo' => "https://anhour.gzmidai.com/statics/shop/img/shop-logo.png",
                        'code' => $key['code'] ? $key['code'] : '',
                        'account_shop_id' => $key['id'],
                        'province' => $key['province'] ? $key['province'] : '',
                        'city' => $key['city'] ? $key['city'] : '',
                        'district' => $key['area'] ? $key['area'] : '',
                        'address' => $key['address'],
                        'shop_time' => $key['business_work_day'].$key['business_time'],
                        'longitude' => $key['wechat_lng'],
                        'latitude' => $key['wechat_lat'],
                        'account_shop_province_id' => $key['province_id'],
                        'account_shop_city_id' => $key['city_id'],
                        'account_shop_area_id' => $key['area_id'],
                    ];
                }
            }

            //再执行批量插入
            if (isset($data))
            {
                $res = \Yii::$app->db->createCommand()
                    ->batchInsert(
                        Mch::tableName(),
                        ['store_id','user_id','addtime','is_delete','is_open','is_lock','review_status','review_result','review_time','realname','province_id','city_id','district_id','name','tel','service_tel','account_money','business_type_text','logo','code','account_shop_id','province','city','district','address','shop_time','longitude','latitude','account_shop_province_id','account_shop_city_id','account_shop_area_id'],
                        $data
                    )->execute();
            }
            if ($res > 0){
                $t->commit();
            }else{
                $t->rollBack();
            }
        }
        $data = [
            'list' => $list,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'page_count' => 50,
            'row_count' => $shop_data['count']
        ];

        return new ApiResponse(0, 'success', $data);
    }


    private static function distance($distance)
    {
        if ($distance == -1) {
            return -1;
        }
        if ($distance > 1000) {
            $distance = round($distance / 1000, 2) . 'km';
        } else {
            $distance .= 'm';
        }
        return $distance;
    }

    /**
     * 根据起点坐标和终点坐标测距离
     * @param  [array]   $from  [起点坐标(经纬度),例如:array(118.012951,36.810024)]
     * @param  [array]   $to    [终点坐标(经纬度)]
     * @param  [bool]    $km        是否以公里为单位 false:米 true:公里(千米)
     * @param  [int]     $decimal   精度 保留小数位数
     * @return [string]  距离数值
     */
    function get_distance($from, $to, $km = true, $decimal = 2)
    {
        sort($from);
        sort($to);
        $EARTH_RADIUS = 6370.996; // 地球半径系数

        $distance = $EARTH_RADIUS * 2 * asin(sqrt(pow(sin(($from[0] * pi() / 180 - $to[0] * pi() / 180) / 2), 2) + cos($from[0] * pi() / 180) * cos($to[0] * pi() / 180) * pow(sin(($from[1] * pi() / 180 - $to[1] * pi() / 180) / 2), 2))) * 1000;

        if ($km) {
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }

}
