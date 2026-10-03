<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocCategoryIds = $this->is('post') ? array_column($this->categories, 'mid') : [];
$blueDocUpdated = new \Typecho\Date((int) ($this->modified ?: $this->created));
$this->need('header.php');
?>
<div class="document-shell">
    <?php $this->need('partials/document-tree.php'); ?>
    <main id="main" class="site-main document-main" tabindex="-1">
        <button class="drawer-toggle" type="button" aria-controls="bluedoc-sidebar" aria-expanded="false" hidden><span aria-hidden="true">☰</span> <?php _e('浏览文档'); ?></button>
        <article class="document">
            <?php $this->need('partials/breadcrumbs.php'); ?>
            <header class="section-header">
                <h1><?php echo bluedocEscape($this->title); ?></h1>
                <div class="document-meta">
                    <?php foreach ($blueDocCategoryIds as $mid): if (!isset($blueDocData['categories'][$mid])) { continue; } $category = $blueDocData['categories'][$mid]; ?>
                        <a class="category-chip" href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape($category['name']); ?></a>
                    <?php endforeach; ?>
                    <span><?php _e('最后更新：'); ?><time datetime="<?php echo bluedocEscape($blueDocUpdated->format('c')); ?>"><?php echo bluedocEscape($blueDocUpdated->format('Y-m-d H:i')); ?></time></span>
                </div>
            </header>
            <div class="document-content" data-document-content><?php $this->content(); ?></div>
        </article>
        <?php $this->need('comments.php'); ?>
    </main>
    <?php $this->need('partials/toc.php'); ?>
</div>
<button class="drawer-backdrop" type="button" tabindex="-1" aria-label="<?php _e('关闭文档导航'); ?>" hidden></button>
<?php $this->need('footer.php'); ?>
