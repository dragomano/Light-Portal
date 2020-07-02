<?php

/**
 * The portal credits template
 *
 * Шаблон просмотра копирайтов используемых компонентов портала
 *
 * @return void
 */
function template_portal_credits()
{
	global $txt, $context;

	if (empty($context['lp_components']))
		return;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_used_components'], '</h3>
	</div>
	<div class="roundframe noup">
		<ul>';

	foreach ($context['lp_components'] as $item) {
		echo '
			<li class="windowbg">';

		if (!empty($item['link'])) {
			echo '
				<a href="', $item['link'], '" target="_blank" rel="noopener">', $item['title'], '</a>';
		} else {
			echo '
				', $item['title'];
		}

		echo ' ', (isset($item['author']) ? ' | &copy; ' . $item['author'] : ''), ' | Licensed under <a href="', $item['license']['link'], '" target="_blank" rel="noopener">', $item['license']['name'], '</a>';

		echo '
			</li>';
	}

	echo '
		</ul>
	</div>';
}
