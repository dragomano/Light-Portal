<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\LightPortal\Utils\ParamWrapper;

function show_posts(array $posts, ParamWrapper $parameters, bool $full_width): void
{
	if ($full_width) {
		echo '
	<div class="recent_posts_list">';
	} else {
		echo '
	<div class="recent_posts noup">';
	}

	if (empty($parameters['use_simple_style'])) {
		foreach ($posts as $post) {
			$post['preview'] = '<a href="' . $post['href'] . '">' . $post['preview'] . '</a>';

			echo '
		<div class="word_break">';

			if ($parameters['show_avatars'] && isset($post['poster']['avatar']))
				echo '
			<div class="poster_avatar" title="', $post['poster']['name'], '">', $post['poster']['avatar'], '</div>';

			if ($post['is_new'])
				echo '
			<a class="new_posts" href="', Config::$scripturl, '?topic=', $post['topic'], '.msg', $post['new_from'], ';topicseen#new">', Lang::$txt['new'], '</a> ';

			echo '
			<span>', $post[$parameters['link_type']];

			if (empty($parameters['show_avatars']))
				echo '
				<br><span class="smalltext">', Lang::$txt['by'], ' ', $post['poster']['link'], '</span>';

			echo '
				<br><span class="smalltext">', $post['timestamp'], '</span>
			</span>';

			if (! empty($parameters['show_body']))
				echo '
			<div>', $post['body'], '</div>';

			echo '
		</div>';
		}
	} else {
		foreach ($posts as $post) {
			$post['preview'] = '<a href="' . $post['href'] . '">' . $post['preview'] . '</a>';

			echo '
		<div class="windowbg">
			<div class="smalltext">', $post['time'], '</div>';

			echo $post[$parameters['link_type']];

			if (! empty($parameters['show_body']))
				echo '
			<div>', $post['body'], '</div>';

			echo '
		</div>';
		}
	}

	echo '
	</div>';
}
