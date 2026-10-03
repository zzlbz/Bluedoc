<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->comments()->to($comments);
?>
<section id="comments" class="comments" aria-label="<?php _e('文档评论'); ?>">
    <?php if ($comments->have()): ?>
        <h2><?php $this->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></h2>
        <?php $comments->listComments(); ?>
        <?php $comments->pageNav(_t('上一页'), _t('下一页')); ?>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="respond">
            <div class="cancel-comment-reply"><?php $comments->cancelReply(); ?></div>
            <h2><?php _e('发表评论'); ?></h2>
            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="comment-form">
                <?php if ($this->user->hasLogin()): ?>
                    <p>
                        <?php _e('当前用户：'); ?>
                        <a href="<?php $this->options->profileUrl(); ?>"><?php echo bluedocEscape($this->user->screenName); ?></a>
                        <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出'); ?></a>
                    </p>
                <?php else: ?>
                    <p>
                        <label for="author"><?php _e('称呼（必填）'); ?></label>
                        <input id="author" name="author" type="text" autocomplete="name" value="<?php $this->remember('author'); ?>" required>
                    </p>
                    <p>
                        <label for="mail"><?php _e('邮箱'); ?><?php if ($this->options->commentsRequireMail): ?><?php _e('（必填）'); ?><?php endif; ?></label>
                        <input id="mail" name="mail" type="email" autocomplete="email" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                    </p>
                    <p>
                        <label for="url"><?php _e('网站'); ?><?php if ($this->options->commentsRequireUrl): ?><?php _e('（必填）'); ?><?php endif; ?></label>
                        <input id="url" name="url" type="url" autocomplete="url" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>>
                    </p>
                <?php endif; ?>
                <p>
                    <label for="textarea"><?php _e('评论内容（必填）'); ?></label>
                    <textarea id="textarea" name="text" rows="5" required></textarea>
                </p>
                <button type="submit"><?php _e('提交评论'); ?></button>
            </form>
        </div>
    <?php elseif (!$comments->have()): ?>
        <p class="muted"><?php _e('评论已关闭。'); ?></p>
    <?php endif; ?>
</section>
