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

	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">', $context['lp_load_page_stats'], '</div>';
}
