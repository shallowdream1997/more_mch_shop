<?php
namespace app\modules\api\models;


use app\utils\CurlHelper;

class HttpRequestForm extends Model
{
    public $user_id;

    public function existBatchNumber()
    {
        $data = [
            'batch_number' => 191121188656,
            'store_id' => 528
        ];
        $http_resquest = CurlHelper::get('storemall/order/existBatchNumber',$data);

        return json_decode($http_resquest);
    }

    public function getAccountByPhone()
    {
        $data = [
            'phone' => '15622363466',
        ];
        $http_resquest = CurlHelper::get('storemall/account/getAccountByPhone',$data);

        return json_decode($http_resquest);
    }
}