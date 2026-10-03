<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>
<footer class="site-footer">
    <p>&copy; <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>"><?php echo bluedocEscape($this->options->title); ?></a></p>
    <p><?php _e('由'); ?> <a href="https://typecho.org" rel="generator">Typecho</a> <?php _e('驱动'); ?> · BlueDoc</p>
</footer>
<script src="<?php $this->options->themeUrl('assets/js/main.js'); ?>?v=<?php echo BLUEDOC_VERSION; ?>" defer></script>
<?php $this->footer(); ?>
</body>
</html>
