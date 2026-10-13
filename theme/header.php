<?php
if (!defined('__TYPECHO_ROOT_DIR__')) { exit; }
$blueDocLanguage = str_replace('_', '-', (string) ($this->options->lang ?: 'zh-CN'));
$blueDocArchiveTitle = $this->getArchiveTitle();
?>
<!DOCTYPE html>
<html lang="<?php echo bluedocEscape($blueDocLanguage); ?>">
<head>
    <meta charset="<?php echo bluedocEscape($this->options->charset); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php if ($blueDocArchiveTitle): ?><?php echo bluedocEscape($blueDocArchiveTitle); ?> - <?php endif; ?><?php echo bluedocEscape($this->options->title); ?></title>
    <script src="<?php $this->options->themeUrl('assets/js/color-mode.js'); ?>?v=<?php echo BLUEDOC_VERSION; ?>"></script>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/style.css'); ?>?v=<?php echo BLUEDOC_VERSION; ?>">
    <meta name="bluedoc-version" content="<?php echo BLUEDOC_VERSION; ?>">
    <?php $this->header(); ?>
</head>
<body class="<?php echo ($this->is('post') || $this->is('page') || $this->is('category')) ? 'layout-document' : ($this->is('index') ? 'layout-home' : 'layout-list'); ?>">
<a class="skip-link" href="#main"><?php _e('跳转到正文'); ?></a>
<?php $this->need('partials/document-tree.php'); ?>
<div class="workspace">
    <header class="site-header">
        <div class="topbar-location">
            <button class="drawer-toggle" type="button" aria-controls="bluedoc-sidebar" aria-expanded="false" hidden><span aria-hidden="true">☰</span><span class="sr-only"><?php _e('浏览文档'); ?></span></button>
            <?php if ($this->is('post') || $this->is('page') || $this->is('category')): ?>
                <?php $this->need('partials/breadcrumbs.php'); ?>
            <?php else: ?><span class="topbar-title"><?php echo bluedocEscape($this->is('index') ? _t('文档首页') : ($blueDocArchiveTitle ?: _t('浏览文档'))); ?></span><?php endif; ?>
        </div>
        <label class="color-control" hidden><span class="sr-only"><?php _e('配色模式'); ?></span><span aria-hidden="true">◐</span>
            <select id="bluedoc-color" aria-label="<?php _e('配色模式'); ?>"><option value="system"><?php _e('跟随系统'); ?></option><option value="light"><?php _e('浅色'); ?></option><option value="dark"><?php _e('深色'); ?></option></select>
        </label>
    </header>
