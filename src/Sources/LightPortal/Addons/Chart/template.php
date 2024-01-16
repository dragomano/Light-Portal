<?php

function chart_template(): string
{
	global $txt, $settings, $context;

	return /** @lang text */ '
	<div x-data="handleDataSets()">
		<table class="add_option centertext table_grid">
			<tbody>
				<template x-for="(dataset, index) in datasets" :key="index">
					<tr class="sort_table windowbg">
						<td>
							<table class="table_grid">
								<tbody>
									<tr class="windowbg">
										<td colspan="2" style="cursor: move">
											<button
												type="button"
												class="button"
												@click="removeSet(index)"
											>
												<span class="main_icons delete"></span> ' . $txt['remove'] . '
											</button>
											<input
												x-model="dataset.label"
												type="text"
												name="set_label[]"
												placeholder="' . $txt['lp_chart']['set_label'] . '"
												required
											>
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="2">
											<input
												x-model="dataset.data"
												type="text"
												name="set_data[]"
												placeholder="' . $txt['lp_chart']['datasets_placeholder'] . '"
												required
											>
										</td>
									</tr>
									<tr class="windowbg">
										<td x-id="[\'select\']">
											<label :for="$id(\'select\')">' . $txt['lp_chart']['type'] . '</label>
											<select x-model="dataset.type" name="set_type[]" :id="$id(\'select\')">
												<template x-for="(value, key) in charts" :key="key">
													<option
														:value="key"
														:selected="key === dataset.type"
														x-text="value"
													></option>
												</template>
											</select>
										</td>
										<td x-id="[\'input-number\']">
											<label :for="$id(\'input-number\')">' . $txt['lp_chart']['border_width'] . '</label>
											<input
												type="number"
												min="0"
												x-model="dataset.borderWidth"
												:id="$id(\'input-number\')"
											>
											<input
												x-model="dataset.borderWidth"
												type="text"
												name="set_borderWidth[]"
												style="display: none"
											>
										</td>
									</tr>
									<template x-if="!usePalette">
										<tr class="windowbg">
											<td x-id="[\'input-color\']">
												<label :for="$id(\'input-color\')">' . $txt['lp_chart']['background_color'] . '</label>
												<input
													type="color"
													x-model="dataset.backgroundColor"
													:id="$id(\'input-color\')"
												>
												<input
													x-model="dataset.backgroundColor"
													type="text"
													name="set_backgroundColor[]"
													style="display: none"
												>
											</td>
											<td x-id="[\'input-color\']">
												<label :for="$id(\'input-color\')">' . $txt['lp_chart']['border_color'] . '</label>
												<input
													type="color"
													x-model="dataset.borderColor"
													:id="$id(\'input-color\')"
												>
												<input
													x-model="dataset.borderColor"
													type="text"
													name="set_borderColor[]"
													style="display: none"
												>
											</td>
										</tr>
									</template>
								</tbody>
							</table>
						</td>
					<tr>
				</template>
			</tbody>
		</table>
		<button type="button" class="button floatnone" @click="addSet()">
			<span class="main_icons plus"></span> ' . $txt['lp_chart']['set_add'] . '
		</button>
	</div>
	<script src="' . $settings['default_theme_url'] . '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		document.addEventListener("alpine:initialized", () => {
			const chartDatasets = document.querySelectorAll(".sort_table");
			chartDatasets.forEach(function (el) {
				Sortable.create(el, {
					group: "charts",
					animation: 500,
				});
			});
		});

		function handleDataSets() {
			return {
				datasets: ' . ($context['lp_block']['options']['datasets'] ?: '[]') . ',
				charts: ' . json_encode($context['lp_chart_types']) . ',
				type: "' . $context['lp_block']['options']['chart_type'] . '",
				usePalette: ' . (empty($context['lp_block']['options']['default_palette']) ? 'false' : 'true') . ',
				addSet() {
					this.datasets.push(this.usePalette ? {
						label: "",
						data: [],
						type: this.type,
						borderWidth: 2
					} : {
						label: "",
						data: [],
						type: this.type,
						backgroundColor: "#9BD0F5",
						borderColor: "#36A2EB",
						borderWidth: 2
					})
				},
				removeSet(index) {
					this.datasets.splice(index, 1)
				}
			}
		}
	</script>';
}
