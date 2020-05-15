<?php

/**
 * Preview for the portal layout
 *
 * Предварительный просмотр макета портала
 *
 * @return void
 */
function template_callback_portal_layout_preview()
{
	global $modSettings, $context, $txt;

	if (empty($modSettings['lp_frontpage_mode']))
		return;

	$num_rows = $context['lp_frontpage_layout'];
	$num_cols = ceil($modSettings['lp_num_items_per_page'] / $num_rows);

	echo '
	<dt style="width: 0"></dt>
	<dd style="width: 100%">
		<table class="table_grid centertext" style="margin: 0 auto; width: 50%">
			<caption>', $txt['preview'], '</caption>
			<tbody>';

	$k = 1;
	for ($i = 0; $i < $num_rows; $i++) {
		echo '
			<tr class="windowbg">';

		for ($j = 0; $j < $num_cols; $j++) {
			$k++;

			echo '
				<td>', $k - 1 > $modSettings['lp_num_items_per_page'] ? '' : ('<div class="title_bar"><h3 class="titlebg">' . $txt['lp_article'] . '</h3></div>'), '</td>';
		}

		echo '
			</tr>';
	}

	echo '
			</tbody>
		</table>
	</dd>';
}
