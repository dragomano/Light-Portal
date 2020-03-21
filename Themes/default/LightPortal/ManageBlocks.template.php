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
	global $settings;

	show_block_table();

	echo '
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/Sortable.min.js"></script>
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/manage_blocks.js"></script>';
}

/**
 * Displaying a table with blocks
 *
 * Отображение таблицы с блоками
 *
 * @return void
 */
function show_block_table()
{
	global $context, $txt, $scripturl;

	if (empty($context['lp_current_blocks'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_blocks'], '</h3>
	</div>
	<div class="information">', $txt['lp_no_items'], '</div>';

		return;
	}

	foreach ($context['lp_current_blocks'] as $placement => $blocks) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatright">
				<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=add;', $context['session_var'], '=', $context['session_id'], ';placement=', $placement, '">
					<i class="fas fa-plus" title="', $txt['lp_blocks_add'], '"></i>
				</a>
			</span>
			', $txt['lp_block_placement_set'][$placement], '
		</h3>
	</div>
	<table class="lp_current_blocks table_grid">';

		if (is_array($blocks)) {
			echo '
		<thead>
			<tr class="title_bar">
				<th scope="col" class="icon">
					', $txt['custom_profile_icon'], '
				</th>
				<th scope="col" class="title">
					', $txt['lp_title'], '
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
			<tr class="windowbg centertext">
				<td>', $txt['lp_no_items'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
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
function show_block_entry($id, $data)
{
	global $context, $language, $txt, $settings, $scripturl;

	if (empty($id) || empty($data))
		return;

	echo '
	<tr id="lp_block_', $id, '" class="windowbg">
		<td class="icon centertext">
			', $data['icon'], '
		</td>
		<td class="title centertext">
			', $data['title'][$context['user']['language']] ?? $data['title'][$language] ?? $data['title']['english'], '
		</td>
		<td class="type centertext">
			', $txt['lp_block_types'][$data['type']] ?? $context['lp_missing_block_types'][$data['type']], '
		</td>
		<td class="areas centertext">
			', $data['areas'], '
		</td>
		<td class="priority centertext">
			', $data['priority'], ' <span class="handle ', (strpos($settings['name'], 'Lunarfall') !== false ? 'fas fa-sort' : 'main_icons select_here'), '" data-key="', $id, '" title="', $txt['lp_action_move'], '"></span>
		</td>
		<td class="actions centertext">';

		if (empty($data['status']))
			echo '
			<span class="toggle_status off" data-id="', $id, '" title="', $txt['lp_action_on'], '"></span>';
		else
			echo '
			<span class="toggle_status on" data-id="', $id, '" title="', $txt['lp_action_off'], '"></span>';

		if (strpos($settings['name'], 'Lunarfall') !== false) {
			echo '
			<span class="fas fa-clone reports" data-id="', $id, '" title="', $txt['lp_action_clone'], '"></span>
			<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '"><span class="fas fa-tools" title="', $txt['edit'], '"></span></a>
			<span class="fas fa-trash del_block" data-id="', $id, '" title="', $txt['remove'], '"></span>';
		} else {
			echo '
			<span class="main_icons reports" data-id="', $id, '" title="', $txt['lp_action_clone'], '"></span>
			<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '"><span class="main_icons settings" title="', $txt['edit'], '"></span></a>
			<span class="main_icons unread_button del_block" data-id="', $id, '" title="', $txt['remove'], '"></span>';
		}

		echo '
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
	global $txt, $context, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_blocks'], '</h3>
	</div>
	<div class="information">', $txt['lp_blocks_add_instruction'], '</div>
	<div id="lp_blocks">
		<form name="block_add_form" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="row">';

	asort($txt['lp_block_types']);
	foreach ($txt['lp_block_types'] as $type => $title) {
		echo '
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
					<div>
						<div class="item roundframe" data-type="', $type, '">
							<h4>', $title, '</h4>
							<p>', $txt['lp_block_types_descriptions'][$type], '</p>
						</div>
					</div>
				</div>';
	}

	echo '
			</div>
			<input type="hidden" name="add_block">
			<input type="hidden" name="placement" value="', $context['current_block']['placement'], '">
		</form>
		<br class="clear">
		<script src="', $settings['default_theme_url'], '/scripts/light_portal/jquery.matchHeight-min.js"></script>
		<script>
			jQuery(document).ready(function($) {
				$("#lp_blocks .item").on("click", function() {
					let block_name = $(this).attr("data-type"),
						this_form = $("form[name=block_add_form]");
					this_form.children("input[name=add_block]").val(block_name);
					this_form.submit();
				});
				$("#lp_blocks .row .item").matchHeight();
			});
		</script>
	</div>';
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
			$style = ' style="' . $context['lp_block']['title_style'] . '"';

		echo sprintf($context['lp_all_title_classes'][$context['lp_block']['title_class']], $context['preview_title'], $style ?? '', null);

		$style = '';
		if (!empty($context['lp_block']['content_style']))
			$style = ' style="' . $context['lp_block']['content_style'] . '"';

		echo '
	<div class="preview block_', $context['lp_block']['type'], '">';

		if (!empty($context['lp_block']['content_class']))
			echo sprintf($context['lp_all_content_classes'][$context['lp_block']['content_class']], $context['preview_content'], $style);
		else
			echo $context['preview_content'];

		echo '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>
	<div class="information">', $txt['lp_block_types'][$context['lp_block']['type']], '</div>';
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

	echo '
	<form id="postblock" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<div class="roundframe">';

	template_post_header();

	if (!empty($context['lp_block']['options']['content']) && $context['lp_block']['type'] === 'bbc') {
		echo '
			<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>';
	}

	echo '
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="block_id" value="', $context['lp_block']['id'], '">
				<input type="hidden" name="add_block" value="', $context['lp_block']['type'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="preview">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save">', $txt['save'], '</button>
			</div>
		</div>
	</form>
	<script>
		jQuery(document).ready(function($) {
			change_icon = function() {
				let icon = $("#icon").val(),
					type = $("#icon_type input:checked").val();
				$("#block_icon").html(\'<i class="\' + type + \' fa-\' + icon + \'"><\/i>\');
			}
			$("#icon").on("change", change_icon);
			$("#icon_type input").on("change", change_icon);
		});
	</script>';
}
