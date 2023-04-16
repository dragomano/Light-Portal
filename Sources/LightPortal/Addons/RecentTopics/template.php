<?php

function show_topics(array $recent_topics, array $parameters, bool $full_width)
{
	global $scripturl, $txt, $context;

	if ($full_width) {
		echo '
	<div class="recent_topics_list">';
	} else {
		echo '
	<div class="recent_topics noup">';
	}

	if (empty($parameters['use_simple_style'])) {
		foreach ($recent_topics as $topic) {
			echo '
		<div class="windowbg">';

			if (! empty($parameters['show_avatars']) && $topic['poster']['avatar'])
				echo '
			<div class="poster_avatar" title="', $topic['poster']['name'], '">', $topic['poster']['avatar'], '</div>';

			if ($topic['is_new'])
				echo '
			<a class="new_posts" href="', $scripturl, '?topic=', $topic['topic'], '.msg', $topic['new_from'], ';topicseen#new">', $txt['new'], '</a> ';

			echo '
			<span>', (! empty($parameters['show_icons']) ? $topic['icon'] . ' ' : ''), $topic['link'];

			if (empty($parameters['show_avatars']))
				echo '
				<br><span class="smalltext">', $txt['by'], ' ', $topic['poster']['link'], '</span>';

			echo '
				<br><span class="smalltext">', $topic['timestamp'], '</span>
			</span>
		</div>';
		}
	} else {
		foreach ($recent_topics as $topic) {
			echo '
		<div class="windowbg">
			<div class="smalltext">', $topic['time'], '</div>';

			echo $topic['link'];

			echo '
			<div class="smalltext', $context['right_to_left'] ? ' floatright' : '', '">
				<i class="fas fa-eye"></i> ', $topic['views'], '&nbsp;
				<i class="fas fa-comment"></i> ', $topic['replies'], '
			</div>
		</div>';
		}
	}

	echo '
	</div>';
}
