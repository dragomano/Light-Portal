<?php

use Bugo\Compat\{Lang, Utils};

function callback_main_menu_table(): void
{
	echo '
	<table class="table_grid centertext">
		<caption class="title_bar">', Lang::$txt['lp_portal'], '</caption>
		<tbody>
			<tr class="popup_content">
				<td class="descbox" colspan="2">
					<strong>', Lang::$txt['lp_main_menu']['menu_item'], '</strong>
				</td>
			</tr>';

	$i = 0;
	foreach (Utils::$context['lp_languages'] as $lang) {
		echo '
			<tr class="bg ', $i++ % 2 === 0 ? 'odd' : 'even', '">
				<td><strong>', $lang['name'], '</strong></td>
				<td>
					<input
						type="text"
						name="portal_item_langs[', $lang['filename'], ']"
						placeholder="', $lang['filename'], '"
						value="', Utils::$context['lp_main_menu_addon_portal_langs'][$lang['filename']] ?? '', '"
					>
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>
	<table class="table_grid centertext">
		<caption class="title_bar">', Lang::$txt['lp_forum'], '</caption>
		<tbody>
			<tr class="popup_content">
				<td class="descbox" colspan="2">
					<strong>', Lang::$txt['lp_main_menu']['menu_item'], '</strong>
				</td>
			</tr>';

	$i = 0;
	foreach (Utils::$context['lp_languages'] as $lang) {
		echo '
			<tr class="bg ', $i++ % 2 === 0 ? 'odd' : 'even', '">
				<td><strong>', $lang['name'], '</strong></td>
				<td>
					<input
						type="text"
						name="forum_item_langs[', $lang['filename'], ']"
						placeholder="', $lang['filename'], '"
						value="', Utils::$context['lp_main_menu_addon_forum_langs'][$lang['filename']] ?? '', '"
					>
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>

	<input type="hidden" name="items">';
}
