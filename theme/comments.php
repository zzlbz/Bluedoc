<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
// 文档中心不渲染评论、评论数量或表单；保留兼容模板入口。
// 已有评论和文章评论权限仍由 Typecho 后台管理，主题不修改这些数据。
