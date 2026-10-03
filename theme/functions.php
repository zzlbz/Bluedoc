<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Typecho 原生主题配置入口。第一阶段不添加配置项。
 */
function themeConfig(\Typecho\Widget\Helper\Form $form): void
{
    // 后续可在此注册主题设置；保持首次启用时无需配置。
}

/**
 * 转义主题直接输出的纯文本与 HTML 属性，避免 PHP 8.2 的 null 弃用提示。
 * 正文、分类链接与评论 HTML 继续交由 Typecho 的原生输出方法处理。
 */
function bluedocEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
