<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$blueDocLanguage = str_replace('_', '-', (string) ($this->options->lang ?: 'zh-CN'));
$blueDocArchiveTitle = $this->getArchiveTitle();
?>
<!DOCTYPE html>
<html lang="<?php echo bluedocEscape($blueDocLanguage); ?>">
<head>
    <meta charset="<?php echo bluedocEscape($this->options->charset); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php if ($blueDocArchiveTitle): ?><?php echo bluedocEscape($blueDocArchiveTitle); ?> - <?php endif; ?><?php echo bluedocEscape($this->options->title); ?></title>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/style.css'); ?>">
    <?php $this->header(); ?>
</head>
<body>
<a class="skip-link" href="#main"><?php _e('跳转到正文'); ?></a>
<header class="site-header">
    <div class="header-inner">
        <a class="site-brand" href="<?php $this->options->siteUrl(); ?>">
            <span class="brand-mark" aria-hidden="true">B</span>
            <span><?php echo bluedocEscape($this->options->title); ?></span>
        </a>
        <form class="site-search" method="get" action="<?php $this->options->siteUrl(); ?>" role="search">
            <label class="sr-only" for="bluedoc-search"><?php _e('搜索文档'); ?></label>
            <input id="bluedoc-search" name="s" type="search" placeholder="<?php _e('搜索文档…'); ?>" value="<?php echo $this->is('search') ? bluedocEscape($blueDocArchiveTitle) : ''; ?>">
            <button type="submit"><?php _e('搜索'); ?></button>
        </form>
    </div>
    <nav class="site-nav" aria-label="<?php _e('主导航'); ?>">
        <a href="<?php $this->options->siteUrl(); ?>"<?php if ($this->is('index')): ?> aria-current="page"<?php endif; ?>><?php _e('文档首页'); ?></a>
        <?php \Widget\Contents\Page\Rows::alloc()->to($blueDocPages); ?>
        <?php while ($blueDocPages->next()): ?>
            <a href="<?php $blueDocPages->permalink(); ?>"<?php if ($this->is('page', $blueDocPages->slug) && !$this->is('index')): ?> aria-current="page"<?php endif; ?>><?php echo bluedocEscape($blueDocPages->title); ?></a>
        <?php endwhile; ?>
    </nav>
</header>
