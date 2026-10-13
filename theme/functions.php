<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
define('BLUEDOC_VERSION', '0.4.0');

/**
 * Typecho 原生主题配置入口。
 */
function themeConfig(\Typecho\Widget\Helper\Form $form): void
{
    foreach (['Title' => '首页标题', 'Description' => '首页描述'] as $key => $label) {
        $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
            'bluedocHome' . $key, null, '', _t($label),
            _t('可选，留空沿用 Typecho 网站标题或简介。内容以纯文本显示。')
        ));
    }
    $popular = new \Typecho\Widget\Helper\Form\Element\Text(
        'bluedocPopularCids', null, '', _t('精选文档文章 CID'),
        _t('最多 12 个正整数 CID，英文逗号分隔，按填写顺序显示。留空或没有可公开展示的文章时显示最新文章；不显示草稿、私密及未到发布时间的文章。')
    );
    $popular->addRule(function ($value) {
        $value = trim((string) $value);
        return $value === '' || (preg_match('/^[1-9][0-9]*(?:\s*,\s*[1-9][0-9]*)*$/D', $value)
            && count(explode(',', $value)) <= 12);
    }, _t('请填写最多 12 个正整数 CID，以英文逗号分隔。'));
    $form->addInput($popular);
    $data = bluedocDocumentData();
    $choices = [0 => _t('自动：展示一级分类')];
    foreach ($data['categories'] as $mid => $category) {
        $choices[$mid] = implode(' / ', array_column(bluedocCategoryPath($data, $mid), 'name'));
    }
    $parent = new \Typecho\Widget\Helper\Form\Element\Select(
        'bluedocQuickParentMid', $choices, '0', _t('快速导航分类范围'),
        _t('默认按后台排序展示前四个一级分类。选择父分类后展示其前四个直接子分类，每张卡片读取该分支最新三篇公开文档。父分类删除后恢复默认；没有子分类时显示空状态。')
    );
    $parent->addRule(fn ($value) => array_key_exists((string) $value, $choices), _t('请选择有效的分类范围。'));
    $form->addInput($parent);
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Select(
        'bluedocNavigationScope', ['all' => _t('完整文档树'), 'branch' => _t('文章与分类页仅显示所属分支')],
        'all', _t('左侧导航范围'), _t('首页和独立页面始终显示完整分类树。')
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Select(
        'bluedocReadingOrder', ['manual' => _t('文档序号优先'), 'oldest' => _t('创建时间从早到晚'), 'newest' => _t('创建时间从晚到早')],
        'manual', _t('文档阅读顺序'), _t('控制侧栏、分类文章目录和同分类上下篇。序号模式下，已填序号从小到大，同序号和未填序号按创建时间、CID 从小到大。最新推荐不受影响。')
    ));
    $start = new \Typecho\Widget\Helper\Form\Element\Text(
        'bluedocStartCid', null, '', _t('开始阅读文章 CID'), _t('可选，留空时选择后台第一个分类分支中按阅读顺序排列的文档；失效 CID 恢复自动选择。')
    );
    $start->addRule(fn ($value) => trim((string) $value) === '' || preg_match('/^[1-9][0-9]*$/D', trim((string) $value)), _t('CID 必须是正整数或留空。'));
    $form->addInput($start);
}

/** 原生文章编辑器自定义字段，不增加数据库列或依赖插件。 */
function themePostFields(\Typecho\Widget\Helper\Layout $layout): void
{
    $order = new \Typecho\Widget\Helper\Form\Element\Text(
        'bluedocOrder', null, '', _t('文档阅读序号'), _t('可选，填写 0–999999999 的整数，数值越小越靠前。未填写的文档排在已填写文档之后。')
    );
    $order->input->setAttribute('inputmode', 'numeric');
    $order->input->setAttribute('pattern', '[0-9]{1,9}');
    $order->input->setAttribute('title', _t('请填写 0–999999999 的整数或留空。非法存量值按未填写处理。'));
    $layout->addItem($order);
}

/**
 * 转义主题直接输出的纯文本与 HTML 属性，避免 PHP 8.2 的 null 弃用提示。
 * 正文与密码保护继续交由 Typecho 的原生输出方法处理。
 */
function bluedocEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

require_once __DIR__ . '/inc/navigation.php';
require_once __DIR__ . '/inc/home.php';
