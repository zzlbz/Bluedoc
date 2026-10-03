<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocPath = bluedocPrimaryPath(bluedocDocumentData(), $this->is('post') ? array_column($this->categories, 'mid') : []);
?>
<nav class="breadcrumbs" aria-label="<?php _e('面包屑'); ?>">
    <ol>
        <li><a href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a></li>
        <?php foreach ($blueDocPath as $category): ?>
            <li><a href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape($category['name']); ?></a></li>
        <?php endforeach; ?>
        <li aria-current="page"><?php echo bluedocEscape($this->title); ?></li>
    </ol>
</nav>
