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
    <main id="main" class="site-main document-main" tabindex="-1">
        <article class="document">
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
            <?php if ($this->is('post')): $blueDocAdjacent = bluedocAdjacentDocuments($blueDocData, (int) $this->cid); ?>
                <?php if ($blueDocAdjacent['previous'] || $blueDocAdjacent['next']): ?>
                    <nav class="document-pagination" aria-label="<?php _e('同分类阅读导航'); ?>">
                        <?php foreach (['previous' => _t('上一篇'), 'next' => _t('下一篇')] as $direction => $label): $document = $blueDocAdjacent[$direction]; ?>
                            <?php if ($document): ?><a class="document-<?php echo $direction; ?>" data-adjacent-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>"><small><?php echo bluedocEscape($label); ?></small><strong><?php echo bluedocEscape($document['title']); ?></strong></a><?php else: ?><span aria-hidden="true"></span><?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    </main>
    <?php $this->need('partials/toc.php'); ?>
</div>
<?php $this->need('footer.php'); ?>
