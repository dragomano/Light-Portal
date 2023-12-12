<?php

function callback_main_menu_table(): void
{
	global $txt, $context;

	echo '
	<table class="table_grid centertext">
		<caption class="title_bar">', $txt['lp_portal'], '</caption>
		<tbody>
			<tr class="popup_content">
				<td class="descbox" colspan="2">
					<strong>', $txt['lp_main_menu']['menu_item'], '</strong>
				</td>
			</tr>';

	$i = 0;
	foreach ($context['languages'] as $lang) {
		echo '
			<tr class="bg ', $i++ % 2 === 0 ? 'odd' : 'even', '">
				<td><strong>', $lang['name'], '</strong></td>
				<td>
					<input type="text" name="portal_item_langs[', $lang['filename'], ']" placeholder="', $lang['filename'], '" value="', $context['lp_main_menu_addon_portal_langs'][$lang['filename']] ?? '', '">
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>
	<table class="table_grid centertext">
		<caption class="title_bar">', $txt['lp_forum'], '</caption>
		<tbody>
			<tr class="popup_content">
				<td class="descbox" colspan="2">
					<strong>', $txt['lp_main_menu']['menu_item'], '</strong>
				</td>
			</tr>';

	$i = 0;
	foreach ($context['languages'] as $lang) {
		echo '
			<tr class="bg ', $i++ % 2 === 0 ? 'odd' : 'even', '">
				<td><strong>', $lang['name'], '</strong></td>
				<td>
					<input type="text" name="forum_item_langs[', $lang['filename'], ']" placeholder="', $lang['filename'], '" value="', $context['lp_main_menu_addon_forum_langs'][$lang['filename']] ?? '', '">
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>

	<input type="hidden" name="items">';
}
