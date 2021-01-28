<?php
defined('YII_ENV') or exit('Access Denied');

$urlManager = Yii::$app->urlManager;
$this->title = '热搜词设置';
$this->params['active_nav_group'] = 4;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="">
            <form method="post" class="form auto-form" autocomplete="off"
                  return="<?= $urlManager->createUrl(['mch/hot-search/index']) ?>">
                <div class="form-body">
                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
                            <label class="col-form-label required">热搜词</label>
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control" name="keywords" placeholder="热搜词"
                                    value="<?= $model->keywords ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="form-group-label col-sm-2 text-right">
                            <label class="col-form-label">是否显示</label>
                        </div>
                        <div class="col-sm-6">
                            <label class="radio-label">
                                <input id="radio2" <?= $model->is_show == 0 ? 'checked' : null ?>
                                       value="0"
                                       name="is_show" type="radio" class="custom-control-input">
                                <span class="label-icon"></span>
                                <span class="label-text">关闭</span>
                            </label>
                            <label class="radio-label">
                                <input id="radio1" <?= $model->is_show == 1 ? 'checked' : null ?>
                                       value="1"
                                       name="is_show" type="radio" class="custom-control-input">
                                <span class="label-icon"></span>
                                <span class="label-text">开启</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
                        </div>
                        <div class="col-5">
                            <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                            <input type="button" class="btn btn-default ml-4" 
                                   name="Submit" onclick="javascript:history.back(-1);" value="返回">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
