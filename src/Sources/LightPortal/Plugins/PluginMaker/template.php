<?php

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\Icon;

function template_plugin_post(): void
{
	if (! empty(Utils::$context['lp_addon_dir_is_not_writable'])) {
		echo '
	<div class="errorbox">', Utils::$context['lp_addon_dir_is_not_writable'], '</div>';
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
	</div>';

	if (! empty(Utils::$context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach (Utils::$context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = Utils::$context['posting_fields'];

	echo '
	<form
		id="lp_post"
		action="', Utils::$context['form_action'], '"
		method="post"
		accept-charset="', Utils::$context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ tab: window.location.hash ? window.location.hash.substring(1) : \'english\' }"
	>
		<div class="roundframe noup">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="common">', Icon::get('content'), Lang::$txt['lp_plugin_maker']['tab_content'], '</div>
					<div class="bg odd" data-tab="copyright">', Icon::get('copyright'), Lang::$txt['lp_plugin_maker']['tab_copyrights'], '</div>
					<div class="bg odd" data-tab="settings">', Icon::get('cog_spin'), Lang::$txt['settings'], '</div>
					<div class="bg odd" data-tab="tuning">', Icon::get('tools'), Lang::$txt['lp_plugin_maker']['tab_tuning'], '</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">', template_portal_tab($fields), '</section>
					<section class="bg even" data-content="copyright">', template_portal_tab($fields, 'copyright'), '</section>
					<section class="bg even" data-content="settings">
						<table class="add_option centertext" x-data="plugin.handleOptions()">
							<tbody>
								<template x-for="(option, index) in options" :key="index">
									<tr class="windowbg">
										<td colspan="4">
											<table class="plugin_options table_grid">
												<thead>
													<tr class="title_bar">
														<th style="width: 20%"></th>
														<th colspan="3">
															<span>', Lang::$txt['lp_plugin_maker']['option'], '</span>
															<button type="button" class="button" @click="removeOption(index)">
																<span class="main_icons delete"></span> <span class="remove_label">', Lang::$txt['remove'], '</span>
															</button>
														</th>
													</tr>
												</thead>
												<tbody>
													<tr class="windowbg">
														<td colspan="4">
															<div class="infobox">', Lang::$txt['lp_plugin_maker']['option_desc'], '</div>
														</td>
													</tr>
													<tr class="windowbg" x-data="{ option_name: $id(\'option-name\') }">
														<td>
															<label :for="option_name">
																<strong>', Lang::$txt['lp_plugin_maker']['option_name'], '</strong>
															</label>
														</td>
														<td colspan="3">
															<input
																type="text"
																x-model="option.name"
																name="option_name[]"
																:id="option_name"
																pattern="^[a-z][a-z_]+$"
																maxlength="100"
																placeholder="option_name"
																required
															>
														</td>
													</tr>
													<tr class="windowbg" x-data="{ type_id: $id(\'option-type\'), default_id: $id(\'option-default\') }">
														<td>
															<label :for="type_id">
																<strong>', Lang::$txt['lp_plugin_maker']['option_type'], '</strong>
															</label>
														</td>
														<td>
															<select
																x-model="option.type"
																name="option_type[]"
																:id="type_id">';

	foreach (Utils::$context['lp_plugin_option_types'] as $type => $name) {
		echo '
																<option value="', $type, '">', $name, '</option>';
	}

	echo '
															</select>
														</td>
														<td>
															<label :for="default_id">
																<strong>', Lang::$txt['lp_plugin_maker']['option_default_value'], '</strong>
															</label>
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
															<template x-if="[\'int\', \'range\'].includes(option.type)">
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
															<template x-if="[\'title\', \'desc\', \'callback\'].includes(option.type)">
																<span>', Lang::$txt['no'], '</span>
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
															<td><strong>', Lang::$txt['lp_plugin_maker']['option_variants'], '</strong></td>
															<td colspan="3">
																<input x-model="option.variants" name="option_variants[]" placeholder="', Lang::$txt['lp_plugin_maker']['option_variants_placeholder'], '">
															</td>
														</tr>
													</template>
													<tr class="windowbg">
														<td><strong>', Lang::$txt['lp_plugin_maker']['option_translations'], '</strong></td>
														<td colspan="3">
															<table class="table_grid">
																<tbody>';

	foreach (Utils::$context['lp_languages'] as $key => $lang) {
		echo '
																	<tr class="windowbg">
																		<td>
																			<input type="text" x-model="option.translations[\'', $key, '\']" name="option_translations[', $key, '][]"', $key === 'english' ? ' required' : '', ' placeholder="', $lang['name'], '">
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
										<button type="button" class="button" @click="addNewOption()"><span class="main_icons plus"></span> ', Lang::$txt['lp_plugin_maker']['option_new'], '</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</section>
					<section class="bg even" data-content="tuning">', template_portal_tab($fields, Tab::TUNING), '</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<div class="noticebox">', Lang::$txt['lp_plugin_maker']['add_info'], '</div>
				<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
				<input type="hidden" name="seqnum" value="', Utils::$context['form_sequence_number'], '">
				<button type="submit" class="button" name="save" @click="plugin.post($root)">', Lang::$txt['save'], '</button>
			</div>
		</div>
	</form>

	<script>
		document.querySelector("#type").addEventListener("change", function() {
            if (! ["block", "ssi", "games"].some(type => this.value.includes(type))) {
				document.querySelector("dt.pf_icon").style.display = "none";
				document.querySelector("dd.pf_icon").style.display = "none";';

	foreach (array_keys(Utils::$context['lp_languages']) as $lang) {
		echo '
				document.querySelector("input[name=\"titles[' . $lang . ']\"]").style.display = "none";';
	}

	echo '
			} else {
				document.querySelector("dt.pf_icon").style.display = "block";
				document.querySelector("dd.pf_icon").style.display = "block";';

	foreach (array_keys(Utils::$context['lp_languages']) as $lang) {
		echo '
				document.querySelector("input[name=\"titles[' . $lang . ']\"]").style.display = "inline-block";';
	}

	echo '
			}
		});

		class PluginMaker extends PortalEntity {
			handleOptions() {
				return {
					options: ', json_encode(Utils::$context['lp_plugin']['options']), ',
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
		const tabs = new PortalTabs();
	</script>';
}
