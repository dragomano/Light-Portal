<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Utils\ParamWrapper;

function show_topics(array $topics, ParamWrapper $parameters, bool $full_width): void
{
	if ($full_width) {
		echo '
	<div class="recent_topics_list">';
	} else {
		echo '
	<div class="recent_topics noup">';
	}

	if (empty($parameters['use_simple_style'])) {
		foreach ($topics as $topic) {
			$topic['preview'] = '<a href="' . $topic['href'] . '">' . $topic['preview'] . '</a>';

			echo '
		<div class="word_break">';

			if (! empty($parameters['show_avatars']) && isset($topic['poster']['avatar']))
				echo '
			<div class="poster_avatar" title="', $topic['poster']['name'], '">', $topic['poster']['avatar'], '</div>';

			if ($topic['is_new'])
				echo '
			<a class="new_posts" href="', Config::$scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', Lang::$txt['new'], '</a> ';

			echo '
			<span>', (empty($parameters['show_icons']) ? '' : ($topic['icon'] . ' ')), $topic[$parameters['link_type']];

			if (empty($parameters['show_avatars']))
				echo '
				<br><span class="smalltext">', Lang::$txt['by'], ' ', $topic['poster']['link'], '</span>';

			echo '
				<br><span class="smalltext">', $topic['timestamp'], '</span>
			</span>
		</div>';
		}
	} else {
		foreach ($topics as $topic) {
			$topic['preview'] = '<a href="' . $topic['href'] . '">' . $topic['preview'] . '</a>';

			echo '
		<div class="windowbg">
			<div class="smalltext">', $topic['time'], '</div>';

			echo $topic[$parameters['link_type']];

			echo '
			<div class="smalltext', Utils::$context['right_to_left'] ? ' floatright' : '', '">
				<i class="fas fa-eye"></i> ', $topic['views'], '&nbsp;
				<i class="fas fa-comment"></i> ', $topic['replies'], '
			</div>
		</div>';
		}
	}

	echo '
	</div>';
}
