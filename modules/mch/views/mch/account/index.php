<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/12/28
 * Time: 15:53
 */
$this->title = '账户列表';
$urlManager = Yii::$app->urlManager;
$urlPlatform = Yii::$app->controller->route;
?>
<div class="alert alert-info rounded-0">
    账户PC端登录网址：
    <a href="<?= $adminUrl ?>" target="_blank"><?= $adminUrl ?></a>
</div>
<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
        <form class="form-inline d-inline-block float-right" style="margin: -.25rem 0" method="get">
            <input type="hidden" name="r" value="mch/mch/index/index">
            <div class="input-group">
                <a class="btn btn-primary mr-3" href="<?= Yii::$app->urlManager->createUrl(['mch/mch/account/add']) ?>">配置账户</a>
                <input class="form-control" name="keyword" value="<?= $get['keyword'] ?>" placeholder="店铺/用户/联系人">
                <span class="input-group-btn">
                    <button class="btn btn-secondary">搜索</button>
                </span>
            </div>
        </form>
    </div>
    <div class="panel-body">
        <?php if (!$list || count($list) == 0) : ?>
            <div class="p-5 text-center text-muted">暂无账户</div>
        <?php else : ?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>账户号码</th>
                    <th>账户所属门店集</th>
                    <th>开创时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <?php foreach ($list as $item) : ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= $item['username'] ?></td>
                        <td>
                            <?php foreach ($item['mc'] as $k) : ?>

                                <p><?= $k['realname'] ?></p>

                            <?php endforeach; ?>
                        </td>
                        <td><?= $item['update_time']?></td>
                        <td>
                            <a href="<?= $urlManager->createUrl(['mch/mch/account/edit', 'id' => $item['id']]) ?>">管理</a>
                            |
                            <a class="del" href="<?= $urlManager->createUrl(['mch/mch/account/mch-del', 'id' => $item['id']]) ?>">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?= \yii\widgets\LinkPager::widget(['pagination' => $pagination]) ?>
        <?php endif; ?>
    </div>
</div>
<script>
    $(document).on('click', '.del', function () {
        if (confirm("是否删除？")) {
            $.ajax({
                url: $(this).attr('href'),
                type: 'get',
                dataType: 'json',
                success: function (res) {
                    alert(res.msg);
                    if (res.code == 0) {
                        window.location.reload();
                    }
                }
            });
        }
        return false;
    });
</script>
