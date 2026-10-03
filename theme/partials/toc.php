<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>
<aside class="document-toc" aria-label="<?php _e('本页目录'); ?>">
    <h2><?php _e('本页目录'); ?></h2>
    <nav data-document-toc aria-label="<?php _e('文章章节'); ?>" data-untitled="<?php _e('未命名章节'); ?>" hidden></nav>
    <p class="toc-empty" data-toc-empty hidden><?php _e('本文暂无章节目录。'); ?></p>
    <noscript><p class="toc-empty"><?php _e('启用 JavaScript 后可显示章节目录。'); ?></p></noscript>
</aside>
