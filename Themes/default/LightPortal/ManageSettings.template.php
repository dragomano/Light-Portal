<?php

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
function template_category_settings()
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
	<dt style="width: 0"></dt>
	<dd style="width: 100%">
		<div class="infobox">', $txt['lp_panel_layout_preview'], '</div>
		<div class="centertext', !empty($modSettings['lp_swap_header_footer']) ? ' row column-reverse' : '', '">
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
					<div class="windowbg', !empty($modSettings['lp_swap_top_bottom']) ? ' row column-reverse' : '', '">
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
	</dd>';
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
	<dt style="width: 0"></dt>
	<dd style="width: 100%">
		<div class="infobox">', $txt['lp_panel_direction_note'], '</div>
		<table class="table_grid centertext">
			<thead>
				<tr class="title_bar">
					<th colspan="2">', $txt['lp_panel_direction'], '</th>
				</tr>
			</thead>
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
	</dd>';
}

/**
 * Callback template to display a summary of portal settings and active plugins
 *
 * Callback-шаблон для отображения сводки настроек портала и активных плагинов
 *
 * @return void
 */
function template_callback_misc()
{
	global $modSettings, $txt, $context;

	echo '
	<dt style="width: 0"></dt>
	<dd style="width: 100%">';

	$portal_settings = '';
	foreach ($modSettings as $key => $value) {
		if (strpos($key, 'lp_') === 0 && isset($txt[$key])) {
			$portal_settings .= $key . ' = ' . $value . PHP_EOL;
		}
	}

	echo '
		<details>
			<summary class="infobox">', $txt['lp_settings'], '</summary>
			<figure>', parse_bbc('[code]' . $portal_settings . '[/code]'), '</figure>
		</details>
		<details>
			<summary class="infobox">', $txt['lp_active_plugins'], '</summary>
			<figure>', parse_bbc('[code]' . implode(PHP_EOL, $context['lp_enabled_plugins']) . '[/code]'), '</figure>
		</details>
	</dd>';
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
