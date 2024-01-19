<?php

use Bugo\LightPortal\Utils\{Lang, Theme, Utils};

function swiper_images(): string
{
	return /** @lang text */ '
	<div x-data="handleImages()">
		<table class="add_option centertext table_grid">
			<tbody>
				<template x-for="(image, index) in images" :key="index">
					<tr class="sort_table windowbg">
						<td style="cursor: move">
							<table class="plugin_options table_grid">
								<tbody>
									<tr class="windowbg">
										<td style="width: 90px"><img alt="*" :src="image.link"></td>
										<td style="display: flex; flex-direction: column; gap: 10px">
											<div>
												' . Utils::$context['lp_icon_set']['arrows'] . '
												<button type="button" class="button" @click="removeImage(index)">
													<span class="main_icons delete"></span> ' . Lang::$txt['remove'] . '
												</button>
											</div>
											<input type="url" x-model="image.link" name="image_link[]" required placeholder="' . Lang::$txt['lp_swiper']['link_placeholder'] . '">
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="2">
											<input type="text" x-model="image.title" name="image_title[]" maxlength="255" placeholder="' . Lang::$txt['lp_swiper']['title_placeholder'] . '">
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
		<button type="button" class="button floatnone" @click="addImage()"><span class="main_icons plus"></span> ' . Lang::$txt['lp_swiper']['image_add'] . '</button>
	</div>
	<script src="' . Theme::$current->settings['default_theme_url'] . '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		document.addEventListener("alpine:initialized", () => {
			const chartDatasets = document.querySelectorAll(".sort_table");
			chartDatasets.forEach(function (el) {
				Sortable.create(el, {
					group: "images",
					animation: 500,
				});
			});
		});

		function handleImages() {
			return {
				images: ' . (Utils::$context['lp_block']['options']['images'] ?: '[]') . ',
				addImage() {
					this.images.push({
						link: "",
						title: ""
					})
				},
				removeImage(index) {
					this.images.splice(index, 1)
				}
			}
		}
	</script>';
}
