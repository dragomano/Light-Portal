<?php

use Bugo\LightPortal\Utils\{Lang, Theme, Utils};

function simple_menu_items(): string
{
	return /** @lang text */ '
	<div x-data="handleItems()">
		<table class="add_option centertext table_grid">
			<tbody>
				<template x-for="(item, index) in items" :key="index">
					<tr class="sort_table windowbg">
						<td>
							<table class="plugin_options table_grid">
								<tbody>
									<tr class="windowbg">
										<td x-text="index + 1" rowspan="2"></td>
										<td style="cursor: move">
											<button type="button" class="button" @click="removeItem(index)">
												<span class="main_icons delete"></span> ' . Lang::$txt['remove'] . '
											</button>
											<input type="text" x-model="item.name" name="item_name[]" maxlength="255" placeholder="' . Lang::$txt['lp_simple_menu']['name_placeholder'] . '" required>
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="2">
											<input type="text" x-model="item.link" name="item_link[]" placeholder="' . Lang::$txt['lp_simple_menu']['link_placeholder'] . '" required>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
		<button type="button" class="button floatnone" @click="addItem()"><span class="main_icons plus"></span> ' . Lang::$txt['lp_simple_menu']['item_add'] . '</button>
	</div>
	<script src="' . Theme::$current->settings['default_theme_url'] . '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		document.addEventListener("alpine:initialized", () => {
			const menuItems = document.querySelectorAll(".sort_table");
			menuItems.forEach(function (el) {
				Sortable.create(el, {
					group: "simple_menu",
					animation: 500,
				});
			});
		});

		function handleItems() {
			return {
				items: ' . (Utils::$context['lp_block']['options']['items'] ?: '[]') . ',
				addItem() {
					this.items.push({
						name: "",
						link: ""
					})
				},
				removeItem(index) {
					this.items.splice(index, 1)
				}
			}
		}
	</script>';
}
