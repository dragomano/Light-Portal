<?php

use Bugo\Compat\Lang;

function callback_site_list_table(): void
{
	echo /** @lang text */ '
	<input type="hidden" name="urls">
	<table class="table_grid centertext" x-data="handleSites()">
		<tbody>
			<template x-for="(site, index) in sites" :key="index">
				<tr class="popup_content">
					<td>
						<table class="plugin_options table_grid">
							<tbody>
								<tr class="windowbg">
									<td rowspan="3"><img :src="site.image" :alt="index + 1"></td>
									<td>
										<input type="url" x-model="site.url" name="url[]" placeholder="' . Lang::$txt['website'] . '" required>
									</td>
									<td style="width: 120px">
										<button type="button" class="button" @click="remove(index)">
											<span class="main_icons delete"></span> <span class="hidden-xs">' . Lang::$txt['remove'] . '</span>
										</button>
									</td>
								</tr>
								<tr class="windowbg">
									<td colspan="2">
										<input type="url" x-model="site.image" name="image[]" placeholder="' . Lang::$txt['lp_site_list']['image'] . '">
									</td>
								</tr>
								<tr class="windowbg">
									<td colspan="2">
										<input x-model="site.title" name="title[]" placeholder="' . Lang::$txt['title'] . '" style="width: 100%">
									</td>
								</tr>
								<tr class="windowbg">
									<td colspan="3">
										<textarea x-model="site.desc" name="desc[]" placeholder="' . Lang::$txt['custom_edit_desc'] . '"></textarea>
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
					<button type="button" class="button" @click="add()" style="width: 100%"><span class="main_icons plus"></span> ' . Lang::$txt['lp_site_list']['add'] . '</button>
				</td>
			</tr>
		</tfoot>
	</table>';
}

function site_list_handle_func(array $urls): string
{
	$js = '
	function handleSites() {
		return {
			sites: [';

	foreach ($urls as $url => $data) {
		$js .= '
				{
					url: "' . $url . '",
					image: "' . $data[0] . '",
					title: "' . $data[1] . '",
					desc: "' . $data[2] . '"
				},';
	}

	return $js . /** @lang text */ '
			],
			add() {
				this.sites.push({
					url: "",
					image: "",
					title: "",
					desc: ""
				});
			},
			remove(index) {
				this.sites.splice(index, 1);
			}
		}
	}';
}