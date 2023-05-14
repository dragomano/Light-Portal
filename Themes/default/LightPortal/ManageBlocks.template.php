<?php

function template_manage_blocks()
{
	global $context, $scripturl, $txt;

	foreach ($context['lp_current_blocks'] as $placement => $blocks) {
		$block_group_type = in_array($placement, ['header', 'top', 'left', 'right', 'bottom', 'footer']) ? 'default' : 'additional';

		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatright">
				<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], ';placement=', $placement, '" x-data>
					', str_replace(' class=', ' @mouseover="block.toggleSpin($event.target)" @mouseout="block.toggleSpin($event.target)" title="' . $txt['lp_blocks_add'] . '" class=', $context['lp_icon_set']['plus']), '
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
				<th scope="col" class="icon hidden-xs hidden-sm">
					', $txt['custom_profile_icon'], '
				</th>
				<th scope="col" class="title">
					', $txt['lp_block_note'], ' / ', $txt['lp_title'], '
				</th>
				<th scope="col" class="type hidden-xs hidden-sm hidden-md">
					', $txt['lp_block_type'], '
				</th>
				<th scope="col" class="areas hidden-xs hidden-sm">
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
				onAdd: e => block.sort(e),
				onUpdate: e => block.sort(e),
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
		<td class="icon hidden-xs hidden-sm">
			', $data['icon'], '
		</td>
		<td class="title">
			<div class="hidden-xs hidden-sm hidden-md">', $title = $data['note'] ?: ($data['title'][$context['user']['language']] ?? $data['title']['english'] ?? $data['title'][$language] ?? ''), '</div>
			<div class="hidden-lg hidden-xl">
				<table class="table_grid">
					<tbody>
						', $title ? '<tr class="windowbg">
							<td colspan="2">' . $title . '</td>
						</tr>' : '', '
						<tr class="windowbg">
							<td>', $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']], '</td>
							<td class="hidden-md">', $data['areas'], '</td>
						</tr>
					</tbody>
				</table>
			</div>
		</td>
		<td class="type hidden-xs hidden-sm hidden-md">
			', $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']], '
		</td>
		<td class="areas hidden-xs hidden-sm">
			', $data['areas'], '
		</td>
		<td class="priority">
			', $data['priority'], ' ', str_replace(' class="', ' title="' . $txt['lp_action_move'] . '" class="handle ', $context['lp_icon_set']['sort']), '
		</td>
		<td class="status">
			<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'', $txt['lp_action_off'], '\' : \'', $txt['lp_action_on'], '\'" @click.prevent="status = !status"></span>
		</td>
		<td class="actions">
			<div class="context_menu" @click.outside="showContextMenu = false">
				<button class="button floatnone" @click.prevent="showContextMenu = true">
					<svg aria-hidden="true" width="10" height="10" focusable="false" data-prefix="fas" data-icon="ellipsis-h" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M328 256c0 39.8-32.2 72-72 72s-72-32.2-72-72 32.2-72 72-72 72 32.2 72 72zm104-72c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72zm-352 0c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72z"></path></svg>
				</button>
				<div class="roundframe" x-show="showContextMenu">
					<ul>
						<li>
							<a @click.prevent="block.clone($root)" class="button">', $txt['lp_action_clone'], '</a>
						</li>';

	if (isset($txt['lp_' . $data['type']]['title'])) {
		echo '
						<li>
							<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '" class="button">', $txt['modify'], '</a>
						</li>';
	}

	echo '
						<li>
							<a @click.prevent="showContextMenu = false; block.remove($root)" class="button error">', $txt['remove'], '</a>
						</li>
					</ul>
				</div>
			</div>
		</td>
	</tr>';
}

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
					<div class="item roundframe" data-type="', $block['type'], '" @click="block.add($el)">
						<i class="', $block['icon'], ' fa-2x" aria-hidden="true"></i>
						<div>
							<strong>', $block['title'], '</strong>
						</div>
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

function template_block_post()
{
	global $context, $txt, $language;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo sprintf($context['lp_all_title_classes'][$context['lp_block']['title_class']], $context['preview_title']);

		echo '
	<div class="preview block_', $context['lp_block']['type'], '">
		', sprintf($context['lp_all_content_classes'][$context['lp_block']['content_class']], $context['preview_content']), '
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

	$titles = '';
	foreach ($context['languages'] as $lang) {
		$titles .= ', title_' . $lang['filename'] . ': \'' . ($context['lp_block']['title'][$lang['filename']] ?? '') . '\'';
	}

	echo '
	<form
		id="lp_post"
		action="', $context['canonical_url'], '"
		method="post"
		accept-charset="', $context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ tab: window.location.hash ? window.location.hash.substring(1) : \'', $language, '\'', $titles, ' }"
	>
		<div class="windowbg">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $context['lp_icon_set']['content'], '<span>', $txt['lp_tab_content'], '</span></label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $context['lp_icon_set']['access'], '<span>', $txt['lp_tab_access_placement'], '</span></label>';

	if ($context['lp_block_tab_appearance']) {
		echo '
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $context['lp_icon_set']['design'], '<span>', $txt['lp_tab_appearance'], '</span></label>';
	}

	echo '
				<input id="tab4" type="radio" name="tabs">
				<label for="tab4" class="bg odd">' . $context['lp_icon_set']['tools'] . '<span>', $txt['lp_tab_tuning'], '</span></label>
				<section id="content-tab1" class="bg even">';

	template_post_tab($fields);

	echo '
				</section>
				<section id="content-tab2" class="bg even">';

	template_post_tab($fields, 'access_placement');

	echo '
				</section>';

	if ($context['lp_block_tab_appearance']) {
		echo '
				<section id="content-tab3" class="bg even">';

		template_post_tab($fields, 'appearance');

		echo '
				</section>';
	}

	echo '
				<section id="content-tab4" class="bg even">';

	template_post_tab($fields, 'tuning');

	echo '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="block_id" value="', $context['lp_block']['id'], '">
				<input type="hidden" name="add_block" value="', $context['lp_block']['type'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	if (! empty($context['lp_block']['id'])) {
		echo '
				<button type="submit" class="button active" name="remove" style="float: left">', $txt['remove'], '</button>';
	}

	echo '
				<button type="submit" class="button" name="preview" @click="block.post($root)">', $context['lp_icon_set']['preview'], $txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="block.post($root)">', $context['lp_icon_set']['save'], $txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="block.post($root)">', $context['lp_icon_set']['save_exit'], $txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>
	<script>
		const block = new Block();
	</script>';
}

function template_show_areas_info()
{
	global $txt, $context;

	echo '
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th colspan="2">', $txt['lp_block_areas_th'], '</th>
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
