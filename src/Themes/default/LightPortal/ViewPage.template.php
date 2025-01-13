<?php

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Utils\{Icon, Setting};
use Bugo\LightPortal\EventManagerFactory;

function template_show_page(): void
{
	if (! empty(Utils::$context['lp_page']['errors'])) {
		echo '
	<aside class="errorbox">
		<ul>';

		Utils::$context['lp_page']['errors'] = array_unique(Utils::$context['lp_page']['errors']);
		foreach (Utils::$context['lp_page']['errors'] as $error) {
			echo '
			<li><strong>', $error, '</strong></li>';
		}

		echo '
		</ul>
	</aside>';
	}

	if (Utils::$context['lp_page']['can_edit']) {
		echo '
	<aside class="infobox">
		<div>
			<strong>', Lang::$txt['edit_permissions'], '</strong>: ', Lang::$txt['lp_permissions'][Utils::$context['lp_page']['permissions']], '
		</div>
		<div>
			<a class="button floatright" href="', Config::$scripturl, '?action=admin;area=lp_pages;sa=edit;id=', Utils::$context['lp_page']['id'], '">', Icon::get('edit'), '<span class="hidden-xs">', Lang::$txt['edit'], '</span></a>';

		if (! (empty(Utils::$context['user']['is_admin']) || empty(Config::$modSettings['lp_frontpage_mode']) || Config::$modSettings['lp_frontpage_mode'] !== 'chosen_pages')) {
			echo '
			<a class="button floatright" href="', Utils::$context['canonical_url'], ';promote">', Icon::get('home'), '<span class="hidden-xs hidden-sm">', Lang::$txt['lp_' . (in_array(Utils::$context['lp_page']['id'], Setting::getFrontpagePages()) ? 'remove_from' : 'promote_to') . '_fp'], '</span></a>';
		}

		echo '
		</div>
	</aside>';
	}

	echo '
	<section itemscope itemtype="https://schema.org/Article">
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', Utils::$context['canonical_url'], '" content="', Utils::$context['canonical_url'], '">';

	if (! isset(Utils::$context['lp_page']['options']['show_title']) || ! empty(Utils::$context['lp_page']['options']['show_title']) || ! empty(Utils::$context['lp_page']['options']['show_author_and_date'])) {
		echo '
		<div id="display_head" class="windowbg">';

		if (! isset(Utils::$context['lp_page']['options']['show_title']) || ! empty(Utils::$context['lp_page']['options']['show_title'])) {
			echo '
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', Utils::$context['page_title'], '</span>
			</h2>';
		}

		if (! empty(Utils::$context['lp_page']['options']['show_author_and_date'])) {
			echo /** @lang text */ '
			<p>
				<span class="floatleft" itemprop="author" itemscope itemtype="https://schema.org/Person">
					', Icon::get('user'), '<span itemprop="name">', Utils::$context['lp_page']['author'], '</span>
					<meta itemprop="url" content="', Config::$scripturl, '?action=profile;u=', Utils::$context['lp_page']['author_id'], '">
				</span>
				', Utils::$context['lp_page']['post_author'] ?? '', '
				<time class="floatright" datetime="', date('c', Utils::$context['lp_page']['created_at']), '" itemprop="datePublished">
					', Icon::get('date'), Utils::$context['lp_page']['created'], empty(Utils::$context['lp_page']['updated_at']) ? '' : (' / ' . Utils::$context['lp_page']['updated'] . ' <meta itemprop="dateModified" content="' . date('c', Utils::$context['lp_page']['updated_at']) . '">'), '
				</time>
			</p>';
		}

		echo '
		</div>';
	}

	echo '
		<article class="roundframe" itemprop="articleBody">
			<h3 style="display: none">
				', Utils::$context['lp_page']['author'], ' - ', Utils::$context['page_title'], '
			</h3>';

	if (! empty(Utils::$context['lp_page']['tags']) && ! empty(Config::$modSettings['lp_show_tags_on_page'])) {
		echo '
			<div class="smalltext">';

		foreach (Utils::$context['lp_page']['tags'] as $tag) {
			echo '
				<a class="button" href="', $tag['href'], '">', $tag['icon'], $tag['title'], '</a>';
		}

		echo '
			</div>
			<hr>';
	}

	app(EventManagerFactory::class)()->dispatch(PortalHook::beforePageContent);

	if (! empty(Theme::$current->settings['og_image'])) {
		echo '
			<meta itemprop="image" content="', Theme::$current->settings['og_image'], '">';
	}

	echo '
			<div class="page_', Utils::$context['lp_page']['type'], '">
				', Utils::$context['lp_page']['content'], '
			</div>';

	app(EventManagerFactory::class)()->dispatch(PortalHook::afterPageContent);

	echo '
		</article>';

	show_prev_next_links();

	show_related_pages();

	show_comments();

	echo '
	</section>';
}

function show_prev_next_links(): void
{
	if (empty(Utils::$context['lp_page']['prev']) && empty(Utils::$context['lp_page']['next']))
		return;

	echo '
	<div class="generic_list_wrapper">';

	if (!empty(Utils::$context['lp_page']['prev']))
		echo '
		<a class="floatleft" href="', Utils::$context['lp_page']['prev']['link'], '">', Icon::get('arrow_left'), ' ', Utils::$context['lp_page']['prev']['title'], '</a>';

	if (!empty(Utils::$context['lp_page']['next']))
		echo '
		<a class="floatright" href="', Utils::$context['lp_page']['next']['link'], '">', Utils::$context['lp_page']['next']['title'], ' ', Icon::get('arrow_right'), '</a>';

	echo '
	</div>';
}

function show_related_pages(): void
{
	if (empty(Utils::$context['lp_page']['related_pages']))
		return;

	echo '
		<div class="related_pages">
			<div class="cat_bar">
				<h3 class="catbg">', Lang::$txt['lp_related_pages'], '</h3>
			</div>
			<div class="list">';

	foreach (Utils::$context['lp_page']['related_pages'] as $page) {
		echo '
				<div class="windowbg">';

		if (! empty($page['image'])) {
			echo '
					<a href="', $page['link'], /** @lang text */ '">
						<div class="article_image">
							<img alt="', $page['title'], '" src="', $page['image'], '">
						</div>
					</a>';
		}

		echo '
					<a href="', $page['link'], '">', $page['title'], '</a>
				</div>';
	}

	echo '
			</div>
		</div>';
}

function show_comments(): void
{
	if (empty(Config::$modSettings['lp_comment_block']) || empty(Utils::$context['lp_page']['options']['allow_comments']))
		return;

	if (Config::$modSettings['lp_comment_block'] === 'none')
		return;

	if (! empty(Utils::$context['lp_' . Config::$modSettings['lp_comment_block'] . '_comment_block'])) {
		echo Utils::$context['lp_' . Config::$modSettings['lp_comment_block'] . '_comment_block'];
		return;
	}

	if (Config::$modSettings['lp_comment_block'] !== 'default')
		return;

	echo /** @lang text */ '
	<div id="svelte_comments"></div>
	<script type="module">
		usePortalApi("', Utils::$context['lp_comments_api_endpoint'], '", "bundle_comments.js")
	</script>';
}
