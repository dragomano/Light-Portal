<?php if (empty($context['lp_active_blocks'])): ?>
<div class="col-xs">
<?php endif ?>
    <!-- <?= $this->debug($context['user']) ?> -->

    <div class="lp_frontpage_articles article_custom">
        <?= show_pagination() ?>

        <?php foreach ($context['lp_frontpage_articles'] as $article): ?>
        <div class="
            col-xs-12 col-sm-6 col-md-4
            col-lg-<?= $context['lp_frontpage_num_columns'] ?>
            col-xl-<?= $context['lp_frontpage_num_columns'] ?>
        ">
            <figure class="noticebox"><?= $this->debug($article) ?></figure>
        </div>
        <?php endforeach ?>

        <?= show_pagination('bottom') ?>
    </div>

<?php if (empty($context['lp_active_blocks'])): ?>
</div>
<?php endif ?>