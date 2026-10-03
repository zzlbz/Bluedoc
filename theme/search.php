<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>
<main id="main" class="site-main" tabindex="-1">
    <header class="section-header">
        <p class="eyebrow"><?php _e('搜索结果'); ?></p>
        <h1><?php echo bluedocEscape(sprintf(_t('搜索：%s'), $this->getArchiveTitle() ?? '')); ?></h1>
        <p class="section-description"><?php _e('选择下方文档查看详细内容。'); ?></p>
    </header>
    <?php $this->need('partials/document-list.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
