<?php

global $context, $scripturl;

if (empty($context['lp_boardlist']))
	return;

foreach ($context['lp_boardlist'] as $category) {
	echo sprintf($context['lp_all_title_classes'][$parameters['category_class']], $category['name'], null, null);

	echo '
	<div', !empty($parameters['board_class']) ? ' class="' . $parameters['board_class'] . '"' : '', '>
		<ul class="smalltext" style="padding-left: 10px">';

	foreach ($category['boards'] as $board) {
		echo '
			<li>';

		if ($board['child_level'])
			echo '
				<ul class="smalltext" style="padding-left: 10px">
					<li>', $context['current_board'] == $board['id'] ? '<strong>' : '', '&raquo; <a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>', $context['current_board'] == $board['id'] ? '</strong>' : '', '</li>
				</ul>';
		else
			echo '
				', $context['current_board'] == $board['id'] ? '<strong>' : '', '<a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>', $context['current_board'] == $board['id'] ? '</strong>' : '';

		echo '
			</li>';
	}

	echo '
		</ul>
	</div>';
}
