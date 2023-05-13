<?php

function template_portal_credits()
{
	global $txt, $context;

	$labels = ['lp_type_block', 'lp_type_editor', 'lp_type_comment', 'lp_type_parser', 'lp_type_article', 'lp_type_frontpage', 'lp_type_impex', 'lp_type_block_options', 'lp_type_page_options', 'lp_type_icons', 'lp_type_seo', 'lp_type_other', 'lp_type_ssi'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['lp_icon_set']['users'], $txt['lp_contributors'], '</h3>
	</div>
	<div class="windowbg noup">
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_translators'], '</h4>
		</div>
		<div class="row">';

		foreach ($context['portal_translations'] as $lang => $translators) {
			echo '
			<div class="col-xs-12 col-sm-6">
				<fieldset class="windowbg">
					<legend class="new_posts ', $labels[random_int(0, count($labels) - 1)], '">', $lang, '</legend>', sentence_list($translators), '
				</fieldset>
			</div>';
		}

		echo '
		</div>
		<br>
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_testers'], '</h4>
		</div>
		<ul>';

	if (! empty($context['testers'])) {
		echo '
			<li class="windowbg">';

		foreach ($context['testers'] as $tester) {
			echo '
				<a class="button" href="', $tester['link'], '" target="_blank" rel="nofollow noopener">', $tester['name'], '</a>';
		}

		echo '
			</li>';
	}

	echo '
		</ul>
		<br>
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_sponsors'], '</h4>
		</div>
		<ul>';

	if (! empty($context['sponsors'])) {
		echo '
			<li class="windowbg">';

		foreach ($context['sponsors'] as $sponsor) {
			echo '
				<a class="button" href="', $sponsor['link'], '" target="_blank" rel="nofollow noopener">', $sponsor['name'], '</a>';
		}

		echo '
			</li>';
	}

	echo '
		</ul>
		<br>
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_tools'], '<span class="floatright">', $txt['lp_quotes'][1], '</span></h4>
		</div>
		<div class="row">';

	foreach ($context['tools'] as $tool) {
		echo '
			<div class="col-xs-12 col-sm-6">
				<div class="windowbg centertext" style="padding: 1px">
					<a class="button" style="width: 100%" href="', $tool['link'], '" target="_blank" rel="nofollow noopener">', $tool['name'], '</a>
				</div>
			</div>';
	}

	echo '
		</div>
	</div>';

	if (empty($context['lp_components']))
		return;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['lp_icon_set']['copyright'], $txt['lp_used_components'], '</h3>
	</div>
	<div class="roundframe noup">
		<ul>';

	foreach ($context['lp_components'] as $item) {
		echo '
			<li class="windowbg">';

		if (empty($item['link'])) {
			echo '
				', $item['title'];
		} else {
			echo '
				<a class="bbc_link" href="', $item['link'], '" target="_blank" rel="noopener">', $item['title'], '</a>';
		}

		echo ' ', (isset($item['author']) ? ' | &copy; ' . $item['author'] : '');

		if (! empty($item['license'])) {
			echo ' | <a href="', $item['license']['link'], '" target="_blank" rel="noopener">', $item['license']['name'], '</a>';
		}

		echo '
			</li>';
	}

	echo '
		</ul>
	</div>';
}
