<?php
/**
 * YiXiaoShi
 * ==================================================================
 * CopyRight © 2017-2099 广州米袋软件有限公司
 * 官网地址：http://www.mdsoftware.cn
 * 售后技术支持：15017566075
 * ------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下
 * 对本程序代码进行修改和使用；不允许对本程序代码以任何目的的再发布。
 * ==================================================================
 *
 * @ClassName CommonCurlAnhour
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2020-12-29 15:19 星期二
 * @Version 1.0
 * @Description 对接一小时公共接口
 */
namespace app\models\curlanhour;

use app\utils\CurlHelper;

class CommonCurlAnhour
{
    public $type; //接口类型 【GET，POST】

    public $data; //接口所传参数

    public $url; //接口名

    public function selectType()
    {
        switch ($this->type) {
            case "GET": //获取用户钱包信息，更新
                return $this->commonGetCurl($this->url,$this->data);
                break;
            case "POST": //微信公众号模板消息
                return $this->commonPostCurl($this->url,$this->data);
                break;
        }
    }

    //公共curl get 一小时对接接口
    public function commonGetCurl($url = '',$data = [])
    {
        $curl = json_decode(CurlHelper::get($url,$data));
        if($curl->error_cod == 0) {
            return $curl;
        }
        \Yii::error("一小时对接接口{$url}报错~");
    }

    //公共curl post 一小时对接接口
    public function commonPostCurl($url = '',$data = [])
    {

        //用户钱包信息
        $curl = json_decode(CurlHelper::post($url,$data));
        if($curl->error_cod == 0) {
            return $curl;
        }
        \Yii::error("一小时对接接口{$url}报错~");
    }
}
