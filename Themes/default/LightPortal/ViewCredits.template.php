<?php

/**
 * The portal contributors & credits template
 *
 * Шаблон просмотра списков внесших вклад в развитие портала, а также списка используемых компонентов
 *
 * @return void
 */
function template_portal_credits()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg"><i class="fas fa-users"></i> ', $txt['lp_contributors'], '</h3>
	</div>
	<div class="roundframe noup">
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_translators'], '</h4>
		</div>
		<div>';

		foreach ($context['portal_translations'] as $lang => $translators) {
			echo '
			<fieldset class="windowbg">
				<legend class="amt">', $lang, '</legend>', implode(', ', $translators), '
			</fieldset>';
		}

		echo '
		</div>
		<br>
		<div class="title_bar">
			<h4 class="titlebg">', $txt['lp_testers'], '</h4>
		</div>
		<ul>';

	if (!empty($context['testers'])) {
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

	if (!empty($context['sponsors'])) {
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
	</div>';

	if (empty($context['lp_components']))
		return;

	echo '
	<div class="cat_bar">
		<h3 class="catbg"><i class="far fa-copyright"></i> ', $txt['lp_used_components'], '</h3>
	</div>
	<div class="roundframe noup">
		<ul>';

	foreach ($context['lp_components'] as $item) {
		echo '
			<li class="windowbg">';

		if (!empty($item['link'])) {
			echo '
				<a class="bbc_link" href="', $item['link'], '" target="_blank" rel="noopener">', $item['title'], '</a>';
		} else {
			echo '
				', $item['title'];
		}

		echo ' ', (isset($item['author']) ? ' | &copy; ' . $item['author'] : '');

		if (!empty($item['license'])) {
			echo ' | ', strpos($item['license']['name'], 'the ') !== false ? 'Licensed under ' : '', '<a href="', $item['license']['link'], '" target="_blank" rel="noopener">', $item['license']['name'], '</a>';
		}

		echo '
			</li>';
	}

	echo '
		</ul>
	</div>';
}
