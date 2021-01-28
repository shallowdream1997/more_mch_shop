<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/3/9
 * Time: 10:26
 */

namespace app\modules\user\controllers;

use app\hejiang\ApiCode;
use app\models\DistrictArr;
use app\models\Mch;
use app\models\MchAuthLogin;
use app\models\MemberId;
use app\models\Store;
use app\models\StorePermission;
use app\models\User;
use app\models\UserAuthLogin;
use app\utils\CurlHelper;
use app\utils\GenerateShareQrcode;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Response;

class PassportController extends Controller
{
    public $layout = 'passport';

    public function actionLogin($_platform = null)
    {
        $m = new UserAuthLogin();
        $img_url = null;
        $error = null;
        if ($_platform) {
            $m->store_id = $this->store->id;
            if ($_platform == 'my') {
                $m->token = 'token=' . md5(uniqid());
            } else {
                $m->token = md5(uniqid());
            }
            $m->addtime = time();
            $m->save();
            $res = GenerateShareQrcode::getQrcode($this->store->id, $m->token, 430, 'pages/web/login/login');
            if ($res['code'] == 0) {
                //保存到本地
                $saveRoot = \Yii::$app->basePath . '/web/mchqrcode';
                $saveDir = '/';
                if (!is_dir($saveRoot . $saveDir)) {
                    mkdir($saveRoot . $saveDir);
                    file_put_contents($saveRoot . $saveDir . '.gitignore', "*\r\n!.gitignore");
                }
                $saveName = md5(uniqid()) . '.jpg';
                $webRoot = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/mchqrcode/' . $saveName);
                file_put_contents($saveRoot . $saveDir . $saveName, file_get_contents($res['file_path']));
//                $img_path = str_replace('\\', '/', $res['file_path']);
//                $webRoot = mb_substr(\Yii::$app->request->baseUrl, 0, -4) . '/' . mb_substr($img_path, mb_stripos($img_path, 'runtime/image/'));
            } else {
                $error = $res['msg'];
            }

        }

        $store = Store::findone(\Yii::$app->request->get('store_id'));

        $auth = StorePermission::getOpenPermissionList($store);
        $isAlipay = 0;
        foreach ($auth as $item) {
            if ($item === 'alipay') {
                $isAlipay = 1;
            }
        }

        return $this->render('login', [
            'error' => $error,
            'token' => $m->token,
            'img_url' => $webRoot,
            '_platform' => $_platform,
            'isAlipay' => $isAlipay,
        ]);
    }

    //账户登录平台
    public function actionAccountLogin($_platform = null)
    {
        $store = Store::findone(\Yii::$app->request->get('store_id'));
        $auth = StorePermission::getOpenPermissionList($store);
        $isAlipay = 0;
        foreach ($auth as $item) {
            if ($item === 'alipay') {
                $isAlipay = 1;
            }
        }
        return $this->render('account-login',[
            'isAlipay' => $isAlipay,
        ]);
    }

    public function actionCheckLogin($token)
    {
        $token = '239bbaff6fb718306b06f236839446d8';
        if (!$token) {
            return [
                'code' => 1,
                'msg' => 'token不能为空',
            ];
        }
        for ($i = 0; $i < 3; $i++) {
            $m = UserAuthLogin::findOne(['token' => $token]);
            if (!$m) {
                return [
                    'code' => 1,
                    'msg' => '错误的token',
                ];
            }
            if ($m->is_pass == 0) {
                sleep(3);
            }
            if ($m->is_pass == 1) {
                $user = User::findOne($m->user_id);
                \Yii::$app->user->login($user);

                $mch = Mch::find()->alias('m')
                    ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
                    ->where(['mau.user_id'=>$user->id,'mau.is_default'=>1])
                    ->select('m.*')
                    ->one();

                if (!$mch){
                    //如果扫码用户没有商户，则判断是否是员工
                    $mch = Mch::find()->alias('m')
                        ->innerJoin(['mem'=>MemberId::tableName()],'mem.mch_id=m.id')
                        ->where(['mem.user_id'=>$user->id,'mem.is_delete'=>0])
                        ->select('m.*')
                        ->one();
                    if (!$mch){
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '该用户无店铺，请先申请再扫码登录'
                        ];
                    }
                }
                if ($mch->is_open === Mch::IS_OPEN_FALSE) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '店铺已被关闭,请联系管理员'
                    ];
                }
                \Yii::$app->session->set('store_id', $this->store->id);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '登录成功',
                ];

            }
        }
        return [
            'code' => -1,
            'msg' => '请扫描小程序码登录',
        ];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     * 账号登录平台
     */
    public function actionCheckAccountLogin()
    {
        $data = [
            'phone' => \Yii::$app->request->post('phone'),
            'password' => \Yii::$app->request->post('password')
        ];
        $curl_data = CurlHelper::get('storemall/account/getAccountByPhone',$data);
        $account_data = json_decode($curl_data);
        if ($account_data->error_code == 0){
            $curl_data = ArrayHelper::toArray($account_data->data);
            $user = User::findOne(['binding'=>\Yii::$app->request->post('phone'),'is_delete'=>0]);
            if (!$user){
                return [
                    'code' => 1,
                    'msg' => '您不是本系统用户，非法登陆!!!',
                ];
            }
            $user->account_id = $curl_data['id'];

            \Yii::$app->user->login($user);

            $Mch_data_list = $curl_data['store_administrators'][0]['store_administrator_stores'];
            //批量更新,并将需要批量插入的数据放入数组中
            $time = time();
            $t = \Yii::$app->db->beginTransaction();
            foreach($Mch_data_list as $i => $key)
            {
                if(Mch::updateAll([
                    'store_id' => 1,
                    'user_id' => 0,
                    'review_time' => $time,
                    'review_result' => '一小时对接',
                    'is_delete' => 0,
                    'is_default' => $i === 0 ? 1 : 0,
                    'realname' => (string)$key['store']['name'] ? (string)$key['store']['name'] : '',
                    'province_id' => DistrictArr::getProCityDisId('province',$key['store']['province']),
                    'city_id' => DistrictArr::getProCityDisId('city',$key['store']['city']),
                    'district_id' => DistrictArr::getProCityDisId('district',$key['store']['district']),
                    'name' => (string)$key['store']['name'] ? (string)$key['store']['name'] : '',
                    'tel' => $key['store']['phone'] ? $key['store']['phone'] : '',
                    'service_tel' => $key['store']['store_info']['service_phone'] ? $key['store']['store_info']['service_phone'] : '',
                    'account_shop_money' => $key['store']['store_wallet']['total_money'] ? $key['store']['store_wallet']['total_money'] : 0,
                    'business_type_text' => $key['store']['business_type_text'] ? $key['store']['business_type_text'] : '',
                    'logo' => $key['store']['store_info']['store_photo'] ? $key['store']['store_info']['store_photo'] : '',
                    'code' => $key['store']['code'] ? $key['store']['code'] : '',
                    'account_shop_id' => $key['store']['id'],
                    'province' => $key['store']['province'] ? $key['store']['province'] : '',
                    'city' => $key['store']['city'] ? $key['store']['city'] : '',
                    'district' => $key['store']['district'] ? $key['store']['district'] : '',
                    'address' => $key['store']['address'],
                    'account_id' => $curl_data['id'],
                    'shop_time' => $key['store']['business_work_day'].$key['store']['business_time'],
                    'update_time' => date("Y-m-d H:i:s"),
                ],
                    ['store_id'=> 1,'account_shop_id' => $key['store']['id']]
                ))
                {
                    continue;//如果已经更新,则跳过此次循环,到下一次
                }
                if (!is_null($key['store'])){
                    $mch_data[] = [
                        'store_id' => 1,
                        'user_id' => 0,
                        'addtime' => $time,
                        'is_delete' => 0,
                        'is_open' => 1,
                        'is_lock' => 0,
                        'is_default' => $i === 0 ? 1 : 0,
                        'review_status' => 1,
                        'review_result' => '一小时对接',
                        'review_time' => $time,
                        'realname' => $key['store']['name'],
                        'province_id' => DistrictArr::getProCityDisId('province',$key['store']['province']),
                        'city_id' => DistrictArr::getProCityDisId('city',$key['store']['city']),
                        'district_id' => DistrictArr::getProCityDisId('district',$key['store']['district']),
                        'name' => $key['store']['name'] ? $key['store']['name'] : '',
                        'tel' => $key['store']['phone'] ? $key['store']['phone'] : '',
                        'service_tel' => $key['store']['store_info']['service_phone'] ? $key['store']['store_info']['service_phone'] : '',
                        'account_shop_money' => $key['store']['store_wallet']['total_money'] ? $key['store']['store_wallet']['total_money'] : 0,
                        'business_type_text' => $key['store']['business_type_text'] ? $key['store']['business_type_text'] : '',
                        'logo' => $key['store']['store_info']['store_photo'] ? $key['store']['store_info']['store_photo'] : '',
                        'code' => $key['store']['code'] ? $key['store']['code'] : '',
                        'account_shop_id' => $key['store']['id'],
                        'province' => $key['store']['province'] ? $key['store']['province'] : '',
                        'city' => $key['store']['city'] ? $key['store']['city'] : '',
                        'district' => $key['store']['district'] ? $key['store']['district'] : '',
                        'address' => $key['store']['address'],
                        'account_id' => $curl_data['id'],
                        'shop_time' => $key['store']['business_work_day'].$key['store']['business_time'],
                    ];
                }
            }
            //再执行批量插入
            if (isset($mch_data))
            {
                $res = \Yii::$app->db->createCommand()
                    ->batchInsert(
                        Mch::tableName(),
                        ['store_id','user_id','addtime','is_delete','is_open','is_lock','is_default','review_status','review_result','review_time','realname','province_id','city_id','district_id','name','tel','service_tel','account_money','business_type_text','logo','code','account_shop_id','province','city','district','address','shop_time'],
                        $mch_data
                    )->execute();
            }
            /////

            if ($user->save()){
                $t->commit();
            }else{
                $t->rollBack();
            }
            //登陆账户后获取用户的门店信息，保存写入之后再读，写入（账户-门店）中间表，记录mch_id和account_id
            $mch_list = Mch::find()->where(['account_id'=>\Yii::$app->user->identity->account_id])->all();

            foreach($mch_list as $i => $key)
            {
                if(MchAuthLogin::updateAll([
                    'mch_id' => $key->id,
                    'addtime' => $time,
                    'name' => $key->name ? $key->name : '',
                    'update_at' => date("Y-m-d H:i:s"),
                ],
                    ['store_id'=> 1,'account_id' => $key->account_id,'account_shop_id' => $key->account_shop_id]
                ))
                {
                    continue;//如果已经更新,则跳过此次循环,到下一次
                }
                {
                    $mchauth_data[] = [
                        'store_id' => 1,
                        'user_id' => $user->id,
                        'mch_id' => $key->id,
                        'account_id' => $key->account_id,
                        'account_shop_id' => $key->account_shop_id,
                        'binding' => \Yii::$app->request->post('phone'),
                        'is_default' => $i === 0 ? 1 : 0,
                        'addtime' => $time,
                        'name' => $key->name ? $key->name : '',
                    ];
                }
            }

            if (isset($mchauth_data))
            {
                $res1 = \Yii::$app->db->createCommand()
                    ->batchInsert(
                        MchAuthLogin::tableName(),
                        ['store_id','user_id','mch_id','account_id','account_shop_id','binding','is_default','addtime','name'],
                        $mchauth_data
                    )->execute();
            }

            $mch = Mch::find()->alias('m')
                ->innerJoin(['mau'=>MchAuthLogin::tableName()],'mau.mch_id=m.id')
                ->where(['mau.account_id'=>$curl_data['id'],'mau.is_default'=>1,'mau.binding'=>\Yii::$app->request->post('phone')])
                ->select('m.*')
                ->one();
            if ($mch->is_open === Mch::IS_OPEN_FALSE) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '店铺已被关闭,请联系管理员'
                ];
            }
            \Yii::$app->session->set('store_id', $this->store->id);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '登录成功',
            ];

        }else{
            return [
                'code' => 1,
                'msg' => $account_data->error_msg ? $account_data->error_msg : '没有该账户',
            ];
        }
    }
    public function actionLogout()
    {
        \Yii::$app->user->logout();
        \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['user']))->send();
    }
}
