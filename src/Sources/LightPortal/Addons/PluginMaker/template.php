<?php

function template_plugin_post(): void
{
	global $context, $txt;

	if (! empty($context['lp_addon_dir_is_not_writable'])) {
		echo '
	<div class="errorbox">', $context['lp_addon_dir_is_not_writable'], '</div>';
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>';

	if (! empty($context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach ($context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = $context['posting_fields'];

	echo '
	<form
		id="lp_post"
		action="', $context['canonical_url'], '"
		method="post"
		accept-charset="', $context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ tab: window.location.hash ? window.location.hash.substring(1) : \'english\' }"
	>
		<div class="roundframe noup">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="common">', $context['lp_icon_set']['content'], $txt['lp_plugin_maker']['tab_content'], '</div>
					<div class="bg odd" data-tab="copyright">', $context['lp_icon_set']['copyright'], $txt['lp_plugin_maker']['tab_copyrights'], '</div>
					<div class="bg odd" data-tab="settings">', $context['lp_icon_set']['cog_spin'], $txt['settings'], '</div>
					<div class="bg odd" data-tab="tuning">', $context['lp_icon_set']['tools'], $txt['lp_plugin_maker']['tab_tuning'], '</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">', template_post_tab($fields), '</section>
					<section class="bg even" data-content="copyright">', template_post_tab($fields, 'copyrights'), '</section>
					<section class="bg even" data-content="settings">
						<table class="add_option centertext" x-data="plugin.handleOptions()">
							<tbody>
								<template x-for="(option, index) in options" :key="index">
									<tr class="windowbg">
										<td colspan="4">
											<table class="plugin_options table_grid">
												<thead>
													<tr class="title_bar">
														<th>#</th>
														<th colspan="3">', $txt['lp_plugin_maker']['option_name'], '</th>
													</tr>
												</thead>
												<tbody>
													<tr class="windowbg">
														<td colspan="4">
															<div class="infobox">', $txt['lp_plugin_maker']['option_desc'], '</div>
														</td>
													</tr>
													<tr class="windowbg">
														<td x-text="index + 1"></td>
														<td colspan="2">
															<input
																type="text"
																x-model="option.name"
																name="option_name[]"
																pattern="^[a-z][a-z_]+$"
																maxlength="100"
																placeholder="option_name"
																required
															>
														</td>
														<td>
															<button type="button" class="button" @click="removeOption(index)" style="width: 100%">
																<span class="main_icons delete"></span> ', $txt['remove'], '
															</button>
														</td>
													</tr>
													<tr class="windowbg" x-data="{ type_id: $id(\'option-type\'), default_id: $id(\'option-default\') }">
														<td>
															<label :for="type_id"><strong>', $txt['lp_plugin_maker']['option_type'], '</strong></label>
														</td>
														<td>
															<select x-model="option.type" name="option_type[]" :id="type_id">';

	foreach ($context['lp_plugin_option_types'] as $type => $name) {
		echo '
																<option value="', $type, '">', $name, '</option>';
	}

	echo '
															</select>
														</td>
														<td>
															<label :for="default_id"><strong>', $txt['lp_plugin_maker']['option_default_value'], '</strong></label>
														</td>
														<td>
															<template x-if="option.type == \'text\'">
																<input x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="option.type == \'url\'">
																<input type="url" x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="option.type == \'color\'">
																<input type="color" x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="option.type == \'int\'">
																<input type="number" min="0" step="1" x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="option.type == \'float\'">
																<input type="number" min="0" step="0.1" x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="option.type == \'check\'">
																<input type="checkbox" x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
															<template x-if="[\'multiselect\', \'select\'].includes(option.type)">
																<input x-model="option.default" :name="`option_defaults[${index}]`" :id="default_id">
															</template>
														</td>
													</tr>
													<template x-if="! [\'multiselect\', \'select\'].includes(option.type)">
														<tr class="windowbg" style="display: none">
															<td colspan="4">
																<input x-model="option.variants" name="option_variants[]">
															</td>
														</tr>
													</template>
													<template x-if="[\'multiselect\', \'select\'].includes(option.type)">
														<tr class="windowbg">
															<td colspan="1"><strong>', $txt['lp_plugin_maker']['option_variants'], '</strong></td>
															<td colspan="3">
																<input x-model="option.variants" name="option_variants[]" placeholder="', $txt['lp_plugin_maker']['option_variants_placeholder'], '">
															</td>
														</tr>
													</template>
													<tr class="windowbg">
														<td colspan="1"><strong>', $txt['lp_plugin_maker']['option_translations'], '</strong></td>
														<td colspan="3">
															<table class="table_grid">
																<tbody>';

	foreach ($context['languages'] as $lang) {
		echo '
																	<tr class="windowbg">
																		<td><strong>', $lang['name'], '</strong></td>
																		<td>
																			<input type="text" x-model="option.translations[\'', $lang['filename'], '\']" name="option_translations[', $lang['filename'], '][]"', $lang['filename'] === 'english' ? ' required' : '', ' placeholder="', $lang['filename'], '">
																		</td>
																	</tr>';
	}

		echo '
																</tbody>
															</table>
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
									<td colspan="4">
										<button type="button" class="button" @click="addNewOption()"><span class="main_icons plus"></span> ', $txt['lp_plugin_maker']['option_new'], '</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</section>
					<section class="bg even" data-content="tuning">', template_post_tab($fields, 'tuning'), '</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<div class="noticebox">', $txt['lp_plugin_maker']['add_info'], '</div>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="save" @click="plugin.post($root)">', $txt['save'], '</button>
			</div>
		</div>
	</form>

	<script>
		class PluginMaker extends PortalEntity {
			change(target) {
				if (target !== "block") {
					document.querySelector("dt.pf_icon").style.display = "none";
					document.querySelector("dd.pf_icon").style.display = "none";';

	foreach ($context['languages'] as $lang) {
		echo '
					document.querySelector("input[name=title_', $lang['filename'], ']").style.display = "none";';
	}

	echo '
				} else {
					document.querySelector("dt.pf_icon").style.display = "block";
					document.querySelector("dd.pf_icon").style.display = "block";';

	foreach ($context['languages'] as $lang) {
		echo '
					document.querySelector("input[name=title_', $lang['filename'], ']").style.display = "inline-block";';
	}

	echo '
				}
			}

			handleOptions() {
				return {
					options: ', json_encode($context['lp_plugin']['options']), ',
					addNewOption() {
						this.options.push({
							name: "",
							type: "text",
							default: "",
							variants: "",
							translations: {},
						});
					},
					removeOption(index) {
						this.options.splice(index, 1);
					}
				}
			}

			updateState(target, refs) {
				if (target) {
					refs.plugin_name.innerText = target;
				}
			}
		}

		const plugin = new PluginMaker();
		const tabs = new Tabs(".lp_tabs");
	</script>';
}
