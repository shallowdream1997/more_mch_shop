<?php
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

    .qrcode {
        max-width: 260px;
        border-radius: 0;
        border: 1px solid #eee;
        margin-bottom: 20px;
        padding: 1rem;
        background-color: #fff;
    }

    .desc {
        background: #eee;
        max-width: 100%;
        text-align: center;
        padding: 12px;
        border-radius: 999px;
        box-shadow: inset 1px 1px 3px 0px rgba(0, 0, 0, .2), 1px 1px 1px #fff;
    }

    .login-success {
        color: #1f9832;
        display: none;
    }

    .platform-item {
        text-decoration: none;
        text-align: center;
        padding: .5rem;
        margin: 0 1rem;
    }
</style>
<!--<div class="main">-->
<!--    <div class="header"></div>-->
<!--    <div class="auto-submit-form card">-->
<!--        <div class="card">-->
<!--            <div class="card-body">-->
<!--                <h1>商户平台登录</h1>-->
<!--                <input class="form-control mb-3 phone" name="phone" placeholder="请输入手机号">-->
<!--                <input class="form-control mb-3 password" name="password" placeholder="请输入密码" type="password">-->
<!--                <button class="btn btn-block btn-primary mb-3 login">登录</button>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

<div class="main-box" flex="dir:left main:center cross:center">
    <?php if ($_platform == ''): ?>
    <div class="main-content">
        <div class="title">商户账号登录</div>
        <input class="form-control mb-3 phone" name="phone" placeholder="请输入手机号">
        <input class="form-control mb-3 password" name="password" placeholder="请输入密码" type="password">
        <button class="btn btn-block btn-primary mb-3 login">登录</button>
        <div class="desc">
            <div class="login-tip">请使用商户账号登录</div>
        </div>
    </div>
    <?php else: ?>
        <div class="main-content">
            <div class="title">请选择您的用户类型</div>
            <div flex="dir:left main:center">
                <a class="platform-item"
                   href="<?= Yii::$app->request->baseUrl ?>/mch.php?store_id=<?= Yii::$app->request->get('store_id') ?>&_platform=wx">
                    <div>
                        <img style="width: 100px;height: 100px"
                             src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon64_appwx_logo.png">
                    </div>
                    <div>微信用户</div>
                </a>
                <?php if ($isAlipay == 1): ?>
                    <a class="platform-item"
                       href="<?= Yii::$app->request->baseUrl ?>/mch.php?store_id=<?= Yii::$app->request->get('store_id') ?>&_platform=my">
                        <div>
                            <img style="width: 100px;height: 100px"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/alipay.png">
                        </div>
                        <div>支付宝用户</div>
                    </a>
                <?php endif; ?>
                <a class="platform-item"
                   href="<?= Yii::$app->request->baseUrl ?>/mch.php?store_id=<?= Yii::$app->request->get('store_id') ?>&_platform=account">
                    <div>
                        <img style="width: 100px;height: 100px"
                             src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon64_appwx_logo.png">
                    </div>
                    <div>商户账号</div>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
    $(document).on('click', '.login', function () {
        var phone = $('.phone').val();
        var password = $('.password').val();
        $.ajax({
            url:'<?=Yii::$app->urlManager->createUrl('user/passport/check-account-login')?>',
            type: 'post',
            dataType: 'json',
            data: {
                'phone': phone,
                'password': password,
                _csrf: _csrf,
            },
            success:function (res) {
                if (res.code === 1) {
                    console.log(res.msg);
                    alert(res.msg);
                }else  {
                    location.href = "<?= \Yii::$app->urlManager->createUrl('user/mch/index/index') ?>";
                }
            }
        })
    });
</script>
