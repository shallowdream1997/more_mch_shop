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
 * @Date 2021-01-29 12:24 星期五
 * @Version 1.0
 * @Description ${TODO}
 */
/**
 * The PHP File shop-select.php Is Created By Idea
 * @User 123 云深知梦
 * @Date 2021/1/29
 * @Time 12:24
 */
$this->title = '商户选择';
$urlManager = Yii::$app->urlManager;
$passport_bg = Yii::$app->request->baseUrl . '/statics/mch/images/passport_bg.png';
?>
<style>
    body {
        background: #f7f6f1;
    }

    .main-box {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }

    .main-content {
        max-width: 260px;
    }

    .title {
        text-align: center;
        padding: 16px;
        font-size: 1.35rem;
    }

    .desc {
        background: #eee;
        max-width: 100%;
        text-align: center;
        padding: 12px;
        border-radius: 999px;
        box-shadow: inset 1px 1px 3px 0px rgba(0, 0, 0, .2), 1px 1px 1px #fff;
    }

</style>
<div class="main-box" flex="dir:left main:center cross:center">
    <div class="main-content">
        <div class="title">请选择您的商户平台</div>
        <div flex="dir:left main:center">
            <?php foreach ($list as $item) : ?>
            <a class="platform-item" href="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon64_appwx_logo.png">
                <?= $item['realname'] ?>
                <div>
                    <img style="width: 100px;height: 100px"
                         src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon64_appwx_logo.png">
                </div>
                <div><?= $item['realname'] ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

