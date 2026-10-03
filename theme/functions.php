<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Typecho 原生主题配置入口。
 */
function themeConfig(\Typecho\Widget\Helper\Form $form): void
{
    $popular = new \Typecho\Widget\Helper\Form\Element\Text(
        'bluedocPopularCids', null, '', _t('热门教程文章 CID'),
        _t('最多 12 个正整数 CID，英文逗号分隔，按填写顺序显示。留空或没有可公开展示的文章时显示最新文章；不显示草稿、私密及未到发布时间的文章。')
    );
    $popular->addRule(function ($value) {
        $value = trim((string) $value);
        return $value === '' || (preg_match('/^[1-9][0-9]*(?:\s*,\s*[1-9][0-9]*)*$/D', $value)
            && count(explode(',', $value)) <= 12);
    }, _t('请填写最多 12 个正整数 CID，以英文逗号分隔。'));
    $form->addInput($popular);
    foreach (['Windows' => 'Windows', 'Android' => 'Android', 'Ios' => 'iOS'] as $key => $label) {
        $input = new \Typecho\Widget\Helper\Form\Element\Text(
            'bluedocQuick' . $key . 'Mid', null, '', sprintf(_t('%s 快速开始分类 MID'), $label),
            _t('可选：填写分类 MID。留空或分类不存在时自动匹配对应平台的分类名称或缩略名；尚未创建分类时显示未发布提示。')
        );
        $input->addRule(function ($value) {
            return trim((string) $value) === '' || preg_match('/^[1-9][0-9]*$/D', trim((string) $value));
        }, _t('分类 MID 必须是正整数或留空。'));
        $form->addInput($input);
    }
}

/**
 * 转义主题直接输出的纯文本与 HTML 属性，避免 PHP 8.2 的 null 弃用提示。
 * 正文、分类链接与评论 HTML 继续交由 Typecho 的原生输出方法处理。
 */
function bluedocEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

require_once __DIR__ . '/inc/navigation.php';
