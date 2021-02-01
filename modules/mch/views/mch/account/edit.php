<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/12/28
 * Time: 15:53
 */
$this->title = '添加账户';
$url_manager = Yii::$app->urlManager;
?>
<div class="panel mb-3" id="app">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body">
        <form class="auto-form" method="post"
              return="<?= Yii::$app->request->referrer ? Yii::$app->request->referrer : '' ?>">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right required">
                    <label class="col-form-label required">联系账号(手机号)</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="username" value="<?= $model->username ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">密码设置</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="password">
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">门店配置</label>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-success" href="javascript:" data-toggle="modal" data-target="#store-goods-insert-modal" data-backdrop="static">门店列表</a>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">所选门店ID</label>
                </div>
                <div class="col-sm-6">
                    <label class="checkbox-label" style="margin-right: 0px;">
                        {{mch_list}}
                    </label>
                    <input class="form-control" name="mchlist" v-model="mch_list" hidden>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                    <input type="button" class="btn btn-default ml-4" name="Submit" onclick="javascript:history.back(-1);" value="返回">
                </div>
            </div>
        </form>

    </div>

    <!--门店列表选择-->
    <div>
        <div class="modal fade" id="store-goods-insert-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document" style="max-width: 1450px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">门店选择</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-control goodsnamekeyword" placeholder="输入门店昵称查找" v-model="keywoorlds" @input="handleInput">
                                <input class="form-control order-id" type="hidden">
                            </div>
                        </div>
                        <template v-if="goods_list.length>0">
                            <div style="max-width:1450px; max-height:400px;overflow: auto">
                                <table class="table table-bordered">
                                    <tr>
                                        <td>
                                            <label class="checkbox-label" style="margin-right: 0px;">
                                                <input type="checkbox" class="allCheckedInput" v-model="isAllChecked" v-on:click="chooseAll">
                                                <span class="label-icon"></span>
                                            </label>
                                        </td>
                                        <td>id</td>
                                        <td>门店昵称</td>
                                        <td>门店编号</td>
                                        <td>门店地址</td>
                                    </tr>
                                    <tr v-for="(item,index) in store_goods_list">
                                        <td><label class="checkbox-label" style="margin-right: 0px;">
                                                <input type="checkbox" @change="singleChecked" :value="item.id" v-model="checkedCode">
                                                <span class="label-icon"></span>
                                            </label></td>
                                        <td>{{item.id}}</td>
                                        <td>{{item.realname}}</td>
                                        <td>{{item.code}}</td>
                                        <td>{{item.address}}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="goods-error text-danger" hidden></div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-primary daoru" href="javascript:">选择确认</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            goods_list: [],
            store_goods_list: [],
            keywoorlds: '', // 关键字
            checkedCode: [],
            isAllChecked: false,
            mch_list: <?= $model->mch_json ?: '' ?>,
        },
        methods: {
            handleInput() {
                this.store_goods_list = [];
                $.ajax({
                    url: '<?=Yii::$app->urlManager->createUrl(['mch/mch/account/store-mch-list'])?>',
                    dataType: 'json',
                    type: 'get',
                    data: {
                        keyword: this.keywoorlds
                    },
                    success:  (res) => {
                        this.goods_list = res;
                        this.store_goods_list = res;
                    }
                });
            },
            // 用户单选
            singleChecked: function(){
                //判断每一个CheckBox是否选中，全选中让全选按钮选中
                if(this.store_goods_list.length == this.checkedCode.length){
                    this.isAllChecked = true;
                }else{  // 只要有一个checkbox不选中，让全选按钮不选中
                    this.isAllChecked = false;
                }
                console.log(this.checkedCode)
            },
            // 用户全选
            chooseAll: function(e){
                var that = this;
                if(that.checkedCode.length !== 0){
                    that.checkedCode = [];
                }
                if(that.isAllChecked){
                    that.store_goods_list.forEach(function(k){
                        that.checkedCode.push(k.id)
                    },that)
                }else{
                    that.checkedCode = [];
                }
                console.log(that.checkedCode)
            },
        }
    });
</script>

<script>
    $(document).on('click', '.daoru', function () {
        var a = $(this);
        $('.goods-error').prop('hidden', true);
        if (app.checkedCode == "") {
            $('.goods-error').prop('hidden', false).html('请先选择门店');
            return;
        }
        app.mch_list = app.checkedCode;
        console.log('门店---->' + app.mch_list)
        $('#store-goods-insert-modal').modal('hide');
    });
</script>
