<?php

/**
 * Output page tags
 *
 * Вывод тегов страниц
 *
 * @return void
 */
function template_show_tags()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>';

	if (empty($context['lp_tags'])) {
		echo '
	<div class="information">', $txt['lp_no_tags'], '</div>';
		return;
	}

	echo '
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col" class="keyword">
					', $txt['lp_keyword_column'], '
				</th>
				<th scope="col" class="frequency">
					', $txt['lp_frequency_column'], '
				</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['lp_tags'] as $tag) {
		echo '
		<tr class="windowbg">
			<td class="keyword centertext">
				<a href="', $scripturl, '?action=portal;sa=tags;key=', urlencode($tag['keyword']), '">', $tag['keyword'], '</a>
			</td>
			<td class="frequency centertext">
				', $tag['frequency'], '
			</td>
		</tr>';
	}

	echo '
		</tbody>
	</table>';
}
