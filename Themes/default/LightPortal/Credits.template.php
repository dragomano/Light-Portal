<?php

// The portal credits template | Шаблон просмотра копирайтов используемых компонентов портала
function template_portal_credits()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_used_components'], '</h3>
	</div>
	<div class="windowbg noup">
		<ul class="normallist">';

	foreach ($context['lp_components'] as $item) {
		echo '
			<li>
				<a href="' . $item['link'] . '" target="_blank" rel="noopener">' . $item['title'] . '</a> ' . (isset($item['author']) ? ' | &copy; ' . $item['author'] : '') . ' | Licensed under <a href="' . $item['license']['link'] . '" target="_blank" rel="noopeneer">' . $item['license']['name'] . '</a>
			</li>';
	}

	echo '
		</ul>
	</div>';
}
