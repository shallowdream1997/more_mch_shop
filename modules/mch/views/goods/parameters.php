<?php
defined('YII_ENV') or exit('Access Denied');

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$imgurl = Yii::$app->request->baseUrl;
$this->title = '产品属性列表';
$this->params['active_nav_group'] = 2;
$urlStr = get_plugin_url();
$show = true;
if (in_array(get_plugin_type(), [0])) {
    $show = true;
} else {
    $show = false;
}
?>
<style>
    table {
        table-layout: fixed;
    }

    th {
        text-align: center;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td {
        text-align: center;
        line-height: 30px;
    }

    .ellipsis {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td.nowrap {
        white-space: nowrap;
        overflow: hidden;
    }
</style>

<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <?php
        $status = ['已启动', '已关闭'];
        ?>
        <div class="mb-3 clearfix">
            <div class="float-left">
                <a href="<?= $urlManager->createUrl([$urlStr . '/parameters-new-edit']) ?>" class="btn btn-primary"><i
                            class="iconfont icon-playlistadd"></i>添加产品模型</a>
                <div class="dropdown float-right ml-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        批量设置
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a href="javascript:void(0)" class="btn btn-secondary batch dropdown-item"
                           data-url="<?= $urlManager->createUrl([$urlStr . '/batch']) ?>" data-content="是否批量启动"
                           data-type="5">批量启动</a>
                        <a href="javascript:void(0)" class="btn btn-warning batch dropdown-item"
                           data-url="<?= $urlManager->createUrl([$urlStr . '/batch']) ?>" data-content="是否批量关闭"
                           data-type="6">批量关闭</a>
                        <a href="javascript:void(0)" class="btn btn-danger batch dropdown-item"
                           data-url="<?= $urlManager->createUrl([$urlStr . '/batch']) ?>" data-content="是否批量删除"
                           data-type="7">批量删除</a>
                    </div>
                </div>

                <div class="dropdown float-right ml-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php if ($_GET['status'] === '1') :
                            ?>已启动模型
                        <?php elseif ($_GET['status'] === '0') :
                            ?>已关闭模型
                        <?php elseif ($_GET['status'] == '') :
                            ?>全部模型
                        <?php else : ?>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu" style="min-width:8rem">
                        <a class="dropdown-item" href="<?= $urlManager->createUrl([$urlStr . '/parameters']) ?>">全部模型</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl([$urlStr . '/parameters', 'status' => 1]) ?>">已启动模型</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl([$urlStr . '/parameters', 'status' => 0]) ?>">已关闭模型</a>
                    </div>
                </div>
            </div>
            <div class="float-right">
                <form method="get">
                    <?php $_s = ['keyword', 'page', 'per-page'] ?>
                    <?php foreach ($_GET as $_gi => $_gv) :
                        if (in_array($_gi, $_s)) {
                            continue;
                        } ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>

                    <div class="input-group">
                        <input class="form-control"
                               placeholder="模型名称"
                               name="keyword"
                               autocomplete="off"
                               value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                        <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-bordered bg-white table-hover">
            <thead>
            <tr>
                <th style="text-align: center;text-overflow:clip;">
                    <label class="checkbox-label" style="margin-right: 0px;">
                        <input type="checkbox" class="goods-all">
                        <span class="label-icon"></span>
                    </label>
                </th>
                <th>
                    <span class="label-text">ID</span>
                </th>
                <th>模型名称</th>
                <th>模型说明</th>
                <th>是否启用</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
            </thead>
            <col style="width: 8%">
            <col style="width: 5%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 16.5%">
            <tbody>
            <?php foreach ($list as $index => $parameter) : ?>
                <tr>
                    <td class="nowrap" style="text-align: center;">
                        <label class="checkbox-label" style="margin-right: 0px;">
                            <input type="checkbox"
                                   class="goods-one"
                                   value="<?= $parameter['id'] ?>">
                            <span class="label-icon"></span>
                        </label>
                    </td>
                    <td data-toggle="tooltip"
                        data-placement="top" title="<?= $parameter['id'] ?>">
                        <span class="label-text"><?= $parameter['id'] ?></span>
                    </td>
                    <td class="text-left ellipsis" data-toggle="tooltip"
                        data-placement="top" title="<?= $parameter['model_name'] ?>">
                        <?= $parameter['model_name'] ?>
                    </td>
                    <td class="text-left ellipsis" data-toggle="tooltip"
                        data-placement="top" title="<?= $parameter['model_comment'] ?>">
                        <span class="label-text"><?= $parameter['model_comment'] ?></span>
                    </td>
                    <td class="nowrap">
                        <?php if ($parameter['is_use'] == 1) : ?>
                            <span class="badge badge-success">已启用</span>
                            |
                            <a href="javascript:" onclick="upDown(<?= $parameter['id'] ?>,'down');">关闭</a>
                        <?php else : ?>
                            <span class="badge badge-default">已关闭</span>
                            |
                            <a href="javascript:" onclick="upDown(<?= $parameter['id'] ?>,'up');">启动</a>
                        <?php endif ?>
                    </td>
                    <td class="nowrap">
                        <?= $parameter['sort'] ?>
                    </td>
                    <td class="nowrap">
                        <a class="btn btn-sm btn-primary"
                           href="<?= $urlManager->createUrl([$urlStr . '/parameters-new-edit', 'id' => $parameter['id']]) ?>">修改</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

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
        </div>
    </div>
</div>

<?= $this->render('/layouts/goods', [
    'goodsType' => 'STORE',
    'goodsList' => $goodsList
]) ?>


<script>
    $(document).on('click', '.del', function () {
        if (layer.confirm("是否删除？")) {
            $.ajax({
                url: $(this).attr('href'),
                type: 'get',
                dataType: 'json',
                success: function (res) {
                    if (res.code == 0) {
                        window.location.reload();
                    }else{
                        layer.alert(res.msg, {
                            closeBtn: 0
                        }, function () {
                            window.location.reload();
                        });
                    }
                }
            });
        }
        return false;
    });

    function upDown(id, type) {
        var text = '';
        if (type == 'up') {
            text = "启动";
        } else if (type == 'down') {
            text = "关闭";
        }
        var url = "<?= $urlManager->createUrl([$urlStr . '/attrbute-up-down']) ?>";
        layer.confirm("是否" + text + "？", {
            btn: [text, '取消'] //按钮
        }, function () {
            layer.msg('加载中', {
                icon: 16
                , shade: 0.01
            });
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: {id: id, type: type},
                success: function (res) {
                    if (res.code == 0) {
                        window.location.reload();
                    }
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            closeBtn: 0
                        }, function () {
                            window.location.reload();
                        });
                    }
                }
            });
        });
        return false;
    }

    $(document).on('click', '.goods-all', function () {
        var checked = $(this).prop('checked');
        $('.goods-one').prop('checked', checked);
        if (checked) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.goods-one', function () {
        var checked = $(this).prop('checked');
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        var is_use = false;//只要有一个选中，批量按妞就可以使用
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_use = true;
            } else {
                is_all = false;
            }
        });
        if (is_all) {
            $('.goods-all').prop('checked', true);
        } else {
            $('.goods-all').prop('checked', false);
        }
        if (is_use) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.batch', function () {
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_all = false;
            }
        });
        if (is_all) {
            $.myAlert({
                content: "请先勾选商品模型"
            });
        }
    });

    $(document).on('click', '.is_use', function () {
        var a = $(this);
        var goods_group = [];
        var all = $('.goods-one');
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                var goods = {};
                goods.id = $(all[i]).val();
                goods_group.push(goods);
            }
        });
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    data: {
                        goods_group: goods_group,
                        type: a.data('type'),
                    },
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    window.location.reload();
                                }
                            });
                        } else {
                            $.myAlert({
                                content: res.msg
                            });
                        }
                    },
                    complete: function () {
                        $.myLoadingHide();
                    }
                });
            }
        })
    });
</script>