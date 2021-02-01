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
 * @ClassName login
 * @Author CloudDre(1783554564@qq.com)
 * @Date 2021-01-29 11:42 星期五
 * @Version 1.0
 * @Description
 */

$this->title = '商户登录';
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
        <div class="title">商户账号登录</div>
        <input name="_csrf"
               type="hidden"
               id="_csrf"
               value="<?= Yii::$app->request->csrfToken ?>">
        <input class="form-control mb-3 phone" name="phone" placeholder="请输入手机号">
        <input class="form-control mb-3 password" name="password" placeholder="请输入密码" type="password">
        <button class="btn btn-block btn-primary mb-3 login">登录</button>
        <div class="desc">
            <div class="login-tip">请使用商户账号登录</div>
        </div>
    </div>
</div>
<script>
    var stop = false;
    function checkLogin() {
        if (stop)
            return;
        $.ajax({
            url: '<?=Yii::$app->urlManager->createUrl(['shop/passport/check-login',])?>',
            data: {
                token: token,
            },
            dataType: 'json',
            success: function (res) {
                $('.login-tip').text(res.msg);
                if (res.code == 1) {
                    stop = true;
                }
                if (res.code == 0) {
                    location.href = '<?=Yii::$app->urlManager->createUrl(['user'])?>';
                }
                if (res.code == -1) {
                    checkLogin();
                }
            },
            error: function () {
                stop = true;
            }
        });
    }
</script>
