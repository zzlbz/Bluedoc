<?php
/**
 * BlueDoc：支持原生分类层级、文档树与阅读目录的现代文档中心主题。
 *
 * @package BlueDoc
 * @author BlueDoc
 * @version 0.2.0
 * @link https://typecho.org
 */
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$blueDocData = bluedocDocumentData();
$blueDocPopular = bluedocPopularDocuments($blueDocData, $this->options->bluedocPopularCids);
$this->need('header.php');
?>
<main id="main" class="site-main home-main" tabindex="-1">
    <section class="home-hero" aria-labelledby="hero-title">
        <p class="eyebrow"><?php _e('文档与帮助中心'); ?></p>
        <h1 id="hero-title"><?php echo bluedocEscape($this->options->title); ?></h1>
        <p class="hero-description"><?php echo bluedocEscape($this->options->description ?: _t('从入门到熟练，在这里找到你需要的指南。')); ?></p>
        <form class="hero-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
            <label class="sr-only" for="hero-search-input"><?php _e('搜索帮助文档'); ?></label>
            <input id="hero-search-input" name="s" type="search" placeholder="<?php _e('搜索教程、平台或常见问题…'); ?>" required>
            <button type="submit"><?php _e('搜索文档'); ?></button>
        </form>
    </section>

    <section class="home-section" aria-labelledby="quick-title">
        <div class="home-section-heading"><h2 id="quick-title"><?php _e('快速开始'); ?></h2><p><?php _e('选择你的设备，开始使用。'); ?></p></div>
        <div class="quick-grid">
            <?php foreach (['Windows' => 'Windows', 'Android' => 'Android', 'Ios' => 'iOS'] as $key => $platform):
                $category = bluedocQuickCategory($blueDocData, $platform, $this->options->{'bluedocQuick' . $key . 'Mid'});
                $tag = $category ? 'a' : 'div'; ?>
                <<?php echo $tag; ?> class="quick-card<?php echo $category ? '' : ' is-unavailable'; ?>"<?php if ($category): ?> href="<?php echo bluedocEscape($category['permalink']); ?>"<?php endif; ?>>
                    <span class="platform-mark" aria-hidden="true"><?php echo $key === 'Ios' ? 'i' : substr($key, 0, 1); ?></span>
                    <span><strong><?php echo $platform; ?></strong><small><?php echo $category ? _t('查看使用教程') : _t('教程尚未发布'); ?></small></span>
                    <?php if ($category): ?><span class="card-arrow" aria-hidden="true">→</span><?php endif; ?>
                </<?php echo $tag; ?>>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section" aria-labelledby="categories-title">
        <div class="home-section-heading"><h2 id="categories-title"><?php _e('文档分类'); ?></h2><p><?php _e('按主题浏览全部指南。'); ?></p></div>
        <div class="category-grid">
            <?php foreach ($blueDocData['children'][0] ?? [] as $mid): $category = $blueDocData['categories'][$mid]; ?>
                <a class="category-card" href="<?php echo bluedocEscape($category['permalink']); ?>">
                    <span class="category-card-top"><span class="category-symbol" aria-hidden="true">▤</span><span class="card-arrow" aria-hidden="true">→</span></span>
                    <h3><?php echo bluedocEscape($category['name']); ?></h3>
                    <p><?php echo bluedocEscape($category['description'] ?: _t('查看相关指南与教程。')); ?></p>
                    <small><?php echo bluedocEscape(sprintf(_t('%d 篇文档'), bluedocCategoryCount($blueDocData, $mid))); ?></small>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($blueDocData['children'][0])): ?><p class="empty-state"><?php _e('文档分类准备中。'); ?></p><?php endif; ?>
    </section>

    <section class="home-section" aria-labelledby="popular-title">
        <div class="home-section-heading"><h2 id="popular-title"><?php _e('热门教程'); ?></h2><p><?php _e('值得先读的使用指南。'); ?></p></div>
        <div class="popular-grid">
            <?php foreach ($blueDocPopular as $document): $path = bluedocPrimaryPath($blueDocData, $document['categoryIds']); ?>
                <a class="popular-card" data-popular-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>">
                    <span class="popular-category"><?php echo bluedocEscape($path ? $path[count($path) - 1]['name'] : _t('使用指南')); ?></span>
                    <h3><?php echo bluedocEscape($document['title']); ?></h3>
                    <span class="read-link"><?php _e('阅读指南'); ?> <span aria-hidden="true">→</span></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!$blueDocPopular): ?><p class="empty-state"><?php _e('教程准备中，发布文章后会自动展示。'); ?></p><?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
