<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocBreadcrumbData = bluedocDocumentData();
$blueDocBreadcrumbCategory = $this->is('category') ? bluedocCurrentCategory($blueDocBreadcrumbData, $this->getArchiveSlug()) : null;
$blueDocPath = bluedocPrimaryPath($blueDocBreadcrumbData, $blueDocBreadcrumbCategory ? [$blueDocBreadcrumbCategory['mid']] : ($this->is('post') ? array_column($this->categories, 'mid') : []));
if ($blueDocBreadcrumbCategory) {
    array_pop($blueDocPath); // 当前分类只输出一次。
}
?>
<nav class="breadcrumbs" aria-label="<?php _e('面包屑'); ?>">
    <ol>
        <li><a href="<?php $this->options->siteUrl(); ?>"><?php _e('首页'); ?></a></li>
        <?php foreach ($blueDocPath as $category): ?>
            <li><a href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape($category['name']); ?></a></li>
        <?php endforeach; ?>
        <li aria-current="page"><?php echo bluedocEscape($blueDocBreadcrumbCategory ? $blueDocBreadcrumbCategory['name'] : $this->title); ?></li>
    </ol>
</nav>
