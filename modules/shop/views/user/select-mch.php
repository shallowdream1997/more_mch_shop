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
 * @Date 2021-02-02 17:12 星期二
 * @Version 1.0
 * @Description ${TODO}
 */
/**
 * The PHP File select-mch.php Is Created By Idea
 * @User 123 云深知梦
 * @Date 2021/2/2
 * @Time 17:12
 */

$this->title = '门店选择列表';
$urlManager = Yii::$app->urlManager;
$urlPlatform = Yii::$app->controller->route;
?>
<div class="container" id="app">
    <div class="alert alert-info rounded-0" style="margin-top: 25%">
        <p>账户门店选择登陆页面：</p>
        <p class="panel-danger float-right">
            <div class="btn btn-success btn-sm" @click="loginout()">注销</div>
        </p>
        <div class="panel-body">
            <?php if (!$mc || count($mc) == 0) : ?>
                <div class="p-5 text-center text-muted">暂无门店</div>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>门店号码</th>
                        <th>门店昵称</th>
                        <th>门店地址</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <?php foreach ($mc as $item) : ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= $item['tel'] ?></td>
                            <td><?= $item['realname'] ?></td>
                            <td><?= $item['address']?></td>
                            <td>
                                <p class="btn btn-success" @click="gotomch(<?= $item['id'] ?>)">进入</p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?= \yii\widgets\LinkPager::widget(['pagination' => $pagination]) ?>
            <?php endif; ?>
        </div>
    </div>

</div>
<script>
    var app = new Vue({
        el: "#app",
        data() {
            return {
                mchId: 0
            };
        },
        methods: {
            gotomch(e){
                this.mchId = e;
                $.ajax({
                    url: "<?= $urlManager->createUrl(['shop/shop/index']) ?>",
                    type: "post",
                    data: {
                        _csrf: _csrf,
                        Mchid: this.mchId,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            location.href = '<?=Yii::$app->urlManager->createUrl(['shop/account/index'])?>';
                        }
                    }
                });
            },
            loginout(){
                $.ajax({
                    url: "<?= $urlManager->createUrl(['shop/shop/loginout'])?>",
                    type: "get",
                    data: {},
                    success: function (res) {
                        if (res.code == 0){
                            location.href = "<?= $urlManager->createUrl(['shop/passport/login'])?>"
                        }
                    }
                });
            }
        }
    });
</script>
