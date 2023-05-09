<?php

function swiper_images(): string
{
	global $txt;

	return '
	<div x-data="handleImages()">
		<table class="add_option centertext table_grid">
			<tbody>
				<template x-for="(image, index) in images" :key="index">
					<tr class="windowbg">
						<td>
							<table class="plugin_options table_grid">
								<tbody>
									<tr class="windowbg">
										<td style="width: 90px"><img alt="*" :src="image.link"></td>
										<td>
											<input type="url" x-model="image.link" name="image_link[]" required placeholder="' . $txt['lp_swiper']['link_placeholder'] . '">
										</td>
										<td style="width: 140px">
											<button type="button" class="button" @click="removeImage(index)" style="width: 100%">
												<span class="main_icons delete"></span> ' . $txt['remove'] . '
											</button>
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="3">
											<input type="text" x-model="image.title" name="image_title[]" maxlength="255" placeholder="' . $txt['lp_swiper']['title_placeholder'] . '">
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</template>
			</tbody>
		</table>
		<button type="button" class="button floatnone" @click="addNewImage()"><span class="main_icons plus"></span> ' . $txt['lp_swiper']['image_add'] . '</button>
	</div>';
}
