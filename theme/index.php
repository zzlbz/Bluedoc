<?php
/**
 * BlueDoc：自动读取原生分类、支持分类树与三栏阅读的通用文档中心主题。
 *
 * @package BlueDoc
 * @author BlueDoc
 * @version 0.3.2
 * @link https://github.com/zzlbz/Bluedoc
 */
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocQuick = bluedocQuickCards($blueDocData, $this->options->bluedocQuickParentMid);
$blueDocCategories = bluedocHomeCategories($blueDocData);
$blueDocPopular = bluedocPopularDocuments($blueDocData, $this->options->bluedocPopularCids);
$this->need('header.php');
?>
<main id="main" class="site-main home-main" tabindex="-1">
    <section class="home-hero" aria-labelledby="hero-title">
        <p class="eyebrow"><?php _e('文档与指南'); ?></p>
        <h1 id="hero-title"><?php echo bluedocEscape($this->options->bluedocHomeTitle ?: $this->options->title); ?></h1>
        <p class="hero-description"><?php echo bluedocEscape($this->options->bluedocHomeDescription ?: ($this->options->description ?: _t('查找文档、阅读教程，找到你需要的答案。'))); ?></p>
        <form class="hero-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
            <span class="search-icon"><?php bluedocIcon('search'); ?></span>
            <label class="sr-only" for="hero-search-input"><?php _e('搜索帮助文档'); ?></label>
            <input id="hero-search-input" name="s" type="search" placeholder="<?php _e('搜索文档、教程或问题…'); ?>" required>
            <button type="submit"><?php _e('搜索文档'); ?></button>
        </form>
        <p class="hero-hint"><?php _e('也可以按下方分类浏览文档。'); ?></p>
    </section>

    <section class="home-section" aria-labelledby="quick-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('快速开始'); ?></p><h2 id="quick-title"><?php _e('快速导航'); ?></h2></div><p><?php _e('按分类找到你需要的文档。'); ?></p></div>
        <div class="quick-grid">
            <?php foreach ($blueDocQuick as $card): $category = $card['category']; ?>
                <article class="quick-card" data-entry-mid="<?php echo $category['mid']; ?>">
                    <a class="quick-card-heading" href="<?php echo bluedocEscape($category['permalink']); ?>">
                        <span class="entry-mark"><?php bluedocIcon('book'); ?></span>
                        <strong><?php echo bluedocEscape($card['name']); ?></strong>
                        <span class="card-arrow" aria-hidden="true">↗</span>
                    </a>
                    <?php if ($card['description'] !== ''): ?><p class="quick-description"><?php echo bluedocEscape($card['description']); ?></p><?php endif; ?>
                    <?php if ($card['documents']): ?>
                        <ul class="quick-documents" aria-label="<?php echo bluedocEscape(sprintf(_t('%s 最新文档'), $card['name'])); ?>">
                            <?php foreach ($card['documents'] as $document): ?>
                                <li><a data-quick-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>"><?php echo bluedocEscape($document['title']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?><small><?php _e('暂无公开文档'); ?></small><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!$blueDocQuick): ?><p class="empty-state"><?php _e('当前分类范围暂无入口，请浏览下方文档分类。'); ?></p><?php endif; ?>
    </section>

    <section class="home-section" aria-labelledby="categories-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('文档分类'); ?></p><h2 id="categories-title"><?php _e('浏览全部分类'); ?></h2></div><p><?php _e('按主题浏览文档与教程。'); ?></p></div>
        <div class="category-grid">
            <?php foreach ($blueDocCategories as $card): $category = $card['category']; ?>
                <article class="category-card" data-category-mid="<?php echo $category['mid']; ?>">
                    <span class="category-symbol"><?php bluedocIcon('folder'); ?></span>
                    <h3><a href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape($card['name']); ?></a></h3>
                    <p><?php echo bluedocEscape($card['description']); ?></p>
                    <?php if (!empty($blueDocData['children'][$category['mid']])): ?>
                        <ul class="category-children">
                            <?php foreach ($blueDocData['children'][$category['mid']] as $mid): $child = $blueDocData['categories'][$mid]; ?>
                                <li><a href="<?php echo bluedocEscape($child['permalink']); ?>"><?php echo bluedocEscape($child['name']); ?><span aria-hidden="true">›</span></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <a class="category-card-footer" href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape(sprintf(_t('浏览 %d 篇文档'), bluedocCategoryCount($blueDocData, $category['mid']))); ?><span aria-hidden="true">→</span><span class="sr-only">：<?php echo bluedocEscape($category['name']); ?></span></a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!$blueDocCategories): ?><p class="empty-state"><?php _e('暂无文档分类。'); ?></p><?php endif; ?>
    </section>

    <section class="home-section" aria-labelledby="popular-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('推荐阅读'); ?></p><h2 id="popular-title"><?php _e('精选文档'); ?></h2></div><p><?php _e('从这些文档开始了解。'); ?></p></div>
        <div class="popular-grid">
            <?php foreach ($blueDocPopular as $document): $path = bluedocPrimaryPath($blueDocData, $document['categoryIds']); ?>
                <a class="popular-card" data-popular-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>">
                    <span class="popular-category"><?php echo bluedocEscape($path ? $path[count($path) - 1]['name'] : _t('文档')); ?></span>
                    <h3><?php echo bluedocEscape($document['title']); ?></h3>
                    <span class="read-link"><?php _e('阅读文档'); ?> <span aria-hidden="true">→</span></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!$blueDocPopular): ?><p class="empty-state"><?php _e('暂无公开文档。'); ?></p><?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
