<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 9:46
 */
defined('YII_ENV') or exit('Access Denied');
/* @var $list \app\models\YongyouIsv */
$urlManager = Yii::$app->urlManager;
$this->title = '用友ISV配置';
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="auto-form" method="post">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">ISV账号的AppKey</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="isv_appkey"
                           value="<?= $list->isv_appkey ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">ISV账号的AppSecret</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-hide">
                        <input class="form-control" value="<?= $list->isv_appsecret ?>" name="isv_appsecret">
                        <div class="tip-block">已隐藏内容，点击查看或编辑</div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">ISV账号的pem</label>
                </div>
                <div class="col-sm-6">
                    <div class="input-hide">
                        <textarea rows="5" class="form-control secret-content" name="cert"><?= $list->cert ?></textarea>
                        <div class="tip-block">已隐藏内容，点击查看或编辑</div>
                    </div>
                    <div class="fs-sm text-muted">使用文本编辑器打开cert.pem文件，将文件的全部内容复制进来</div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">企业云账号</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="orgid"
                           value="<?= $list->orgid ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">认证模式</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="authmode"
                           value="<?= $list->authmode ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">账套账号ID</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="account_id"
                           value="<?= $list->account_id ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">账套账号Password</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="account_password"
                           value="<?= $list->account_password ?>">
                </div>
            </div><div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">账套编号</label>
                </div>
                <div class="col-sm-6">
                    <input autocomplete="off" class="form-control" type="text" name="account_number"
                           value="<?= $list->account_number ?>">
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
    </div>
</div>