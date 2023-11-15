<?php

function template_show_page()
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	if (! empty($context['lp_page']['errors'])) {
		echo '
	<aside class="errorbox">
		<ul>';

		$context['lp_page']['errors'] = array_unique($context['lp_page']['errors']);
		foreach ($context['lp_page']['errors'] as $error) {
			echo '
			<li><strong>', $error, '</strong></li>';
		}

		echo '
		</ul>
	</aside>';
	}

	if ($context['lp_page']['can_edit']) {
		echo '
	<aside class="infobox">
		<div>
			<strong>', $txt['edit_permissions'], '</strong>: ', $txt['lp_permissions'][$context['lp_page']['permissions']], '
		</div>
		<div>
			<a class="button floatright" href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=', $context['lp_page']['id'], '">', $context['lp_icon_set']['edit'], '<span class="hidden-xs">', $txt['edit'], '</span></a>';

		if (! (empty($context['user']['is_admin']) || empty($modSettings['lp_frontpage_mode']) || $modSettings['lp_frontpage_mode'] !== 'chosen_pages')) {
			echo '
			<a class="button floatright" href="', $context['canonical_url'], ';promote">', $context['lp_icon_set']['home'], '<span class="hidden-xs hidden-sm">', $txt['lp_' . (in_array($context['lp_page']['id'], $context['lp_frontpage_pages']) ? 'remove_from' : 'promote_to') . '_fp'], '</span></a>';
		}

		echo '
		</div>
	</aside>';
	}

	echo '
	<section itemscope itemtype="https://schema.org/Article">
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="', $context['canonical_url'], '" content="', $context['canonical_url'], '">';

	if (! isset($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_author_and_date'])) {
		echo '
		<div id="display_head" class="windowbg">';

		if (! isset($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_title'])) {
			echo '
			<h2 class="display_title" itemprop="headline">
				<span id="top_subject">', $context['page_title'], '</span>
			</h2>';
		}

		if (! empty($context['lp_page']['options']['show_author_and_date'])) {
			echo '
			<p>
				<span class="floatleft" itemprop="author" itemscope itemtype="https://schema.org/Person">
					', $context['lp_icon_set']['user'], '<span itemprop="name">', $context['lp_page']['author'], '</span>
					<meta itemprop="url" content="', $scripturl, '?action=profile;u=', $context['lp_page']['author_id'], '">
				</span>
				', $context['lp_page']['post_author'] ?? '', '
				<time class="floatright" datetime="', date('c', $context['lp_page']['created_at']), '" itemprop="datePublished">
					', $context['lp_icon_set']['date'], $context['lp_page']['created'], empty($context['lp_page']['updated_at']) ? '' : (' / ' . $context['lp_page']['updated'] . ' <meta itemprop="dateModified" content="' . date('c', $context['lp_page']['updated_at']) . '">'), '
				</time>
			</p>';
		}

		echo '
		</div>';
	}

	echo '
		<article class="roundframe" itemprop="articleBody">
			<h3 style="display: none">', $context['lp_page']['author'], ' - ', $context['page_title'], '</h3>';

	if (! empty($context['lp_page']['tags']) && ! empty($modSettings['lp_show_tags_on_page'])) {
		echo '
			<div class="smalltext">';

		foreach ($context['lp_page']['tags'] as $tag) {
			echo '
				<a class="button" href="', $tag['href'], '">', $context['lp_icon_set']['tag'], $tag['name'], '</a>';
		}

		echo '
			</div>
			<hr>';
	}

	call_portal_hook('beforePageContent');

	if (! empty($settings['og_image'])) {
		echo '
			<meta itemprop="image" content="', $settings['og_image'], '">';
	}

	echo '
			<div class="page_', $context['lp_page']['type'], '">', $context['lp_page']['content'], '</div>';

	call_portal_hook('afterPageContent');

	echo '
		</article>';

	show_prev_next_links();

	show_related_pages();

	show_comments();

	echo '
	</section>';
}

function show_prev_next_links()
{
	global $context;

	if (empty($context['lp_page']['prev']) && empty($context['lp_page']['next']))
		return;

	echo '
	<div class="generic_list_wrapper">';

	if (!empty($context['lp_page']['prev']))
		echo '
		<a class="floatleft" href="', $context['lp_page']['prev']['link'], '">', $context['lp_icon_set']['arrow_left'], ' ', $context['lp_page']['prev']['title'], '</a>';

	if (!empty($context['lp_page']['next']))
		echo '
		<a class="floatright" href="', $context['lp_page']['next']['link'], '">', $context['lp_page']['next']['title'], ' ', $context['lp_icon_set']['arrow_right'], '</a>';

	echo '
	</div>';
}

function show_related_pages()
{
	global $context, $txt;

	if (empty($context['lp_page']['related_pages']))
		return;

	echo '
		<div class="related_pages">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['lp_related_pages'], '</h3>
			</div>
			<div class="list">';

	foreach ($context['lp_page']['related_pages'] as $page) {
		echo '
				<div class="windowbg">';

		if (! empty($page['image'])) {
			echo '
					<a href="', $page['link'], '">
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

function show_comments()
{
	global $modSettings, $context, $db_show_debug, $settings;

	if (empty($modSettings['lp_show_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
		return;

	if ($modSettings['lp_show_comment_block'] === 'none')
		return;

	if (! empty($context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'])) {
		echo $context['lp_' . $modSettings['lp_show_comment_block'] . '_comment_block'];
		return;
	}

	if ($modSettings['lp_show_comment_block'] !== 'default')
		return;

	echo '
	<div id="vue_comments"></div>
	<script>
		const vueGlobals = {
			user: ', $context['lp_json']['user'], ',
			context: ', $context['lp_json']['context'], ',
			settings: ', $context['lp_json']['settings'], ',
			icons: ', $context['lp_json']['icons'], ',
			txt: ', $context['lp_json']['txt'], ',
		}
	</script>';

	if ($db_show_debug) {
		echo '
	<script src="https://cdn.jsdelivr.net/combine/npm/vue@3/dist/vue.global.min.js,npm/vue3-sfc-loader@0.8.4,npm/vue-demi@0.14.6,npm/pinia@2,npm/showdown@2,npm/vue-showdown@4,npm/vue-i18n@9/dist/vue-i18n.global.prod.min.js,npm/@vueuse/shared@10,npm/@vueuse/core@10"></script>
	<script type="module" src="https://cdn.jsdelivr.net/npm/@github/markdown-toolbar-element@2/dist/index.min.js"></script>
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/dev/helpers.js"></script>
	<script type="module" src="', $settings['default_theme_url'], '/scripts/light_portal/dev/comment_helpers.js"></script>
	<script type="module" src="', $settings['default_theme_url'], '/scripts/light_portal/dev/vue_comments.js"></script>';
	} else {
		echo '
	<script type="module" src="', $settings['default_theme_url'], '/scripts/light_portal/bundle_comments.js"></script>';
	}
}
