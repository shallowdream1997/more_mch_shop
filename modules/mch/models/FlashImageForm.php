<?php
/**
 * more_mch_shop
 * ==================================================================
 * CopyRight © 2017-2099 广州米袋软件有限公司
 * 官网地址：http://www.mdsoftware.cn
 * 售后技术支持：15017566075
 * ------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下
 * 对本程序代码进行修改和使用；不允许对本程序代码以任何目的的再发布。
 * ==================================================================
 *
 * @ClassName FlashImageForm
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-01-28 17:38 星期四
 * @Version 1.0
 * @Description 图片素材列表
 */

namespace app\modules\mch\models;


use app\models\UploadFile;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class FlashImageForm extends MchModel
{
    public $store_id;

    public function getFlashList()
    {
        $query = UploadFile::find()->where(['store_id' => $this->store_id,'is_delete' => 0,'mch_id' => 0,'type' => 'image']);
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query
            ->orderBy('size ASC')
            ->offset($p->offset)
            ->limit($p->limit)
            ->asArray()
            ->all();

        return [$list, $p];
    }

    public function save()
    {

    }
}
