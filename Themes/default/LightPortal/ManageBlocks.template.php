<?php

/**
 * Block management section template
 *
 * Шаблон раздела управления блоками
 *
 * @return void
 */
function template_manage_blocks()
{
	global $context, $scripturl, $txt;

	foreach ($context['lp_current_blocks'] as $placement => $blocks) {
		$block_group_type = in_array($placement, array('header', 'top', 'left', 'right', 'bottom', 'footer')) ? 'default' : 'additional';

		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatright">
				<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], ';placement=', $placement, '" x-data>
					<i class="fas fa-plus" @mouseover="block.toggleSpin($event.target)" @mouseout="block.toggleSpin($event.target)" title="', $txt['lp_blocks_add'], '"></i>
				</a>
			</span>
			', $context['lp_block_placements'][$placement] ?? $txt['not_applicable'], is_array($blocks) ? (' (' . count($blocks) . ')') : '', '
		</h3>
	</div>
	<table class="lp_', $block_group_type, '_blocks table_grid centertext">';

		if (is_array($blocks)) {
			echo '
		<thead>
			<tr class="title_bar">
				<th scope="col" class="icon">
					', $txt['custom_profile_icon'], '
				</th>
				<th scope="col" class="title">
					', $txt['lp_block_note'], ' / ', $txt['lp_title'], '
				</th>
				<th scope="col" class="type">
					', $txt['lp_block_type'], '
				</th>
				<th scope="col" class="areas">
					', $txt['lp_block_areas'], '
				</th>
				<th scope="col" class="priority">
					', $txt['lp_block_priority'], '
				</th>
				<th scope="col" class="status">
					', $txt['status'], '
				</th>
				<th scope="col" class="actions">
					', $txt['lp_actions'], '
				</th>
			</tr>
		</thead>
		<tbody data-placement="', $placement, '">';

			foreach ($blocks as $id => $data)
				show_block_entry($id, $data);
		} else {
			echo '
		<tbody data-placement="', $placement, '">
			<tr class="windowbg centertext" x-data>
				<td>', $txt['lp_no_items'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	echo '
	<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
	<script>
		const block = new Block(),
			defaultBlocks = document.querySelectorAll(".lp_default_blocks tbody"),
			additionalBlocks = document.querySelectorAll(".lp_additional_blocks tbody");

		defaultBlocks.forEach(function (el) {
			Sortable.create(el, {
				group: "default_blocks",
				animation: 500,
				handle: ".handle",
				draggable: "tr.windowbg",
				onSort: e => block.sort(e)
			});
		});

		additionalBlocks.forEach(function (el) {
			Sortable.create(el, {
				group: "additional_blocks",
				animation: 500,
				handle: ".handle",
				draggable: "tr.windowbg",
				onSort: e => block.sort(e)
			});
		});
	</script>';
}

/**
 * Adding a row with block parameters to the common table
 *
 * Добавление строчки с параметрами блока в общую таблицу
 *
 * @param int $id
 * @param array $data
 * @return void
 */
function show_block_entry(int $id, array $data)
{
	global $context, $language, $txt, $scripturl;

	if (empty($id) || empty($data))
		return;

	echo '
	<tr
		class="windowbg"
		data-id="', $id, '"
		x-data="{status: ' . (empty($data['status']) ? 'false' : 'true') . ', showContextMenu: false}"
		x-init="$watch(\'status\', value => block.toggleStatus($el))"
	>
		<td class="icon">
			', $data['icon'], '
		</td>
		<td class="title">
			', $title = $data['note'] ?: ($data['title'][$context['user']['language']] ?? $data['title']['english'] ?? $data['title'][$language] ?? '');

	if (empty($title))
		echo '<div class="hidden-sm hidden-md hidden-lg hidden-xl">', $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']], '</div>';

	echo '
		</td>
		<td class="type">
			', $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']], '
		</td>
		<td class="areas">
			', $data['areas'], '
		</td>
		<td class="priority">
			', $data['priority'], ' <span class="handle fas fa-sort fa-lg" title="', $txt['lp_action_move'], '"></span>
		</td>
		<td class="status">
			<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'', $txt['lp_action_off'], '\' : \'', $txt['lp_action_on'], '\'" @click.prevent="status = !status"></span>
		</td>
		<td class="actions">
			<div class="context_menu" @click.away="showContextMenu = false">
				<button class="button floatnone" @click.prevent="showContextMenu = true">
					<svg aria-hidden="true" width="10" height="10" focusable="false" data-prefix="fas" data-icon="ellipsis-h" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M328 256c0 39.8-32.2 72-72 72s-72-32.2-72-72 32.2-72 72-72 72 32.2 72 72zm104-72c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72zm-352 0c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72z"></path></svg>
				</button>
				<div class="roundframe" x-show="showContextMenu">
					<ul>
						<li>
							<a @click.prevent="block.clone($el)" class="button">', $txt['lp_action_clone'], '</a>
						</li>';

	if (isset($txt['lp_' . $data['type']]['title'])) {
		echo '
						<li>
							<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '" class="button">', $txt['modify'], '</a>
						</li>';
	}

	echo '
						<li>
							<a @click.prevent="showContextMenu = false; block.remove($el)" class="button error">', $txt['remove'], '</a>
						</li>
					</ul>
				</div>
			</div>
		</td>
	</tr>';
}

/**
 * The page for adding blocks
 *
 * Страница добавления блоков
 *
 * @return void
 */
function template_block_add()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_blocks'], '</h3>
	</div>
	<div class="information">', $txt['lp_blocks_add_instruction'], '</div>
	<div id="lp_blocks">
		<form name="block_add_form" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="row">';

	foreach ($context['lp_all_blocks'] as $block) {
		echo '
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" x-data>
					<div class="item roundframe" data-type="', $block['type'], '" @click="block.add($el.children[0])">
						<i class="', $block['icon'], '"></i>
						<strong>', $block['title'], '</strong>
						<hr>
						<p>', $block['desc'], '</p>
					</div>
				</div>';
	}

	echo '
			</div>
			<input type="hidden" name="add_block">
			<input type="hidden" name="placement" value="', $context['current_block']['placement'], '">
		</form>
	</div>

	<script>
		const block = new Block();
	</script>';
}

/**
 * Block creation/editing template
 *
 * Шаблон создания/редактирования блока
 *
 * @return void
 */
function template_block_post()
{
	global $context, $txt;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		if (!empty($context['lp_block']['title_style']))
			$context['preview_title'] = '<span style="' . $context['lp_block']['title_style'] . '">' . $context['preview_title'] . '</span>';

		echo sprintf($context['lp_all_title_classes'][$context['lp_block']['title_class']], $context['preview_title']);

		$style = '';
		if (!empty($context['lp_block']['content_style']))
			$style = ' style="' . $context['lp_block']['content_style'] . '"';

		echo '
	<div class="preview block_', $context['lp_block']['type'], '">
		', sprintf($context['lp_all_content_classes'][$context['lp_block']['content_class']], $context['preview_content'], $style), '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_' . $context['lp_block']['type']]['title'], '</h3>
	</div>
	<div class="information">
		', $txt['lp_' . $context['lp_block']['type']]['description'], '
	</div>';
	}

	if (!empty($context['post_errors'])) {
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
	<form id="lp_post" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);" x-data>
		<div class="windowbg">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd"><i class="far fa-newspaper"></i> <span>', $txt['lp_tab_content'], '</span></label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd"><i class="fas fa-key"></i> <span>', $txt['lp_tab_access_placement'], '</span></label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd"><i class="fas fa-object-group"></i> <span>', $txt['lp_tab_appearance'], '</span></label>';

	if ($context['lp_block_tab_tuning']) {
		echo '
				<input id="tab4" type="radio" name="tabs">
				<label for="tab4" class="bg odd"><i class="fas fa-tools"></i> <span>', $txt['lp_tab_tuning'], '</span></label>';
	}

	echo '
				<section id="content-tab1" class="bg even">';

	template_post_tab($fields);

	echo '
				</section>
				<section id="content-tab2" class="bg even">';

	template_post_tab($fields, 'access_placement');

	echo '
				</section>
				<section id="content-tab3" class="bg even">';

	template_post_tab($fields, 'appearance');

	echo '
				</section>';

	if ($context['lp_block_tab_tuning']) {
		echo '
				<section id="content-tab4" class="bg even">';

		template_post_tab($fields, 'tuning');

		echo '
				</section>';
	}

	echo '
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="block_id" value="', $context['lp_block']['id'], '">
				<input type="hidden" name="add_block" value="', $context['lp_block']['type'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	if (!empty($context['lp_block']['id'])) {
		echo '
				<button type="submit" class="button active" name="remove" style="float: left">', $txt['remove'], '</button>';
	}

	echo '
				<button type="submit" class="button" name="preview" @click="block.post($el)">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="block.post($el)">', $txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="block.post($el)">', $txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>

	<script>
		const block = new Block();

		const placementSelect = document.getElementById("placement");

		if (placementSelect.style.display !== "none") {
			new SlimSelect({
				select: placementSelect,
				showSearch: false,
				hideSelectedOption: true,
				closeOnSelect: true,
				showContent: "down"
			});
		}

		new SlimSelect({
			select: "#permissions",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});

		let iconSelect = new SlimSelect({
			select: "#icon",
			allowDeselect: true,
			deselectLabel: "<span class=\"red\">✖</span>",
			limit: 30,
			ajax: function (search, callback) {
				if (search.length < 3) {
					callback("', sprintf($txt['lp_min_search_length'], 3), '")
					return
				}

				fetch("', $context['canonical_url'], ';icons", {
					method: "POST",
					headers: {
						"Content-Type": "application/json; charset=utf-8"
					},
					body: JSON.stringify({
						search,
						add_block: "', $context['lp_block']['type'], '"
					})
				})
				.then(response => response.json())
				.then(function (json) {
					let data = [];
					for (let i = 0; i < json.length; i++) {
						data.push({innerHTML: json[i].innerHTML, text: json[i].text})
					}

					callback(data)
				})
				.catch(function (error) {
					callback(false)
				})
			},
			hideSelectedOption: true,
			placeholder: "', $txt['lp_block_select_icon'], '",
			searchingText: "', $txt['search'], '...",
			searchText: "', $txt['no_matches'], '",
			searchPlaceholder: "cheese",
			searchHighlight: true,
			closeOnSelect: false,
			showContent: "down",
			addable: function (value) {
				return {
					text: value.toLowerCase(),
					value: value.toLowerCase()
				}
			}
		});';

	if (!empty($context['lp_block']['icon'])) {
		echo '
		iconSelect.setData([{innerHTML: `', $context['lp_block']['icon_template'], '`, text: "', $context['lp_block']['icon'], '"}]);
		iconSelect.set(', JavaScriptEscape($context['lp_block']['icon']), ');';
	}

	if (!empty($context['lp_all_title_classes'])) {
		echo '
		new SlimSelect({
			select: "#title_class",
			data: [';

		foreach ($context['lp_all_title_classes'] as $key => $template) {
			echo '
				{
					innerHTML: `' . sprintf($template, empty($key) ? $txt['no'] : $key) . '`,
					text: "' . $key . '",
					selected: ' . ($key == $context['lp_block']['title_class'] ? 'true' : 'false') . '
				},';
		}

		echo '
			],
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});';
	}

	if (empty($context['lp_block']['options']['no_content_class']) && !empty($context['lp_all_content_classes'])) {
		echo '
		new SlimSelect({
			select: "#content_class",
			data: [';

		foreach ($context['lp_all_content_classes'] as $key => $template) {
			echo '
				{
					innerHTML: `' . sprintf($template, empty($key) ? $txt['no'] : $key, '') . '`,
					text: "' . $key . '",
					selected: ' . ($key == $context['lp_block']['content_class'] ? 'true' : 'false') . '
				},';
		}

		echo '
			],
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});';
	}

	echo '
	</script>';
}

/**
 * Show a table with possible areas for displaying the block
 *
 * Отображаем табличку с возможными областями для вывода блока
 *
 * @return void
 */
function template_show_areas_info()
{
	global $txt, $context;

	echo $txt['lp_block_areas_subtext'] . '<br>';

	echo '
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th>', $txt['lp_block_areas_area_th'], '</th>
				<th>', $txt['lp_block_areas_display_th'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['lp_possible_areas'] as $area => $where_to_display) {
		echo '
			<tr class="windowbg">
				<td class="righttext"><strong>', $area, '</strong></td>
				<td class="lefttext">', $where_to_display, '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}
