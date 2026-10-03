<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocActive = bluedocActiveBranches($blueDocData, $this->is('post') ? array_column($this->categories, 'mid') : []);
?>
<aside id="bluedoc-sidebar" class="document-sidebar" aria-label="<?php _e('文档导航'); ?>">
    <div class="sidebar-heading"><h2><?php _e('文档导航'); ?></h2><button class="drawer-close" type="button" aria-label="<?php _e('关闭文档导航'); ?>" hidden>×</button></div>
    <nav class="document-tree" aria-label="<?php _e('文档分类与文章'); ?>">
        <a class="tree-home" href="<?php $this->options->siteUrl(); ?>"><?php _e('文档首页'); ?></a>
        <?php bluedocRenderTree($blueDocData, 0, $blueDocActive, (int) $this->cid); ?>
        <?php if (empty($blueDocData['categories'])): ?><p class="tree-empty"><?php _e('暂无文档分类'); ?></p><?php endif; ?>
    </nav>
</aside>
