<?php
/**
 * BlueDoc：面向产品文档与帮助中心的轻量主题基础框架。
 *
 * @package BlueDoc
 * @author BlueDoc
 * @version 0.1.0
 * @link https://typecho.org
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>
<main id="main" class="site-main" tabindex="-1">
    <header class="section-header">
        <p class="eyebrow"><?php _e('帮助与指南'); ?></p>
        <h1><?php _e('文档中心'); ?></h1>
        <?php if ($this->options->description): ?>
            <p class="section-description"><?php echo bluedocEscape($this->options->description); ?></p>
        <?php endif; ?>
    </header>
    <?php $this->need('partials/document-list.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
