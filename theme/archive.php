<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>
<main id="main" class="site-main" tabindex="-1">
    <header class="section-header">
        <p class="eyebrow"><?php _e('文档归档'); ?></p>
        <h1><?php echo bluedocEscape($this->getArchiveTitle() ?: _t('全部文档')); ?></h1>
    </header>
    <?php $this->need('partials/document-list.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
