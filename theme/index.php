<?php
/**
 * BlueDoc：以设备入口、原生分类树和三栏阅读组织内容的独立文档中心主题。
 *
 * @package BlueDoc
 * @author BlueDoc
 * @version 0.3.1
 * @link https://github.com/zzlbz/Bluedoc
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
        <p class="eyebrow"><?php _e('帮助与指南'); ?></p>
        <h1 id="hero-title"><?php echo bluedocEscape($this->options->bluedocHomeTitle ?: $this->options->title); ?></h1>
        <p class="hero-description"><?php echo bluedocEscape($this->options->bluedocHomeDescription ?: ($this->options->description ?: _t('从安装到配置，找到适合你的使用指南。'))); ?></p>
        <form class="hero-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
            <span class="search-icon"><?php bluedocIcon('search'); ?></span>
            <label class="sr-only" for="hero-search-input"><?php _e('搜索帮助文档'); ?></label>
            <input id="hero-search-input" name="s" type="search" placeholder="<?php _e('输入问题、客户端或教程名称…'); ?>" required>
            <button type="submit"><?php _e('搜索文档'); ?></button>
        </form>
        <p class="hero-hint"><?php _e('也可以选择设备，或按下方分类浏览。'); ?></p>
    </section>

    <section class="home-section" aria-labelledby="quick-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('快速开始'); ?></p><h2 id="quick-title"><?php _e('选择你的设备'); ?></h2></div><p><?php _e('找到对应平台的安装与配置教程。'); ?></p></div>
        <div class="quick-grid">
            <?php foreach (bluedocPlatforms() as $key => $platform):
                $card = bluedocDeviceCard($blueDocData, $platform['name'], $this->options->{'bluedocQuick' . $key . 'Mid'});
                $category = $card['category']; $tag = $category ? 'a' : 'div'; ?>
                <article class="quick-card<?php echo $category ? '' : ' is-unavailable'; ?>" data-platform="<?php echo $platform['name']; ?>"<?php if ($category): ?> data-platform-mid="<?php echo $category['mid']; ?>"<?php endif; ?>>
                    <<?php echo $tag; ?> class="quick-card-heading"<?php if ($category): ?> href="<?php echo bluedocEscape($category['permalink']); ?>"<?php endif; ?>>
                        <span class="platform-mark"><?php bluedocIcon($platform['icon']); ?></span>
                        <strong><?php echo bluedocEscape($card['name']); ?></strong>
                        <?php if ($category): ?><span class="card-arrow" aria-hidden="true">↗</span><?php endif; ?>
                    </<?php echo $tag; ?>>
                    <?php if ($card['description'] !== ''): ?><p class="quick-description"><?php echo bluedocEscape($card['description']); ?></p><?php endif; ?>
                    <?php if ($card['documents']): ?>
                        <ul class="quick-documents" aria-label="<?php echo bluedocEscape(sprintf(_t('%s 最新教程'), $card['name'])); ?>">
                            <?php foreach ($card['documents'] as $document): ?>
                                <li><a data-device-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>"><?php echo bluedocEscape($document['title']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?><small><?php _e('教程准备中'); ?></small><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section" aria-labelledby="categories-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('文档分类'); ?></p><h2 id="categories-title"><?php _e('你想了解什么？'); ?></h2></div><p><?php _e('从入门、配置到解决问题，按主题查找。'); ?></p></div>
        <div class="category-grid">
            <?php foreach (bluedocHomeCategories($blueDocData) as $card): $category = $card['category']; ?>
                <article class="category-card<?php echo $category ? '' : ' is-unavailable'; ?>">
                    <span class="category-symbol"><?php bluedocIcon($card['icon']); ?></span>
                    <h3><?php if ($category): ?><a href="<?php echo bluedocEscape($category['permalink']); ?>"><?php endif; ?><?php echo bluedocEscape($card['name']); ?><?php if ($category): ?></a><?php endif; ?></h3>
                    <p><?php echo bluedocEscape(_t($card['description'])); ?></p>
                    <?php if ($category): ?>
                        <?php if (!empty($blueDocData['children'][$category['mid']])): ?>
                            <ul class="category-children">
                                <?php foreach ($blueDocData['children'][$category['mid']] as $mid): $child = $blueDocData['categories'][$mid]; ?>
                                    <li><a href="<?php echo bluedocEscape($child['permalink']); ?>"><?php echo bluedocEscape($child['name']); ?><span aria-hidden="true">›</span></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a class="category-card-footer" href="<?php echo bluedocEscape($category['permalink']); ?>"><?php echo bluedocEscape(sprintf(_t('浏览 %d 篇文档'), bluedocCategoryCount($blueDocData, $category['mid']))); ?><span aria-hidden="true">→</span><span class="sr-only">：<?php echo bluedocEscape($category['name']); ?></span></a>
                    <?php else: ?><small><?php _e('文档准备中'); ?></small><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section" aria-labelledby="popular-title">
        <div class="home-section-heading"><div><p class="eyebrow"><?php _e('推荐阅读'); ?></p><h2 id="popular-title"><?php _e('热门教程'); ?></h2></div><p><?php _e('从这些指南开始。'); ?></p></div>
        <div class="popular-grid">
            <?php foreach ($blueDocPopular as $document): $path = bluedocPrimaryPath($blueDocData, $document['categoryIds']); ?>
                <a class="popular-card" data-popular-cid="<?php echo $document['cid']; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>">
                    <span class="popular-category"><?php echo bluedocEscape($path ? $path[count($path) - 1]['name'] : _t('使用指南')); ?></span>
                    <h3><?php echo bluedocEscape($document['title']); ?></h3>
                    <span class="read-link"><?php _e('阅读指南'); ?> <span aria-hidden="true">→</span></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (!$blueDocPopular): ?><p class="empty-state"><?php _e('教程准备中。'); ?></p><?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
