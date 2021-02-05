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
 * @ClassName ${NAME}
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-02-03 12:19 星期三
 * @Version 1.0
 * @Description ${TODO}
 */
/**
 * The PHP File index.php Is Created By Idea
 * @User 123 云深知梦
 * @Date 2021/2/3
 * @Time 12:19
 */

defined('YII_ENV') or exit('Access Denied');
$this->title = '店铺设置';
/** @var \app\models\Mch $model
 * @var \app\models\MchCommonCat[] $mch_common_cat_list
 */
$urlManager = Yii::$app->urlManager;
?>
<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key=OV7BZ-ZT3HP-6W3DE-LKHM3-RSYRV-ULFZV"></script>

<div class="panel">
    <div class="panel-body">
        <div class="p-3">
            <div style="width: 10rem">
                <img style="width: 8rem;height: 8rem;border-radius: 9999px;margin:0 1rem 1rem 1rem"
                     src="<?= $mch['avatar_url'] ?>">
                <div style="text-align: center;font-size: 1.15rem;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= $mch['realname'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body">
        <form class="auto-form" method="post" style="display: inline-block;width: 50%;">
            <div class="form-group row">
                <div class="col-sm-2 form-group-label text-right">
                    <label class="col-form-label">基本信息</label>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">联系人</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[realname]" value="<?= $mch['realname'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">联系电话</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[tel]" value="<?= $mch['tel'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 form-group-label text-right">
                    <label class="col-form-label">店铺信息</label>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">店铺名称</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[name]" value="<?= $mch['name'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">所在地区</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="hidden" name="model[province_id]" value="<?= $mch['province_id'] ?>">
                        <input type="hidden" name="model[city_id]" value="<?= $mch['city_id'] ?>">
                        <input type="hidden" name="model[district_id]" value="<?= $mch['district_id'] ?>">
                        <input class="form-control district-text"
                               value="<?= $province->name ?>-<?= $city->name ?>-<?= $district->name ?>" readonly>
                        <span class="input-group-btn">
                            <a class="btn btn-secondary picker-district" href="javascript:">选择地区</a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">详细地址</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[address]" value="<?= $mch['address'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">主营内容</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[main_content]" value="<?= $mch['main_content'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">经营时间</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[shop_time]" value="<?= $mch['shop_time'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">店铺简介</label>
                </div>
                <div class="col-sm-6">
                    <textarea class="form-control" name="model[summary]"><?= $mch['summary'] ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">经度</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[longitude]" value="<?= $mch['longitude'] ?>">
                    <div class="fs-sm">经纬度可以在地图上选择，也可以自己添加</div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">纬度</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[latitude]" value="<?= $mch['latitude'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">客服电话</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="model[service_tel]" value="<?= $mch['service_tel'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">店铺头像</label>
                </div>
                <div class="col-sm-6">
                    <div class="upload-group">
                        <div class="input-group">
                            <input class="form-control file-input" name="model[logo]" value="<?= $mch['logo'] ?>">
                            <span class="input-group-btn">
                                <a class="btn btn-secondary upload-file" href="javascript:" data-toggle="tooltip"
                                   data-placement="bottom" title="上传文件">
                                    <span class="iconfont icon-cloudupload"></span>
                                </a>
                            </span>
                            <span class="input-group-btn">
                                <a class="btn btn-secondary delete-file" href="javascript:" data-toggle="tooltip"
                                   data-placement="bottom" title="删除文件">
                                    <span class="iconfont icon-close"></span>
                                </a>
                            </span>
                        </div>
                        <div class="upload-preview text-center upload-preview">
                            <span class="upload-preview-tip">100&times;100</span>
                            <img class="upload-preview-img" src="<?= $mch['logo'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">店铺背景（顶部）</label>
                </div>
                <div class="col-sm-6">
                    <div class="upload-group">
                        <div class="input-group">
                            <input class="form-control file-input" name="model[header_bg]"
                                   value="<?= $mch['header_bg'] ?>">
                            <span class="input-group-btn">
                                <a class="btn btn-secondary upload-file" href="javascript:" data-toggle="tooltip"
                                   data-placement="bottom" title="上传文件">
                                    <span class="iconfont icon-cloudupload"></span>
                                </a>
                            </span>
                            <span class="input-group-btn">
                                <a class="btn btn-secondary delete-file" href="javascript:" data-toggle="tooltip"
                                   data-placement="bottom" title="删除文件">
                                    <span class="iconfont icon-close"></span>
                                </a>
                            </span>
                        </div>
                        <div class="upload-preview text-center upload-preview">
                            <span class="upload-preview-tip">750&times;300</span>
                            <img class="upload-preview-img" src="<?= $mch['header_bg'] ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">手续费(千分之)</label>
                </div>
                <div class="col-sm-6">
                    <input type="number" min="0" max="1000" step="1" class="form-control" readonly
                           value="<?= $mch['transfer_rate'] ?>">
                    <div>商户每笔订单交易金额扣除的手续费</div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                </div>
            </div>
        </form>
        <div style="display: inline-block;vertical-align: top;width: 45%">
            <div class="form-group row map">
                <div class="offset-2 col-9">
                    <div class="input-group" style="margin-top: 20px;">
                        <input class="form-control region" type="text" placeholder="城市">
                        <span class="input-group-addon ">和</span>
                        <input class="form-control keyword" type="text" placeholder="关键字">
                        <a class="input-group-addon search" href="javascript:">搜索</a>
                    </div>
                    <div class="text-info">搜索时城市和关键字必填</div>
                    <div class="text-info">点击地图上的蓝色点，获取经纬度</div>
                    <div class="text-danger map-error mb-3" style="display: none">错误信息</div>
                    <div id="container" style="min-width:600px;min-height:600px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
