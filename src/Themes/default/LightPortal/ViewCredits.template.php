<?php

use Bugo\LightPortal\Utils\{Lang, Utils};

function template_portal_credits(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['lp_icon_set']['users'], Lang::$txt['lp_contributors'], '</h3>
	</div>
	<div class="windowbg noup">
		<div class="title_bar">
			<h4 class="titlebg">', Lang::$txt['lp_translators'], '</h4>
		</div>
		<div class="row">';

		foreach (Utils::$context['portal_translations'] as $lang => $translators) {
			echo '
			<div class="col-xs-12 col-sm-6">
				<div class="sub_bar">
					<h5 class="subbg">', $lang, '</h5>
				</div>
				<fieldset class="windowbg noup">', sentence_list($translators), '</fieldset>
			</div>';
		}

		echo '
		</div>
		<div class="title_bar">
			<h4 class="titlebg">', Lang::$txt['lp_testers'], '</h4>
		</div>
		<div class="roundframe noup">';

	if (! empty(Utils::$context['testers'])) {
		foreach (Utils::$context['testers'] as $tester) {
			echo '
			<a class="button" href="', $tester['link'], '" target="_blank" rel="nofollow noopener">', $tester['name'], '</a>';
		}
	}

	echo '
		</div>
		<div class="title_bar">
			<h4 class="titlebg">', Lang::$txt['lp_sponsors'], '</h4>
		</div>
		<div class="roundframe noup">';

	if (! empty(Utils::$context['sponsors'])) {
		foreach (Utils::$context['sponsors'] as $sponsor) {
			echo '
			<a class="button" href="', $sponsor['link'], '" target="_blank" rel="nofollow noopener">', $sponsor['name'], '</a>';
		}
	}

	echo '
		</div>
		<div class="title_bar">
			<h4 class="titlebg">', Lang::$txt['lp_tools'], '</h4>
		</div>
		<div class="row">';

	foreach (Utils::$context['tools'] as $tool) {
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

	if (empty(Utils::$context['lp_components']))
		return;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['lp_icon_set']['copyright'], Lang::$txt['lp_used_components'], '</h3>
	</div>
	<div class="roundframe noup">';

	foreach (Utils::$context['lp_components'] as $item) {
		echo '
		<div class="windowbg row center-xs between-md">
			<div>';

		if (empty($item['link'])) {
			echo '
				<span>', $item['title'], '</span>';
		} else {
			echo '
				<a class="bbc_link" href="', $item['link'], '" target="_blank" rel="noopener">', $item['title'], '</a>';
		}

		echo '
			</div>
			<div class="hidden-xs hidden-sm">', (empty($item['author']) ? '' : ('&copy; ' . $item['author']));

		if (! empty($item['license'])) {
			if (isset($item['author']))
				echo ' | ';

			echo '
				<a href="', $item['license']['link'], '" target="_blank" rel="noopener">', $item['license']['name'], '</a>';
		}

		echo '
			</div>
		</div>';
	}

	echo '
	</div>';
}
