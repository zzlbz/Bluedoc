<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocTreeCategory = $this->is('category') ? bluedocCurrentCategory($blueDocData, $this->getArchiveSlug()) : null;
$blueDocTreeCategoryIds = $blueDocTreeCategory ? [$blueDocTreeCategory['mid']] : ($this->is('post') ? array_column($this->categories, 'mid') : []);
$blueDocActive = bluedocActiveBranches($blueDocData, $blueDocTreeCategoryIds);
$blueDocTreeData = $blueDocData;
if ($this->options->bluedocNavigationScope === 'branch' && ($this->is('post') || $blueDocTreeCategory)) {
    $blueDocTreeData['children'][0] = bluedocNavigationRoots($blueDocData, $blueDocTreeCategoryIds);
}
?>
<aside id="bluedoc-sidebar" class="document-sidebar" aria-label="<?php _e('文档导航'); ?>">
    <a class="site-brand" href="<?php $this->options->siteUrl(); ?>"><span class="brand-mark" aria-hidden="true">B</span><span><?php echo bluedocEscape($this->options->title); ?></span></a>
    <form class="site-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search" aria-label="<?php _e('全站文档搜索'); ?>">
        <label class="sr-only" for="bluedoc-search"><?php _e('搜索文档'); ?></label>
        <input id="bluedoc-search" name="s" type="search" placeholder="<?php _e('搜索文档…'); ?>" value="<?php echo $this->is('search') ? bluedocEscape($this->getArchiveTitle()) : ''; ?>">
        <button type="submit" aria-label="<?php _e('搜索'); ?>"><?php bluedocIcon('search'); ?></button>
    </form>
    <div class="sidebar-heading"><h2><?php _e('文档导航'); ?></h2><button class="drawer-close" type="button" aria-label="<?php _e('关闭文档导航'); ?>" hidden>×</button></div>
    <nav class="document-tree" data-site-root="<?php $this->options->siteUrl(); ?>" aria-label="<?php _e('文档分类与文章'); ?>">
        <a class="tree-home" href="<?php $this->options->siteUrl(); ?>"<?php if ($this->is('index')): ?> aria-current="page"<?php endif; ?>><?php _e('文档首页'); ?></a>
        <?php bluedocRenderTree($blueDocTreeData, 0, $blueDocActive, $this->is('post') ? (int) $this->cid : 0, (int) ($blueDocTreeCategory['mid'] ?? 0)); ?>
        <?php if (empty($blueDocTreeData['children'][0])): ?><p class="tree-empty"><?php _e('当前范围暂无文档分类'); ?></p><?php endif; ?>
        <a class="tree-home tree-all" href="<?php $this->options->siteUrl(); ?>#categories-title"><?php _e('浏览全部分类'); ?></a>
    </nav>
    <nav class="sidebar-pages" aria-label="<?php _e('独立页面'); ?>">
        <?php \Widget\Contents\Page\Rows::alloc()->to($blueDocPages); ?>
        <?php while ($blueDocPages->next()): ?><a href="<?php $blueDocPages->permalink(); ?>"<?php if ($this->is('page', $blueDocPages->slug)): ?> aria-current="page"<?php endif; ?>><?php echo bluedocEscape($blueDocPages->title); ?></a><?php endwhile; ?>
    </nav>
</aside>
