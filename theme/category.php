<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocCategory = bluedocCurrentCategory($blueDocData, $this->getArchiveSlug());
if (!$blueDocCategory) {
    $this->need('archive.php');
    return;
}
$blueDocCategoryDocuments = bluedocCategoryDocuments($blueDocData, $blueDocCategory['mid']);
$blueDocParentCategory = $blueDocData['categories'][$blueDocCategory['parent']] ?? null;
$this->need('header.php');
?>
<div class="document-shell category-shell">
    <?php $this->need('partials/document-tree.php'); ?>
    <main id="main" class="site-main document-main" tabindex="-1">
        <button class="drawer-toggle" type="button" aria-controls="bluedoc-sidebar" aria-expanded="false" hidden><span aria-hidden="true">☰</span> <?php _e('浏览文档'); ?></button>
        <section class="category-overview" aria-labelledby="category-title">
            <?php $this->need('partials/breadcrumbs.php'); ?>
            <header class="section-header">
                <p class="eyebrow"><?php _e('浏览文档'); ?></p>
                <h1 id="category-title"><?php echo bluedocEscape($blueDocCategory['name']); ?></h1>
                <p class="section-description"><?php echo bluedocEscape($blueDocCategory['description'] ?: _t('选择下方分类或文档，继续阅读。')); ?></p>
                <p class="category-count"><?php echo bluedocEscape(sprintf(_t('共 %d 篇文档，包含子分类'), bluedocCategoryCount($blueDocData, $blueDocCategory['mid']))); ?></p>
                <?php if ($blueDocParentCategory): ?><a class="back-link" href="<?php echo bluedocEscape($blueDocParentCategory['permalink']); ?>"><?php echo bluedocEscape(sprintf(_t('返回父分类：%s'), $blueDocParentCategory['name'])); ?></a><?php endif; ?>
            </header>
            <?php if (!empty($blueDocData['children'][$blueDocCategory['mid']])): ?>
                <section aria-labelledby="subcategories-title">
                    <h2 id="subcategories-title"><?php _e('选择分类'); ?></h2>
                    <div class="subcategory-grid">
                        <?php foreach ($blueDocData['children'][$blueDocCategory['mid']] as $mid): $child = $blueDocData['categories'][$mid]; ?>
                            <a class="subcategory-card" href="<?php echo bluedocEscape($child['permalink']); ?>">
                                <strong><?php echo bluedocEscape($child['name']); ?></strong>
                                <small><?php echo bluedocEscape(sprintf(_t('%d 篇文档'), bluedocCategoryCount($blueDocData, $mid))); ?> <span aria-hidden="true">→</span></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            <?php if ($blueDocCategoryDocuments): ?>
                <section class="category-documents" aria-labelledby="category-documents-title">
                    <h2 id="category-documents-title"><?php _e('全部文档'); ?></h2>
                    <ul class="guide-list">
                        <?php foreach ($blueDocCategoryDocuments as $document): $path = bluedocPrimaryPath($blueDocData, $document['categoryIds']); ?>
                            <li><a href="<?php echo bluedocEscape($document['permalink']); ?>" data-guide-cid="<?php echo $document['cid']; ?>"><span><?php echo bluedocEscape($document['title']); ?><?php if ($path): ?><small class="guide-category"><?php echo bluedocEscape($path[count($path) - 1]['name']); ?></small><?php endif; ?></span><span aria-hidden="true">→</span></a></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php elseif (empty($blueDocData['children'][$blueDocCategory['mid']])): ?>
                <p class="empty-state"><?php _e('本分类的文档正在准备中，请选择其他分类。'); ?></p>
            <?php endif; ?>
        </section>
    </main>
</div>
<button class="drawer-backdrop" type="button" tabindex="-1" aria-label="<?php _e('关闭文档导航'); ?>" hidden></button>
<?php $this->need('footer.php'); ?>
