<?php

function callback_main_menu_table()
{
	global $txt, $scripturl, $context;

	echo '
	<table class="main_menu_list centertext table_grid">
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
	<table class="main_menu_list centertext table_grid">
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

	<input type="hidden" name="items">

	<table class="main_menu_list centertext" x-data="handleItems()">
		<caption class="title_bar">', $txt['lp_main_menu']['additional_items'], '</caption>
		<tbody>
			<template x-for="(item, index) in items" :key="index">
				<tr class="popup_content">
					<td>
						<table class="plugin_options table_grid">
							<tbody>
								<tr class="windowbg">
									<td style="width: 200px"><strong>', $txt['lp_main_menu']['unicode'], '</strong></td>
									<td><input type="text" x-model="item.unicode" name="unicode[]" placeholder="f007"></td>
									<td style="width: 140px">
										<button type="button" class="button" @click="remove(index)">
											<span class="main_icons delete"></span> <span class="hidden-xs">', $txt['remove'], '</span>
										</button>
									</td>
								</tr>
								<tr class="windowbg">
									<td><strong>', $txt['url'], '</strong></td>
									<td colspan="2">
										<input type="url" x-model="item.url" name="url[]" placeholder="', $scripturl, '?page=home" required>
									</td>
								</tr>
								<tr class="windowbg">
									<td><strong>', $txt['lp_main_menu']['menu_item'], '</strong></td>
									<td colspan="2">
										<table class="table_grid">
											<tbody>';

	foreach ($context['languages'] as $lang) {
		echo '
												<tr class="windowbg">
													<td><strong>', $lang['name'], '</strong></td>
													<td>
														<input type="text" x-model="item.langs[\'', $lang['filename'], '\']" name="langs[', $lang['filename'], '][]"', in_array($lang['filename'], [$context['user']['language'], 'english']) ? ' required' : '', ' placeholder="', $lang['filename'], '">
													</td>
												</tr>';
	}

	echo '
											</tbody>
										</table>
									</td>
								</tr>
								<tr class="windowbg">
									<td><strong>', $txt['lp_main_menu']['access'], '</strong></td>
									<td colspan="2">
										<select x-model="item.access" name="access[]" style="width: 100%">';

	foreach ($txt['lp_permissions'] as $id => $perm) {
		echo '
											<option value="', $id, '">', $perm, '</option>';
	}

	echo '
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</template>
		</tbody>
		<tfoot>
			<tr>
				<td>
					<button type="button" class="button" @click="add()"><span class="main_icons plus"></span> ', $txt['lp_main_menu']['add'], '</button>
				</td>
			</tr>
		</tfoot>
	</table>
	<script>
		function handleItems() {
			return {
				items: ', json_encode($context['lp_main_menu_addon_items']), ',
				add() {
					this.items.push({
						url: "",
						unicode: "",
						langs: {},
						access: 3
					});
				},
				remove(index) {
					this.items.splice(index, 1);
				}
			}
		}
	</script>';
}
