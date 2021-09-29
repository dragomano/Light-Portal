<?php

function template_lp_basic_settings_above() {}

function template_lp_basic_settings_below()
{
	global $txt, $scripturl, $modSettings;

	// Frontpage mode toggle
	$frontpage_mode_toggle = array('lp_frontpage_title', 'lp_frontpage_alias', 'lp_frontpage_categories', 'lp_frontpage_boards', 'lp_frontpage_pages', 'lp_frontpage_topics', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_time_format', 'lp_frontpage_custom_time_format', 'lp_show_teaser', 'lp_show_author', 'lp_show_num_views_and_comments', 'lp_frontpage_order_by_num_replies', 'lp_frontpage_article_sorting', 'lp_frontpage_layout', 'lp_frontpage_num_columns', 'lp_num_items_per_page');

	$frontpage_mode_toggle_dt = [];
	foreach ($frontpage_mode_toggle as $item) {
		$frontpage_mode_toggle_dt[] = 'setting_' . $item;
	}

	$frontpage_alias_toggle = array('lp_frontpage_title', 'lp_frontpage_categories', 'lp_frontpage_boards', 'lp_frontpage_pages', 'lp_frontpage_topics', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_time_format', 'lp_frontpage_custom_time_format', 'lp_show_teaser', 'lp_show_author', 'lp_show_num_views_and_comments','lp_frontpage_order_by_num_replies', 'lp_frontpage_article_sorting', 'lp_frontpage_layout', 'lp_frontpage_num_columns', 'lp_show_pagination', 'lp_use_simple_pagination', 'lp_num_items_per_page');

	$frontpage_alias_toggle_dt = [];
	foreach ($frontpage_alias_toggle as $item) {
		$frontpage_alias_toggle_dt[] = 'setting_' . $item;
	}

	echo '
	<script>
		function toggleFrontpageMode() {
			let front_mode = $("#lp_frontpage_mode").val();
			let change_mode = front_mode > 0;
			let board_selector = $(".board_selector").parent("dd");

			$("#lp_standalone_mode").attr("disabled", front_mode == 0);

			if (front_mode == 0) {
				$("#lp_standalone_mode").prop("checked", false);
			}

			$("#', implode(', #', $frontpage_mode_toggle), '").closest("dd").toggle(change_mode);
			$("#', implode(', #', $frontpage_mode_toggle_dt), '").closest("dt").toggle(change_mode);
			board_selector.toggle(change_mode);

			let allow_change_title = !["0", "chosen_page"].includes(front_mode);

			$("#', implode(', #', $frontpage_alias_toggle), '").closest("dd").toggle(allow_change_title);
			$("#', implode(', #', $frontpage_alias_toggle_dt), '").closest("dt").toggle(allow_change_title);
			board_selector.toggle(allow_change_title);

			let allow_change_alias = front_mode == "chosen_page";

			$("#lp_frontpage_alias").closest("dd").toggle(allow_change_alias);
			$("#setting_lp_frontpage_alias").closest("dt").toggle(allow_change_alias);

			let allow_change_chosen_topics = front_mode == "chosen_topics";

			$("#lp_frontpage_topics").closest("dd").toggle(allow_change_chosen_topics);
			$("#setting_lp_frontpage_topics").closest("dt").toggle(allow_change_chosen_topics);

			let allow_change_chosen_pages = front_mode == "chosen_pages";

			$("#lp_frontpage_pages").closest("dd").toggle(allow_change_chosen_pages);
			$("#setting_lp_frontpage_pages").closest("dt").toggle(allow_change_chosen_pages);

			if (["chosen_topics", "all_pages", "chosen_pages"].includes(front_mode)) {
				let boards = $("#setting_lp_frontpage_boards").closest("dt");

				boards.hide();
				boards.next("dd").hide();
			}

			if (["all_topics", "chosen_topics", "chosen_boards", "chosen_pages"].includes(front_mode)) {
				let categories = $("#setting_lp_frontpage_categories").closest("dt");

				categories.hide();
				categories.next("dd").hide();
			}
		};

		toggleFrontpageMode();

		$("#lp_frontpage_mode").on("change", function () {
			toggleFrontpageMode();
			toggleTimeFormat();
		});';

	// Time format toggle
	echo '
		function toggleTimeFormat() {
			let change_mode = $("#lp_frontpage_time_format").val() == 2;

			$("#lp_frontpage_custom_time_format").closest("dd").toggle(change_mode);
			$("#setting_lp_frontpage_custom_time_format").closest("dt").toggle(change_mode);
		};

		toggleTimeFormat();

		$("#lp_frontpage_time_format").on("change", function () {
			toggleTimeFormat()
		});';

	// Standalone mode toggle
	$standalone_mode_toggle = array('lp_standalone_url', 'lp_standalone_mode_disabled_actions');

	$standalone_mode_toggle_dt = [];
	foreach ($standalone_mode_toggle as $item) {
		$standalone_mode_toggle_dt[] = 'setting_' . $item;
	}

	echo '
		function toggleStandaloneMode() {
			let change_mode = $("#lp_standalone_mode").prop("checked");

			$("#', implode(', #', $standalone_mode_toggle), '").closest("dd").toggle(change_mode);
			$("#', implode(', #', $standalone_mode_toggle_dt), '").closest("dt").toggle(change_mode);
		};

		toggleStandaloneMode();

		$("#lp_standalone_mode").on("click", function () {
			toggleStandaloneMode()
		});';

	// Alias select
	echo '
		let frontpageAlias = document.getElementById("lp_frontpage_alias");
		if (frontpageAlias) {
			let aliasSelect = new SlimSelect({
				select: frontpageAlias,
				ajax: function (search, callback) {
					if (search.length < 3) {
						callback("', sprintf($txt['lp_min_search_length'], 3), '")
						return
					}

					fetch("', $scripturl, '?action=admin;area=lp_settings;sa=basic;alias_list", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							search
						})
					})
					.then(response => response.json())
					.then(function (json) {
						let data = [];
						for (let i = 0; i < json.length; i++) {
							data.push({text: json[i].text})
						}

						callback(data)
					})
					.catch(function (error) {
						callback(false)
					})
				},
				hideSelectedOption: true,
				searchingText: "', $txt['search'], '...",
				searchText: "', $txt['no_matches'], '",
				searchPlaceholder: "home",
				searchHighlight: true,
				showContent: "down"
			});';

	if (!empty($modSettings['lp_frontpage_alias'])) {
		echo '
			aliasSelect.setData([{value: "', $modSettings['lp_frontpage_alias'], '", text: "', $modSettings['lp_frontpage_alias'], '"}]);
			aliasSelect.set(', JavaScriptEscape($modSettings['lp_frontpage_alias']), ');';
	}

	echo '
		}
	</script>';
}

function template_lp_extra_settings_above() {}

function template_lp_extra_settings_below()
{
	// Show comment block toggle
	$show_comment_block_toggle = array('lp_disabled_bbc_in_comments', 'lp_time_to_change_comments', 'lp_num_comments_per_page');

	$show_comment_block_toggle_dt = [];
	foreach ($show_comment_block_toggle as $item) {
		$show_comment_block_toggle_dt[] = 'setting_' . $item;
	}

	echo '
	<script>
		function toggleShowCommentBlock() {
			let change_mode = $("#lp_show_comment_block").val() != "none";

			$("#', implode(', #', $show_comment_block_toggle), '").closest("dd").toggle(change_mode);
			$("#', implode(', #', $show_comment_block_toggle_dt), '").closest("dt").toggle(change_mode);

			if (change_mode && $("#lp_show_comment_block").val() != "default") {
				$("#lp_disabled_bbc_in_comments").closest("dd").hide();
				$("#setting_lp_disabled_bbc_in_comments").closest("dt").hide();
				$("#lp_time_to_change_comments").closest("dd").hide();
				$("#setting_lp_time_to_change_comments").closest("dt").hide();
				$("#lp_num_comments_per_page").closest("dd").hide();
				$("#setting_lp_num_comments_per_page").closest("dt").hide();
			}
		};

		toggleShowCommentBlock();

		$("#lp_show_comment_block").on("click", function () {
			toggleShowCommentBlock()
		});
	</script>';
}

/**
 * Callback template for selecting allowed tags in comments
 *
 * Callback-шаблон для выбора допустимых тегов в комментариях
 *
 * @return void
 */
function template_callback_disabled_bbc_in_comments()
{
	global $txt, $scripturl, $context;

	echo '
	<dt>
		<a id="setting_lp_disabled_bbc_in_comments"></a>
		<span>
			<label for="lp_disabled_bbc_in_comments">', $txt['lp_disabled_bbc_in_comments'], '</label>
		</span>
		<div class="smalltext">
			', sprintf($txt['lp_disabled_bbc_in_comments_subtext'], $scripturl . '?action=admin;area=featuresettings;sa=bbc;' . $context['session_var'] . '=' . $context['session_id'] . '#disabledBBC'), '
		</div>
	</dt>
	<dd>
		<fieldset x-data>
			<select id="lp_disabled_bbc_in_comments" name="lp_disabled_bbc_in_comments_enabledTags[]" multiple>';

	foreach ($context['bbc_sections']['columns'] as $bbcColumn) {
		foreach ($bbcColumn as $bbcTag) {
			echo '
					<option id="tag_lp_disabled_bbc_in_comments_', $bbcTag, '" value="', $bbcTag, '"', !in_array($bbcTag, $context['bbc_sections']['disabled']) ? ' selected' : '', '>
						', $bbcTag, '
					</option>';
		}
	}

	echo '
			</select>
			<input type="checkbox" id="bbc_lp_disabled_bbc_in_comments_select_all" @click="selectDeselectAll($event.target, \'lp_disabled_bbc_in_comments\')"', $context['bbc_sections']['all_selected'] ? ' selected' : '', '> <label for="bbc_lp_disabled_bbc_in_comments_select_all"><em>', $txt['enabled_bbc_select_all'], '</em></label>
			<script>
				new SlimSelect({
					select: "#lp_disabled_bbc_in_comments",
					hideSelectedOption: true,
					placeholder: "', $txt['enabled_bbc_select'], '",
					searchText: "', $txt['no_matches'], '",
					searchPlaceholder: "', $txt['search'], '",
					searchHighlight: true,
					closeOnSelect: false,
					showContent: "down"
				});
				function selectDeselectAll(elem, select) {
					if (elem.checked) {
						let test = document.querySelectorAll("#" + select + " option");
						test = Array.from(test).map(el => el.value);
						eval(`${select}Select`).set(test);
					} else {
						eval(`${select}Select`).set([]);
					}
				}
			</script>
		</fieldset>
	</dd>';
}

/**
 * Callback template for selecting categories-sources of articles
 *
 * Callback-шаблон для выбора рубрик-источников статей
 *
 * @return void
 */
function template_callback_frontpage_categories()
{
	global $txt, $context;

	echo '
	<dt>
		<a id="setting_lp_frontpage_categories"></a>
		<span><label for="lp_frontpage_categories">', $txt['lp_frontpage_categories'], '</label></span>
	</dt>
	<dd>
		<a href="#" class="board_selector">[ ', $txt['lp_select_categories_from_list'], ' ]</a>
		<fieldset>
			<legend class="board_selector">
				<a href="#">', $txt['lp_select_categories_from_list'], '</a>
			</legend>
			<ul>';

	foreach ($context['lp_all_categories'] as $id => $cat) {
		echo '
				<li>
					<label>
						<input type="checkbox" name="lp_frontpage_categories[', $id, ']" value="1"', in_array($id, $context['lp_frontpage_categories']) ? ' checked' : '', '> ', $cat['name'], '
					</label>
				</li>';
	}

	echo '
				<li>
					<input type="checkbox" onclick="invertAll(this, this.form, \'lp_frontpage_categories[\');">
					<span>', $txt['check_all'], '</span>
				</li>
			</ul>
		</fieldset>
	</dd>';
}

/**
 * Template for the category management page
 *
 * Шаблон страницы управления рубриками
 *
 * @return void
 */
function template_lp_category_settings()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_categories_manage'], '</h3>
	</div>
	<div class="windowbg noup">
		<dl class="lp_categories settings" x-data>
			<dt>
				<form accept-charset="', $context['character_set'], '">
					<table class="table_grid">
						<tbody id="lp_categories" x-ref="category_list">';

	foreach ($context['lp_categories'] as $id => $cat)
		show_single_category($id, $cat);

	echo '
						</tbody>
					</table>
				</form>
			</dt>
			<dd>
				<div class="roundframe">
					<div class="noticebox">
						<form
							id="add_category_form"
							name="add_category_form"
							accept-charset="', $context['character_set'], '"
							@submit.prevent="category.add($refs)"
						>
							<input
								name="new_category_name"
								type="text"
								placeholder="', $txt['title'], '"
								maxlength="255"
								form="add_category_form"
								required
								x-ref="cat_name"
							>
							<textarea
								placeholder="', $txt['lp_categories_desc'], '"
								maxlength="255"
								x-ref="cat_desc"
							></textarea>
						</form>
					</div>
					<div class="centertext">
						<input form="add_category_form" class="button" type="submit" value="', $txt['lp_categories_add'], '">
					</div>
				</div>
			</dd>
		</dl>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
	<script>
		const category = new Category();
		new Sortable(document.getElementById("lp_categories"), {
			handle: ".handle",
			animation: 150,
			onSort: e => category.updatePriority(e)
		});
	</script>';
}

/**
 * Single category template
 *
 * Шаблон одиночной рубрики
 *
 * @param int $id
 * @param array $cat
 * @return void
 */
function show_single_category(int $id, array $cat)
{
	global $txt;

	echo '
	<tr class="windowbg" x-data data-id="', $id, '">
		<td class="centertext handle"><i class="fas fa-arrows-alt"></i></td>
		<td>
			<span class="floatright">
				<span @click="category.remove($el)" title="', $txt['remove'], '" class="error">&times;</span>
			</span>
			<label for="category_name', $id, '" class="handle">', $txt['lp_category'], '</label>
			<input
				id="category_name', $id, '"
				name="category_name[', $id, ']"
				type="text"
				value="', $cat['name'], '"
				maxlength="255"
				@change="category.updateName($el, $event.target)"
			>
			<br>
			<textarea
				rows="2"
				placeholder="', $txt['lp_page_description'], '"
				maxlength="255"
				@change="category.updateDescription($el, $event.target.value)"
			>', $cat['desc'], '</textarea>
		</td>
	</tr>';
}

/**
 * Callback template to configure panel layouts
 *
 * Callback-шаблон для настройки макета панелей
 *
 * @return void
 */
function template_callback_panel_layout()
{
	global $txt, $modSettings, $context;

	echo '
		</dl>
	</div>
	<div class="windowbg">', $txt['lp_panel_layout_preview'], '</div>
	<div class="generic_list_wrapper">
		<div class="centertext', !empty($modSettings['lp_swap_header_footer']) ? ' column-reverse' : '', '">
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_header_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['header'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_header_panel_width">col-xs-</label>
						<select id="lp_header_panel_width" name="lp_header_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_header_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
			<div class="row', !empty($modSettings['lp_swap_left_right']) ? ' reverse' : '', '">
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_left_panel_width']['md'], ' col-lg-', $context['lp_left_panel_width']['lg'], ' col-xl-', $context['lp_left_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['left'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[md]">col-md-</label>
								<select id="lp_left_panel_width[md]" name="lp_left_panel_width[md]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[lg]">col-lg-</label>
								<select id="lp_left_panel_width[lg]" name="lp_left_panel_width[lg]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[xl]">col-xl-</label>
								<select id="lp_left_panel_width[xl]" name="lp_left_panel_width[xl]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_left_panel_sticky">', $txt['lp_left_panel_sticky'], '</label>
						<input type="checkbox" id="lp_left_panel_sticky" name="lp_left_panel_sticky"', !empty($modSettings['lp_left_panel_sticky']) ? ' checked="checked"' : '', '>
					</div>
				</div>
				<div class="col-xs">
					<div class="windowbg', !empty($modSettings['lp_swap_top_bottom']) ? ' column-reverse' : '', '">
						<strong>col-xs (auto)</strong>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $context['lp_block_placements']['top'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="descbox alternative">
									<strong><i class="far fa-newspaper fa-2x"></i></i></strong>
									<div>', $txt['lp_content'], '</div>
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $context['lp_block_placements']['bottom'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_right_panel_width']['md'], ' col-lg-', $context['lp_right_panel_width']['lg'], ' col-xl-', $context['lp_right_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['right'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[md]">col-md-</label>
								<select id="lp_right_panel_width[md]" name="lp_right_panel_width[md]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[lg]">col-lg-</label>
								<select id="lp_right_panel_width[lg]" name="lp_right_panel_width[lg]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[xl]">col-xl-</label>
								<select id="lp_right_panel_width[xl]" name="lp_right_panel_width[xl]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_right_panel_sticky">', $txt['lp_right_panel_sticky'], '</label>
						<input type="checkbox" id="lp_right_panel_sticky" name="lp_right_panel_sticky"', !empty($modSettings['lp_right_panel_sticky']) ? ' checked="checked"' : '', '>
					</div>
				</div>
			</div>
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_footer_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $context['lp_block_placements']['footer'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_footer_panel_width">col-xs-</label>
						<select id="lp_footer_panel_width" name="lp_footer_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_footer_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br>';
}

/**
 * Callback template for selecting the direction of blocks inside panels
 *
 * Callback-шаблон для выбора направления блоков внутри панелей
 *
 * @return void
 */
function template_callback_panel_direction()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_panel_direction'], '</h3>
	</div>
	<div class="information">', $txt['lp_panel_direction_note'], '</div>
	<div class="generic_list_wrapper">
		<table class="table_grid centertext">
			<tbody>';

	foreach ($context['lp_block_placements'] as $key => $label) {
		echo '
				<tr class="windowbg">
					<td>
						<label for="lp_panel_direction_' . $key . '">', $label, '</label>
					</td>
					<td>
						<select id="lp_panel_direction[' . $key . ']" name="lp_panel_direction[' . $key . ']">';

		foreach ($txt['lp_panel_direction_set'] as $value => $direction) {
			echo '
							<option value="', $value, '"', !empty($context['lp_panel_direction'][$key]) && $context['lp_panel_direction'][$key] == $value ? ' selected' : '', '>', $direction, '</option>';
		}

		echo '
						</select>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	<dl class="settings">';
}

/**
 * Display settings on multiple tabs
 *
 * Вывод настроек на нескольких вкладках
 *
 * @param array $fields
 * @param string $tab
 * @return void
 */
function template_post_tab(array $fields, string $tab = 'content')
{
	global $context;

	$fields['subject'] = ['no'];

	foreach ($fields as $pfid => $pf) {
		if (empty($pf['input']['tab']))
			$pf['input']['tab'] = 'tuning';

		if ($pf['input']['tab'] != $tab)
			$fields[$pfid] = ['no'];
	}

	$context['posting_fields'] = $fields;

	LoadTemplate('Post');

	template_post_header();
}
