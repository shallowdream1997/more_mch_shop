<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/6
 * Time: 15:19
 */

namespace app\modules\api\models\mch;


use app\models\DistrictArr;
use app\models\Mch;
use app\models\MchCommonCat;
use app\modules\api\models\ApiModel;
use app\utils\GenerateShareQrcode;

class SettingForm extends ApiModel
{
    /** @var  Mch $mch */
    public $mch;

    public function search()
    {
//        $mch_common_cat_list = MchCommonCat::find()->where(['store_id' => $this->mch->store_id, 'is_delete' => 0])
//            ->select('id,name')->orderBy('sort')->asArray()->all();
//        $mch_common_cat_name = '';
//        foreach ($mch_common_cat_list as $item) {
//            if ($item['id'] == $this->mch->mch_common_cat_id) {
//                $mch_common_cat_name = $item['name'];
//                break;
//            }
//        }
        $mch_data = $this->mch->getAttributes(['realname', 'tel','wechat_name', 'name', 'province_id', 'city_id', 'district_id', 'address', 'mch_common_cat_id', 'service_tel', 'logo', 'header_bg',]);
//        $mch_data['mch_common_cat_name'] = $mch_common_cat_name;
        $province = DistrictArr::getDistrict($this->mch->province_id);
        $city = DistrictArr::getDistrict($this->mch->city_id);
        $district = DistrictArr::getDistrict($this->mch->district_id);
        return [
            'code' => 0,
            'data' => [
                'mch' => $mch_data,
                'edit_district' => [
                    'province' => [
                        'id' => $this->mch->province_id,
                        'name' => $province->name,
                    ],
                    'city' => [
                        'id' => $this->mch->city_id,
                        'name' => $city->name,
                    ],
                    'district' => [
                        'id' => $this->mch->district_id,
                        'name' => $district->name,
                    ],
                ],
//                'mch_common_cat_list' => $mch_common_cat_list,
                'shop_qrcode' => $this->getShopQrcode(),
            ],
        ];
    }

    //门店详情
    public function getMchshop()
    {
        $mch_data = $this->mch->getAttributes(['realname', 'tel', 'name', 'province_id', 'city_id', 'district_id', 'address', 'service_tel', 'logo']);
        $province = DistrictArr::getDistrict($this->mch->province_id);
        $city = DistrictArr::getDistrict($this->mch->city_id);
        $district = DistrictArr::getDistrict($this->mch->district_id);
        return [
            'code' => 0,
            'data' => [
                'mch' => $mch_data,
                'mch_district' => [
                    'province' => [
                        'id' => $this->mch->province_id,
                        'name' => $province->name,
                    ],
                    'city' => [
                        'id' => $this->mch->city_id,
                        'name' => $city->name,
                    ],
                    'district' => [
                        'id' => $this->mch->district_id,
                        'name' => $district->name,
                    ],
                ],
            ],
        ];
    }

    //获取商户id
    public function getShopQrcode()
    {
        $data = [
            'scene' => 'mch_id:' . $this->mch->id,
            'page' => 'mch/shop/shop',
            'width' => '400',
        ];
        $is_img = false;
        $uid = \Yii::$app->user->id;
        if(\Yii::$app->fromAlipayApp()){
            $scene = "mch_id={$this->mch->id}&uid={$uid}";
        }else{
            $scene = "mch_id:{$this->mch->id},uid:{$uid}";
        };
        $res = GenerateShareQrcode::getQrcode($this->store->id,$scene,240,"mch/shop/shop");
        if($res['code'] == 0){
            $is_img = true;
            $img_path = file_get_contents($res['file_path']);
        } else {
            $img_path = "";
        }
        if ($is_img) {
            $qrcode_pic = md5(json_encode(array_merge($data, [
                    'store_id' => $this->store->id,
                ]))) . '.jpg';
            if (!is_dir(\Yii::$app->basePath . '/web/qrcode')) {
                mkdir(\Yii::$app->basePath . '/web/qrcode');
            }
            $res = file_put_contents(\Yii::$app->basePath . '/web/qrcode/' . $qrcode_pic, $img_path);
            if (!$res) {
                return [
                    'code' => 1,
                    'msg' => '获取小程序码失败，文件写入失败。',
                ];
            } else {
                return [
                    'code' => 0,
                    'data' => [
                        'header_bg' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/shop/img/qrcode-header-bg.png',
                        'shop_logo' => $this->mch->logo,
                        'shop_name' => $this->mch->name,
                        'qrcode_pic' => str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/qrcode/' . $qrcode_pic),
                    ],
                ];
            }
        } else {
            $res = json_decode($this->wechat->curl->response, true);
            return [
                'code' => 1,
                'msg' => $res['errmsg'],
            ];
        }
    }

}
