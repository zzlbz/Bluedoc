<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>
<main id="main" class="site-main" tabindex="-1">
    <article class="document">
        <header class="section-header">
            <a class="back-link" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回文档中心'); ?></a>
            <h1><?php echo bluedocEscape($this->title); ?></h1>
            <div class="document-meta">
                <span><?php _e('发布于'); ?> <time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time></span>
                <span><?php $this->category(' / '); ?></span>
            </div>
        </header>
        <div class="document-content"><?php $this->content(); ?></div>
    </article>
    <?php $this->need('comments.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
