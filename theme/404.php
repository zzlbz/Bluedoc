<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>
<main id="main" class="site-main" tabindex="-1">
    <div class="empty-state">
        <p class="eyebrow">404</p>
        <h1><?php _e('未找到文档'); ?></h1>
        <p><?php _e('该文档可能已移动或删除，可以尝试搜索或返回首页。'); ?></p>
        <a class="button-link" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回文档中心'); ?></a>
    </div>
</main>
<?php $this->need('footer.php'); ?>
