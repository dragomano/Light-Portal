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
	global $context, $txt;

	echo '
	<div class="centertext clear noticebox smalltext" style="margin-top: 2px">', $context['lp_load_page_stats'], '</div>';

	if (!empty($context['lp_detail_cache_info'])) {
		echo '
	<fieldset class="descbox">
		<legend>&nbsp;', $txt['lp_cache_info'], '&nbsp;</legend>';

		foreach ($context['lp_detail_cache_info'] as $info) {
			echo '
		<div class="', $info['level'], 'box">', $info['title'], '<details><figure><pre style="white-space: pre-line">', $info['details'], '</pre></figure></details></div>';
		}

		echo '
	</fieldset>';
	}
}
