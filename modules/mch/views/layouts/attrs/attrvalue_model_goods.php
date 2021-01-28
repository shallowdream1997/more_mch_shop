<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<!--产品参数设置-->
<div class="step-block" flex="dir:left box:first">
    <div>
        <span>产品模型</span>
    </div>
    <div>
        <div class="form-group row">
            <div class="col-3 text-right">
                <label class=" col-form-label required">产品模型</label>
            </div>
            <div class="col-9">
                <select class="form-control short-row addattr" name="model[attrbute_id]" id="attrtype">
                    <option value="0">请选择模型</option>
                    <?php foreach ($models_list as $m) : ?>
                        <option value="<?= $m->id ?>" <?= $m->id == $goods['attrbute_id'] ? 'selected' : '' ?>><?= $m->model_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="step-block" flex="dir:left box:first">
    <div>
        <span>模型规格参数</span>
    </div>
    <div>
        <div>
            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">模型参数</label>
                </div>
                <div class="col-9">
                    <div v-for="(model_group,i) in model_group_list" :key="i" class="attr-group">
                        <div>
                            <input hidden type="text" v-bind:name="'model[model_group_name]['+i+']'" v-bind:value="model_group.model_group_name">
                            <b>{{model_group.model_group_name}}</b>
                        </div>
                        <div class="attr-group">
                            <div v-for="(model,j) in model_group.model_list" class="attr-group">
                                <div class="attr-item">
                                    <input hidden type="text" v-bind:name="'model[model_name]['+i+']['+j+'][model_name]'" v-bind:value="model.model_name">
                                    <span class="attr-name">{{model.model_name}}</span>
                                    <input type="text" v-bind:name="'model[model_name]['+i+']['+j+'][model_name_value]'" v-bind:value="model.model_name_value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
