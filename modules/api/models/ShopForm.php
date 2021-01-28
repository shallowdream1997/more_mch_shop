<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/24
 * Time: 14:44
 */

namespace app\modules\api\models;

use app\hejiang\ApiResponse;
use app\models\DistrictArr;
use app\models\Mch;
use app\models\Option;
use app\models\Printer;
use app\models\Shop;
use app\models\ShopPic;
use app\utils\CurlHelper;

class ShopForm extends ApiModel
{
    public $store_id;
    public $user;
    public $shop_id;

    public $longitude; //经度
    public $latitude; //纬度

    public $type; //区分进入详情页的场景值 1-是由列表选择进入 不传-表示由首页进入
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['longitude', 'latitude','shop_id'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        if (!trim($this->shop_id)){
            $shop = Mch::find()->where([
                'store_id' => $this->store_id, 'is_store' => 1, 'is_delete' => 0
            ])->asArray()->one();
        }else{
            if ($this->type == 1){
                if (substr($this->shop_id,-1) === '@') //匹配最后字符串是否是查询所得
                {
                    $shop_id = trim($this->shop_id,'@');
                    $shop = Mch::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'id'=>$this->shop_id])->asArray()->one();
                }else{
                    $shopdata = [
                        'id' => $this->shop_id,
                    ];
                    $res = CurlHelper::get('storemall/store/getStore',$shopdata);
                    $shop_data = json_decode($res,true);
                    if ($shop_data['error_code'] == 0){
                        $shop = Mch::find()->where(['store_id'=>$this->store_id,'account_shop_id'=>$this->shop_id])->asArray()->one();

                        if (!empty($shop_data['store']['store_info']['print_sn'])){
                            $print_setting = [
                                'user'=>$shop_data['store']['store_info']['print_sn'],
                                'ukey'=>$shop_data['store']['store_info']['print_key'],
                                'sn'=>$shop_data['store']['store_info']['label_print_sn'],
                                'key'=>$shop_data['store']['store_info']['label_print_key'],
                                'time'=>1,
                            ];
                            $prt = Printer::findOne(['mch_id'=>$shop['id']]);
                            if (!$prt){
                                $prt = new Printer();
                            }
                            $prt->store_id = $this->store_id;
                            $prt->name = $shop['name'].'打印机';
                            $prt->printer_type = 'feie';
                            $prt->printer_setting = \Yii::$app->serializer->encode($print_setting);
                            $prt->addtime = time();
                            $prt->mch_id = $shop['id'];
                            $prt->save();
                        }
                    }
                }
            }else{
                $shop = Mch::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'id'=>$this->shop_id])->asArray()->one();
            }
        }

        if (!$shop) {
            return new ApiResponse(1, '店铺不存在');
        }

        $shop['distance'] = -1;
        if ($this->longitude && $shop['longitude']){
            $from = [$this->longitude, $this->latitude];
            $to = [$shop['longitude'], $shop['latitude']];
            $shop['distance'] = $this->distance($this->get_distance($from, $to, false, 2));
        }

        foreach ($shop as $index => &$value) {
            if (!$value) {
                if (in_array($index, ['pic_url', 'cover_url', 'pic_list'])) {
                    continue;
                }
//                $shop[$index] = "暂无设置";
            }
            if ($index == 'content') {
                $value = str_replace("&amp;nbsp;", " ", $value);
                $value = str_replace("&nbsp;", " ", $value);
            }
        }
        $shop['tel_icon'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/shop/img/tel.png';
        return new ApiResponse(0, 'success', ['shop'=>$shop]);
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
