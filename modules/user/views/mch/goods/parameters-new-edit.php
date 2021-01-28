<?php
defined('YII_ENV') or exit('Access Denied');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 10:49
 */

$urlManager = Yii::$app->urlManager;
$this->title = '商品编辑';
$staticBaseUrl = Yii::$app->request->baseUrl . '/statics';
$this->params['active_nav_group'] = 2;
$returnUrl = Yii::$app->request->referrer;
if (!$returnUrl) {
    $returnUrl = $urlManager->createUrl([get_plugin_url() . '/goods']);
}
?>
<script src="<?= $staticBaseUrl ?>/mch/js/uploadVideo.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/datetime.js?v=2.5.8"></script>
<style>
    .cat-box {
        border: 1px solid rgba(0, 0, 0, .15);
    }

    .cat-box .row {
        margin: 0;
        padding: 0;
    }

    .cat-box .col-6 {
        padding: 0;
    }

    .cat-box .cat-list {
        border-right: 1px solid rgba(0, 0, 0, .15);
        overflow-x: hidden;
        overflow-y: auto;
        height: 10rem;
    }

    .cat-box .cat-item {
        border-bottom: 1px solid rgba(0, 0, 0, .1);
        padding: .5rem 1rem;
        display: block;
        margin: 0;
    }

    .cat-box .cat-item:last-child {
        border-bottom: none;
    }

    .cat-box .cat-item:hover {
        background: rgba(0, 0, 0, .05);
    }

    .cat-box .cat-item.active {
        background: rgb(2, 117, 216);
        color: #fff;
    }

    .cat-box .cat-item input {
        display: none;
    }

    form .head {
        position: fixed;
        top: 100px;
        right: 1rem;
        left: calc(240px + 1rem);
        z-index: 1001;
        padding-top: 1rem;
        background: #f5f7f9;
        padding-bottom: 1rem;
    }

    form .head .head-content {
        background: #fff;
        border: 1px solid #eee;
        height: 40px;
    }

    .head-step {
        height: 100%;
        padding: 0 20px;
    }

    .step-block {
        position: relative;
    }

    form .body {
        padding-top: 45px;
    }

    .step-block > div {
        padding: 20px;
        background: #fff;
        border: 1px solid #eee;
        margin-bottom: 5px;
    }

    .step-block > div:first-child {
        padding: 20px;
        width: 120px;
        margin-right: 5px;
        font-weight: bold;
        text-align: center;
    }

    .step-block .step-location {
        position: absolute;
        top: -172px;
        left: 0;
    }

    .step-block:first-child .step-location {
        top: -190px;
    }

    form .short-row {
        width: 450px;
    }

    .attr-group {
        border: 1px solid #eee;
        padding: .5rem .75rem;
        margin-bottom: .5rem;
        border-radius: .15rem;
    }

    .attr-group-delete {
        display: inline-block;
        background: #eee;
        color: #fff;
        width: 1rem;
        height: 1rem;
        text-align: center;
        line-height: 1rem;
        border-radius: 999px;
    }

    .attr-group-delete:hover {
        background: #ff4544;
        color: #fff;
        text-decoration: none;
    }

    .attr-list > div {
        vertical-align: top;
    }

    .attr-item {
        display: inline-block;
        background: #eee;
        margin-right: 1rem;
        margin-top: .5rem;
        overflow: hidden;
    }

    .attr-item .attr-name {
        padding: .15rem .75rem;
        display: inline-block;
    }

    .attr-item .attr-delete {
        padding: .35rem .75rem;
        background: #d4cece;
        color: #fff;
        font-size: 1rem;
        font-weight: bold;
    }

    .attr-item .attr-delete:hover {
        text-decoration: none;
        color: #fff;
        background: #ff4544;
    }

    .panel {
        margin-top: calc(50px + 1rem);
    }

    form .form-group .col-3 {
        -webkit-box-flex: 0;
        -webkit-flex: 0 0 160px;
        -ms-flex: 0 0 160px;
        flex: 0 0 160px;
        max-width: 160px;
        width: 160px;
    }
</style>


<div id="one_menu_bar">
    <div id="tab_bar">
        <ul>
            <li class="tab_bar_item" id="tab1" onclick="myclick(1)" style="background-color: #eeeeee">
                基础设置
            </li>
        </ul>
    </div>
    <div id="page">
        <form class="auto-form" method="post" return="<?= $returnUrl ?>">
            <div class="tab_css" id="tab1_content" style="display: block">
                <div>
                    <div class="panel mb-3">
                        <div class="panel-header"><?= $this->title ?></div>
                        <div class="panel-body">

                            <div class="head">
                                <div class="head-content" flex="dir:left">
                                    <a flex="cross:center" class="head-step" href="#step2">基本信息</a>
                                </div>
                            </div>

                            <div class="step-block" flex="dir:left box:first">
                                <div>
                                    <span>基本信息</span>
                                    <span class="step-location" id="step2"></span>
                                </div>
                                <div>
                                    <div class="form-group row">
                                        <div class="col-3 text-right">
                                            <label class=" col-form-label required">模型名称</label>
                                        </div>
                                        <div class="col-9">
                                            <input class="form-control short-row" type="text" name="model[modelname]"
                                                   value="<?= str_replace("\"", "&quot", $models['model_name']) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-3 text-right">
                                            <label class=" col-form-label">排序</label>
                                        </div>
                                        <div class="col-9">
                                            <input class="form-control short-row" type="number" step="1" flex="1" max="9999" name="model[sort]"
                                                   value="<?= $models['sort'] ?>">
                                            <div class="text-muted fs-sm">排序按升序排列</div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-3 text-right">
                                            <label class=" col-form-label">说明</label>
                                        </div>
                                        <div class="col-9">
                                            <div class="input-group short-row">
                                                <input type="text" class="form-control"
                                                       name="model[model_comment]"
                                                       value="<?= $models['model_comment']?>">
                                            </div>
                                        </div>
                                    </div>


                                    <div <?= in_array(get_plugin_type(), [2,5]) ? 'hidden' : '' ?> class="form-group row">
                                        <div class="col-3 text-right">
                                            <label class="col-form-label">是否开启</label>
                                        </div>
                                        <div class="col-9">
                                            <label class="radio-label">
                                                <input <?= $models['is_use'] == 0 ? 'checked' : null ?>
                                                        value="0" name="model[is_use]" type="radio"
                                                        class="custom-control-input">
                                                <span class="label-icon"></span>
                                                <span class="label-text">关闭</span>
                                            </label>
                                            <label class="radio-label">
                                                <input <?= $models['is_use'] == 1 ? 'checked' : null ?>
                                                        value="1" name="model[is_use]" type="radio"
                                                        class="custom-control-input">
                                                <span class="label-icon"></span>
                                                <span class="label-text">开启</span>
                                            </label>

                                            <div class="fs-sm text-danger">如果关闭，商品无法选取该模型</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?= $this->render('/layouts/attrvalue_setting') ?>

                        </div>
                    </div>

                </div>
            </div>


            <div style="margin-left: 0;" class="form-group row text-center">
                <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                <input type="button" class="btn btn-default ml-4"
                       name="Submit" onclick="javascript:history.back(-1);" value="返回">
            </div>
        </form>

    </div>
</div>

<?= $this->render('/layouts/common', [
    'page_type' => 'MODELS',
    'models' => $models
]) ?>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.config.js?v=1.9.6"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.all.min.js?v=1.9.6"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/Sortable.min.js"></script>
<script>
    var Map = function () {
        this._data = [];
        this.set = function (key, val) {
            for (var i in this._data) {
                if (this._data[i].key == key) {
                    this._data[i].val = val;
                    return true;
                }
            }
            this._data.push({
                key: key,
                val: val,
            });
            return true;
        };
        this.get = function (key) {
            for (var i in this._data) {
                if (this._data[i].key == key)
                    return this._data[i].val;
            }
            return null;
        };
        this.delete = function (key) {
            for (var i in this._data) {
                if (this._data[i].key == key) {
                    this._data.splice(i, 1);
                }
            }
            return true;
        };
    };
    var map = new Map();

    var ue = UE.getEditor('editor', {
        serverUrl: "<?=$urlManager->createUrl(['upload/ue'])?>",
        enableAutoSave: false,
        saveInterval: 1000 * 3600,
        enableContextMenu: false,
        autoHeightEnabled: false,
    });


    $(document).on("change", ".attr-select", function () {
        var name = $(this).attr("data-name");
        var id = $(this).val();
        if ($(this).prop("checked")) {
        } else {
        }
    });

    $(document).on("click", ".add-attr-group-btn", function () {
        var name = $(".add-attr-group-input").val();
        name = $.trim(name);
        if (name == "")
            return;
        page.model_group_list.push({
            model_group_name: name,
            model_list: [],
        });
        $(".add-attr-group-input").val("");
        page.checked_attr_list = getAttrList();
    });

    $(document).on("click", ".add-attr-btn", function () {
        var name = $(this).parents(".attr-input-group").find(".add-attr-input").val();
        var index = $(this).attr("index");
        name = $.trim(name);
        if (name == "")
            return;
        page.model_group_list[index].model_list.push({
            model_name: name,
        });

        // 如果是单规格的，添加新规格时不清空原先的数据
        page.old_checked_attr_list = page.checked_attr_list;
        page.model_group_count = page.model_group_list.length;
        var attrList = getAttrList();
        if (page.model_group_list.length === 1) {
            for (var i in attrList) {
                if (i > page.old_checked_attr_list.length - 1) {
                    page.old_checked_attr_list.push(attrList[i])
                }
            }
            var newCheckedAttrList = page.old_checked_attr_list;
        } else if (page.model_group_list.length === page.model_group_count) {
            for (var pi in attrList) {
                var pAttrName = '';
                for (var pj in attrList[pi].model_list) {
                    pAttrName += attrList[pi].model_list[pj].model_name
                }
                for (var ci in page.old_checked_attr_list) {
                    var cAttrName = '';
                    for (var cj in page.old_checked_attr_list[ci].model_list) {
                        cAttrName += page.old_checked_attr_list[ci].model_list[cj].model_name;
                    }
                    if (pAttrName === cAttrName) {
                        attrList[pi] = page.old_checked_attr_list[ci];
                    }
                }
            }
            var newCheckedAttrList = attrList;
        } else {
            var newCheckedAttrList = attrList;
        }
        $(this).parents(".attr-input-group").find(".add-attr-input").val("");
        page.checked_attr_list = newCheckedAttrList;
    });


    $(document).on("click", ".attr-group-delete", function () {
        var index = $(this).attr("index");
        page.model_group_list.splice(index, 1);
        page.checked_attr_list = getAttrList();
    });

    $(document).on("click", ".attr-delete", function () {
        var index = $(this).attr("index");
        var group_index = $(this).attr("group-index");
        page.model_group_list[group_index].model_list.splice(index, 1);

        // 如果是单规格的，删除规格时不清空原先的数据
        page.old_checked_attr_list = page.checked_attr_list;
        var attrList = getAttrList();
        if (page.model_group_list.length === 1) {
            var newCheckedAttrList = [];
            for (var i in page.model_group_list[0].model_list) {
                var attrName = page.model_group_list[0].model_list[i].model_name;
                for (j in page.old_checked_attr_list) {
                    var oldAttrName = page.old_checked_attr_list[j].model_list[0].model_name;
                    if (attrName === oldAttrName) {
                        newCheckedAttrList.push(page.old_checked_attr_list[j]);
                        break;
                    }
                }
            }
        } else if (page.model_group_list.length === page.model_group_count) {
            for (var pi in attrList) {
                var pAttrName = '';
                for (var pj in attrList[pi].model_list) {
                    pAttrName += attrList[pi].model_list[pj].model_name
                }
                for (var ci in page.old_checked_attr_list) {
                    var cAttrName = '';
                    for (var cj in page.old_checked_attr_list[ci].model_list) {
                        cAttrName += page.old_checked_attr_list[ci].model_list[cj].model_name;
                    }
                    if (pAttrName === cAttrName) {
                        attrList[pi] = page.old_checked_attr_list[ci];
                    }
                }
            }
            var newCheckedAttrList = attrList;
        } else {
            var newCheckedAttrList = attrList;
        }

        page.checked_attr_list = newCheckedAttrList;
    });


    function getAttrList() {
        var array = [];
        for (var i in page.model_group_list) {
            for (var j in page.model_group_list[i].model_list) {
                var object = {
                    model_group_name: page.model_group_list[i].model_group_name,
                    attr_id: null,
                    model_name: page.model_group_list[i].model_list[j].model_name,
                };
                if (!array[i])
                    array[i] = [];
                array[i].push(object);
            }
        }
        var len = array.length;
        var results = [];
        var indexs = {};

        function specialSort(start) {
            start++;
            if (start > len - 1) {
                return;
            }
            if (!indexs[start]) {
                indexs[start] = 0;
            }
            if (!(array[start] instanceof Array)) {
                array[start] = [array[start]];
            }
            for (indexs[start] = 0; indexs[start] < array[start].length; indexs[start]++) {
                specialSort(start);
                if (start == len - 1) {
                    var temp = [];
                    for (var i = len - 1; i >= 0; i--) {
                        if (!(array[start - i] instanceof Array)) {
                            array[start - i] = [array[start - i]];
                        }
                        if (array[start - i][indexs[start - i]]) {
                            temp.push(array[start - i][indexs[start - i]]);
                        }
                    }
                    var key = [];
                    for (var i in temp) {
                        key.push(temp[i].attr_id);
                    }
                    var oldVal = map.get(key.sort().toString());
                    if (oldVal) {
                        results.push({
                            num: oldVal.num,
                            price: oldVal.price,
                            no: oldVal.no,
                            pic: oldVal.pic,
                            model_list: temp
                        });
                    } else {
                        var obj = {
                            num: 0,
                            price: 0,
                            no: '',
                            pic: '',
                            model_list: temp,
                            share_commission_first: '',
                            share_commission_second: '',
                            share_commission_third: '',
                        };

                        var levelList = page.level_list;
                        for (var i = 0; i < levelList.length; i++) {
                            var keyName = 'member' + levelList[i].id;
                            obj[keyName] = '';
                        }

                        results.push(obj);
                    }
                }
            }
        }

        specialSort(-1);
        return results;
    }
</script>
