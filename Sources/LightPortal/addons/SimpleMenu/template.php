<?php

function simple_menu_items()
{
	global $txt;

	return '
	<div x-data="handleItems()">
		<table class="add_option centertext table_grid">
			<tbody>
				<template x-for="(item, index) in items" :key="index">
					<tr class="windowbg">
						<td>
							<table class="plugin_options table_grid">
								<tbody>
									<tr class="windowbg">
										<td x-text="index + 1" rowspan="2"></td>
										<td>
											<input type="text" x-model="item.name" name="item_name[]" maxlength="255" placeholder="' . $txt['lp_simple_menu']['name_placeholder'] . '">
										</td>
										<td style="width: 140px">
											<button type="button" class="button" @click="removeItem(index)" style="width: 100%">
												<span class="main_icons delete"></span> ' . $txt['remove'] . '
											</button>
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="2">
											<input type="text" x-model="item.link" name="item_link[]" placeholder="' . $txt['lp_simple_menu']['link_placeholder'] . '">
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
		<button type="button" class="button floatnone" @click="addNewItem()"><span class="main_icons plus"></span> ' . $txt['lp_simple_menu']['item_add'] . '</button>
	</div>';
}
