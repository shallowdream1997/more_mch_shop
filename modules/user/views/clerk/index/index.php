<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/13
 * Time: 15:03
 */
use yii\widgets\LinkPager;

defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
$this->title = '店员列表';
$this->params['active_nav_group'] = 4;
$urlPlatform = Yii::$app->controller->route;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="float-left pt-2">
            <div class="dropdown float-right ml-2">
                <button class="btn btn-secondary dropdown-toggle" type="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php if ($_GET['platform'] === '1') :
                        ?>支付宝
                    <?php elseif ($_GET['platform'] === '0') :
                        ?>微信
                    <?php elseif ($_GET['platform'] == '') :
                        ?>全部用户
                    <?php else : ?>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu" style="min-width:8rem">
                    <a class="dropdown-item" href="<?= $urlManager->createUrl([$urlPlatform]) ?>">全部用户</a>
                    <a class="dropdown-item"
                       href="<?= $urlManager->createUrl([$urlPlatform, 'platform' => 1]) ?>">支付宝</a>
                    <a class="dropdown-item"
                       href="<?= $urlManager->createUrl([$urlPlatform, 'platform' => 0]) ?>">微信</a>
                </div>
            </div>
        </div>
        <div class="float-right mb-4">
            <form method="get">

                <?php $_s = ['keyword'] ?>
                <?php foreach ($_GET as $_gi => $_gv) :
                    if (in_array($_gi, $_s)) {
                        continue;
                    } ?>
                    <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                <?php endforeach; ?>

                <div class="input-group">
                    <input class="form-control"
                           placeholder="昵称"
                           name="keyword"
                           autocomplete="off"
                           value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                    <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                </div>
            </form>
        </div>
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th>ID</th>
                <th>头像</th>
                <th>昵称</th>
                <th>归属门店</th>
                <th>归属店员</th>
                <th>加入时间</th>
                <th>身份</th>
                <th>核销订单数</th>
                <th>核销总额</th>
                <th>核销卡券数</th>
            </tr>
            </thead>
            <?php foreach ($list as $u) : ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                        <img src="<?= $u['avatar_url'] ?>" style="width: 34px;height: 34px;margin: -.6rem 0;">
                    </td>
                    <td>
                        <?= $u['nickname']; ?>
                        <?php if (isset($u['platform']) && intval($u['platform']) === 0): ?>
                            <span class="badge badge-success">微信</span>
                        <?php elseif (isset($u['platform']) && intval($u['platform']) === 1): ?>
                            <span class="badge badge-primary">支付宝</span>
                        <?php else: ?>
                            <span class="badge badge-default">未知</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-primary"><?= $u['shop_name']; ?></span></td>
                    <td><span class="badge badge-primary"><?= $u['p_name']; ?></span></td>
                    <td><?= date('Y-m-d H:i:s', $u['addtime']) ?></td>
                    <?php if ($u['is_clerk'] == 1) : ?>
                        <td><span class="badge badge-success" style="font-size: 100%;">门店店员</span></td>
                    <?php else : ?>
                        <?php if ($u['parent_id'] == 0): ?>
                            <td><span class="badge badge-warning" style="font-size: 100%;">门店会员</span></td>
                        <?php else : ?>
                            <td><span class="badge badge-info" style="font-size: 100%;">店员发展用户</span></td>
                        <?php endif ?>
                    <?php endif ?>
                    <td><?= $u['order_count'] ?></td>
                    <td><?= $u['total_price']?></td>
                    <td><?= $u['card_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="text-center">
            <nav aria-label="Page navigation example">
                <?php echo LinkPager::widget([
                    'pagination' => $pagination,
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'firstPageLabel' => '首页',
                    'lastPageLabel' => '尾页',
                    'maxButtonCount' => 5,
                    'options' => [
                        'class' => 'pagination',
                    ],
                    'prevPageCssClass' => 'page-item',
                    'pageCssClass' => "page-item",
                    'nextPageCssClass' => 'page-item',
                    'firstPageCssClass' => 'page-item',
                    'lastPageCssClass' => 'page-item',
                    'linkOptions' => [
                        'class' => 'page-link',
                    ],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ])
                ?>
            </nav>
            <div class="text-muted">共<?= $row_count ?>条数据</div>
        </div>
    </div>
</div>