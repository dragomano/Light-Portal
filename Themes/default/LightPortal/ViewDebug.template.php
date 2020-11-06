<?php

function template_debug_above()
{
}

/**
 * Displaying the portal debugging information at the bottom of the page
 *
 * Отображение отладочной информации портала в нижней части страницы
 *
 * @return void
 */
function template_debug_below()
{
	global $context;

	if (!empty($context['lp_load_page_stats'])) {
		echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">', $context['lp_load_page_stats'], '</div>';
	}

	if (!empty($context['lp_current_queries'])) {
		echo '
	<div class="lp_queries noticebox smalltext" style="margin-top: 2px">';

		foreach ($context['lp_current_queries'] as $query) {
			echo '
		<div class="windowbg">', $query, '</div>';
		}

		echo '
	</div>';
	}
}
