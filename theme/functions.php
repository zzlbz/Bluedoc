<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
define('BLUEDOC_VERSION', '0.3.2');

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
