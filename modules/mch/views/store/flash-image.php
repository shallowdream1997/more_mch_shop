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
 * @Date 2021-01-28 17:45 星期四
 * @Version 1.0
 * @Description ${TODO}
 */
/**
 * The PHP File flash-image.php Is Created By Idea
 * @User 123 云深知梦
 * @Date 2021/1/28
 * @Time 17:45
 */

defined('YII_ENV') or exit('Access Denied');

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = '商城图片素材管理';
?>

<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body">
        <?php foreach ($list as $index => $value) : ?>
            <div class="card mb-3 upload-preview-img" style="display: inline-block;">
                <div class="card-img-top" data-responsive="11:9"
                     style="background-image: url(<?= $value['file_url'] ?>);background-size: cover;background-position: center"></div>
                <div class="card-body p-3">
                    <p>图片格式：<?= $value['extension'] ?></p>
                    <div style="white-space: nowrap;overflow: hidden;word-break: break-all;text-overflow: ellipsis;">图片大小：<?= $value['size'] ?> KB</div>
                </div>

                <div class="card-footer text-muted">
                    <a class="btn btn-sm btn-danger del"
                       href="<?= $urlManager->createUrl(['mch/store/flash-del', 'id' => $value['id']]) ?>">删除</a>
                </div>
            </div>
        <?php endforeach; ?>
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
    </div>
</div>

<script>
    $(document).on('click', '.del', function () {
        var a = $(this);
        $.confirm({
            content: '确认删除？',
            confirm: function () {
                $.loading();
                $.ajax({
                    url: a.attr('href'),
                    type: 'get',
                    dataType: 'json',
                    success: function (res) {
                        $.loadingHide();
                        $.alert({
                            content: res.msg,
                            confirm: function () {
                                if (res.code == 0) {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        });
        return false;
    });
</script>
