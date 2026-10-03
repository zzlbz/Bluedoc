<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>
<?php if ($this->have()): ?>
    <div class="document-list">
        <?php while ($this->next()): ?>
            <article class="document-card">
                <div class="document-meta">
                    <span><?php $this->category(' / '); ?></span>
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time>
                </div>
                <h2><a href="<?php $this->permalink(); ?>"><?php echo bluedocEscape($this->title); ?></a></h2>
                <p class="document-excerpt"><?php $this->excerpt(140); ?></p>
                <a class="read-link" href="<?php $this->permalink(); ?>"><?php _e('阅读文档'); ?><span class="sr-only">：<?php echo bluedocEscape($this->title); ?></span> <span aria-hidden="true">→</span></a>
            </article>
        <?php endwhile; ?>
    </div>
    <?php $this->pageNav(_t('上一页'), _t('下一页')); ?>
<?php else: ?>
    <div class="empty-state">
        <h2><?php _e('暂无文档'); ?></h2>
        <p><?php echo $this->is('search') ? _t('没有匹配的文档，请尝试其他关键词。') : _t('发布文章后，文档会显示在这里。'); ?></p>
    </div>
<?php endif; ?>
