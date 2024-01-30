<?php if (empty($context['lp_active_blocks'])): ?>
<div class="col-xs">
<?php endif ?>

	<div class="lp_frontpage_articles article_view">
		<?= show_pagination() ?>

		<?php foreach ($context['lp_frontpage_articles'] as $article): ?>
		<div class="col-xs-12 col-sm-6 col-md-<?= $context['lp_frontpage_num_columns'] ?>">
			<article class="roundframe<?= $article['css_class'] ?? '' ?>">
				<?php if (!empty($article['image'])): ?>
					<?php if ($article['is_new']): ?>
					<div class="new_hover">
						<div class="new_icon">
							<span class="new_posts"><?= $txt['new'] ?></span>
						</div>
					</div>
					<?php endif ?>
					<?php if ($article['can_edit']): ?>
					<div class="info_hover">
						<div class="edit_icon">
							<a href="<?= $article['edit_link'] ?>"><?= $this->icon('edit', $txt['edit']) ?></a>
						</div>
					</div>
					<?php endif ?>
					<div class="card_img"></div>
					<a href="<?= $article['link'] ?>">
						<div class="card_img_hover lazy" data-bg="<?= $article['image'] ?>"></div>
					</a>
				<?php endif ?>
				<div class="card_info">
					<span class="card_date smalltext">
						<?php if (!empty($article['section']['name'])): ?>
							<a class="floatleft" href="<?= $article['section']['link'] ?>">
								<?= $this->icon('category') ?><?= $article['section']['name'] ?>
							</a>
						<?php endif ?>
						<?php if ($article['is_new'] && empty($article['image'])): ?>
							<span class="new_posts">
								<?= $txt['new'] ?>
							</span>
						<?php endif ?>
						<?php if (!empty($article['datetime'])): ?>
							<time class="floatright" datetime="{$article[datetime]}">
								<?= $this->icon('date') ?><?= $article['date'] ?>
							</time>
						<?php endif ?>
					</span>
					<h3>
						<a href="<?= $article['msg_link'] ?>"><?= $article['title'] ?></a>
					</h3>
					<?php if (!empty($article['teaser'])): ?>
						<p><?= $article['teaser'] ?></p>
					<?php endif ?>
					<div>
						<?php if (!empty($article['category'])): ?>
							<span class="card_author">
								<?= $this->icon('category') ?><?= $article['category'] ?>
							</span>
						<?php endif ?>
						<?php if (!empty($modSettings['lp_show_author']) && !empty($article['author'])): ?>
							<?php if (!empty($article['author']['id']) && !empty($article['author']['name'])): ?>
								<a href="<?= $article['author']['link'] ?>" class="card_author">
									<?= $this->icon('user') ?><?= $article['author']['name'] ?>
								</a>
							<?php else: ?>
								<span class="card_author"><?= $txt['guest_title'] ?></span>
							<?php endif ?>
						<?php endif ?>
						<?php if (!empty($modSettings['lp_show_views_and_comments'])): ?>
							<span class="floatright">
								<?php if (!empty($article['views']['num'])): ?>
									<?= $this->icon('views', $article['views']['title']) ?>
									<?= $article['views']['num'] ?>
								<?php endif ?>
								<?php if (!empty($article['views']['after'])): ?>
									<?= $article['views']['after'] ?>
								<?php endif ?>
								<?php if (!empty($article['is_redirect'])): ?>
									<?= $this->icon('redirect') ?>
								<?php endif ?>
								<?php if (!empty($article['replies']['num'])): ?>
									<?= $this->icon('replies', $article['replies']['title']) ?>
									<?= $article['replies']['num'] ?>
								<?php endif ?>
								<?php if (!empty($article['replies']['after'])): ?>
									<?= $article['replies']['after'] ?>
								<?php endif ?>
							</span>
						<?php endif ?>
					</div>
				</div>
			</article>
		</div>
		<?php endforeach ?>

		<?= show_pagination('bottom') ?>
	</div>

<?php if (empty($context['lp_active_blocks'])): ?>
</div>
<?php endif ?>