<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocTreeCategory = $this->is('category') ? bluedocCurrentCategory($blueDocData, $this->getArchiveSlug()) : null;
$blueDocTreeCategoryIds = $blueDocTreeCategory ? [$blueDocTreeCategory['mid']] : ($this->is('post') ? array_column($this->categories, 'mid') : []);
$blueDocActive = bluedocActiveBranches($blueDocData, $blueDocTreeCategoryIds);
$blueDocTreeData = $blueDocData;
if ($this->is('post') || $blueDocTreeCategory) {
    $blueDocTreeData['children'][0] = bluedocNavigationRoots($blueDocData, $blueDocTreeCategoryIds);
}
?>
<aside id="bluedoc-sidebar" class="document-sidebar" aria-label="<?php _e('文档导航'); ?>">
    <div class="sidebar-heading"><h2><?php _e('文档导航'); ?></h2><button class="drawer-close" type="button" aria-label="<?php _e('关闭文档导航'); ?>" hidden>×</button></div>
    <nav class="document-tree" aria-label="<?php _e('文档分类与文章'); ?>">
        <a class="tree-home" href="<?php $this->options->siteUrl(); ?>"><?php _e('文档首页'); ?></a>
        <?php bluedocRenderTree($blueDocTreeData, 0, $blueDocActive, $this->is('post') ? (int) $this->cid : 0, (int) ($blueDocTreeCategory['mid'] ?? 0)); ?>
        <?php if (empty($blueDocTreeData['children'][0])): ?><p class="tree-empty"><?php _e('当前内容暂无所属分类'); ?></p><?php endif; ?>
        <a class="tree-home tree-all" href="<?php $this->options->siteUrl(); ?>#categories-title"><?php _e('浏览全部分类'); ?></a>
    </nav>
</aside>
