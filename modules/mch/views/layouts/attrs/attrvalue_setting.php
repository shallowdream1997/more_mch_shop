<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

?>

<div class="step-block" flex="dir:left box:first">
    <div>
        <span>模型规格</span>
    </div>
    <div>
        <!-- 规格开关 -->
        <div class="form-group row" <?= get_plugin_type() == 2 ? 'hidden' : '' ?>>
            <div class="col-3 text-right">
                <label class="col-form-label">创建模型参数</label>
            </div>
            <div class="col-9 col-form-label" hidden>
                <label class="custom-control custom-checkbox">
                    <input type="checkbox"
                           name="model[use_attr]"
                           value="1"
                           checked
                           disabled
                           class="custom-control-input use-attr">
                    <span class="custom-control-indicator"></span>
                </label>
            </div>
        </div>

        <!-- 有规格 -->
        <div class="attr-edit-block" <?= get_plugin_type() == 2 ? 'hidden' : '' ?>>
            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">一级模型参数</label>
                </div>
                <div class="col-9">

                    <div class="input-group short-row mb-2" v-if="attr_group_list.length<10">
                        <span class="input-group-addon">一级规格</span>
                        <input class="form-control add-attr-group-input"
                               placeholder="如主体 基本信息">
                        <span class="input-group-btn">
                            <a class="btn btn-secondary add-attr-group-btn" href="javascript:">添加</a>
                        </span>
                    </div>

                    <div v-for="(model_group,i) in model_group_list" class="attr-group">
                        <div>
                            <input hidden type="text" v-bind:name="'model[model_group_name]['+i+']'" v-bind:value="model_group.model_group_name">
                            <b>{{model_group.model_group_name}}</b>
                            <a v-bind:index="i" href="javascript:"
                               class="attr-group-delete">×</a>
                        </div>
                        <div class="attr-list">
                            <div v-for="(model,j) in model_group.model_list" class="attr-item">
                                <input hidden type="text" v-bind:name="'model[model_name]['+i+']['+j+'][model_name]'" v-bind:value="model.model_name">
                                <span class="attr-name">{{model.model_name}}</span>
                                <a v-bind:group-index="i" v-bind:index="j"
                                   class="attr-delete"
                                   href="javascript:">×</a>
                            </div>
                            <div style="display: inline-block;width: 200px;margin-top: .5rem">
                                <div class="input-group attr-input-group"
                                     style="border-radius: 0">
                                        <span class="input-group-addon"
                                              style="padding: .35rem .35rem;font-size: .8rem">二级规格</span>
                                    <input class="form-control form-control-sm add-attr-input"
                                           placeholder="如入网型号">
                                    <span class="input-group-btn">
                                        <a v-bind:index="i" class="btn btn-secondary btn-sm add-attr-btn"
                                           href="javascript:">添加</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
