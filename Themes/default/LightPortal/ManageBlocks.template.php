<?php

// Block management section template | Шаблон раздела управления блоками
function template_manage_blocks()
{
	global $settings, $txt;

	show_block_table();

	echo '
	<script src="' . $settings['default_theme_url'] . '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		let work = smf_scripturl + "?action=admin;area=lp_blocks;actions";
		jQuery(document).ready(function($) {
			$(".lp_current_blocks tbody").each(function (i, e) {
				Sortable.create(this, {
					group: "blocks",
					animation: 500,
					handle: ".handle",
					draggable: "tr.windowbg",
					onSort: function (e) {
						let items = e.from.children,
							items2 = e.to.children,
							priority = [],
							placement = "";
						for (let i = 0; i < items2.length; i++) {
							let key = $(items2[i]).find("span.handle").data("key"),
								place = $(items[i]).parent("tbody").data("placement");
								place2 = $(items2[i]).parent("tbody").data("placement");
							if (place !== place2)
								placement = place2;
							if (typeof key !== "undefined")
								priority.push(key);
						}
						$.ajax({
							type: "POST",
							url: work,
							data: {update_priority: priority, update_placement: placement},
							success: function () {
								$("tbody[data-placement=" + place2 + "]").find("td[colspan]").slideUp();
							},
							error: function () {
								console.log(priority);
							}
						});
					}
				});
			});
			$(".del_block").on("click", function() {
				if (!confirm("' . $txt['quickmod_confirm'] . '"))
					return false;
				let item = $(this).attr("data-id");
				if (item) {
					$.post(work, {del_block: item});
					$(this).closest("tr").css("display", "none");
				}
			});
			$(".toggle_status").on("click", function() {
				let item = $(this).attr("data-id"),
					status = $(this).attr("class");
				if (item) {
					$.post(work, {toggle_status: status, item: item});
					if ($(this).hasClass("on")) {
						$(this).removeClass("on");
						$(this).addClass("off");
					} else {
						$(this).removeClass("off");
						$(this).addClass("on");
					}
				}
			});
		});
	</script>';
}

// Displaying a table with blocks | Отображение таблицы с блоками
function show_block_table()
{
	global $context, $txt, $settings, $scripturl;

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
		<h3 class="catbg">', $txt['lp_block_placement_set'][$placement], '</h3>
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

			foreach ($blocks as $id => $data) {
				echo '
			<tr class="windowbg">
				<td class="icon centertext">
					', $data['icon'], '
				</td>
				<td class="title centertext">
					', $data['title'][$context['user']['language']], '
				</td>
				<td class="type centertext">
					', $txt['lp_block_types'][$data['type']], '
				</td>
				<td class="areas centertext">
					', $data['areas'], '
				</td>
				<td class="priority centertext">
					', $data['priority'], ' <span class="handle main_icons select_here" data-key="', $id, '" title="', $txt['lp_action_move'], '"></span>
				</td>
				<td class="actions centertext">';

				if (empty($data['status']))
					echo '
					<span class="toggle_status off" data-id="', $id, '" title="', $txt['lp_action_on'], '"></span>';
				else
					echo '
					<span class="toggle_status on" data-id="', $id, '" title="', $txt['lp_action_off'], '"></span>';

				if ($settings['name'] == 'Lunarfall') {
					echo '
					<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '"><span class="fas fa-edit settings" title="', $txt['edit'], '"></span></a>
					<span class="fas fa-trash unread_button del_block" data-id="', $id, '" title="', $txt['remove'], '"></span>';
				} else {
					echo '
					<a href="', $scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '"><span class="main_icons settings" title="', $txt['edit'], '"></span></a>
					<span class="main_icons unread_button del_block" data-id="', $id, '" title="', $txt['remove'], '"></span>';
				}

				echo '
				</td>
			</tr>';
			}
		} else {
			echo '
		<tbody data-placement="', $placement, '">
			<tr class="windowbg centertext">
				<td colspan="6">', $txt['lp_no_items'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
}

// The page for adding blocks | Страница добавления блоков
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

	foreach ($txt['lp_block_types'] as $type => $title) {
		echo '
				<div class="col-xs">
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
		</form>
		<br class="clear">
		<script>
			jQuery(document).ready(function($) {
				$("#lp_blocks .item").on("click", function() {
					let block_name = $(this).attr("data-type"),
						this_form = $("form[name=block_add_form]");
					this_form.children("input[name=add_block]").val(block_name);
					this_form.submit();
				});
			});
		</script>
	</div>';
}

// Block creation/editing template | Шаблон создания/редактирования блока
function template_post_block()
{
	global $context, $txt;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo sprintf($context['lp_all_title_classes'][$context['lp_block']['title_class']], $context['preview_title'], $context['lp_block']['title_style'], null);

		if (!empty($context['lp_block']['content_class']))
			echo sprintf($context['lp_all_content_classes'][$context['lp_block']['content_class']], $context['preview_content'], $context['lp_block']['content_style']);
		else
			echo $context['preview_content'];
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
	<form action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<div class="roundframe">';

	template_post_header();

	if (!empty($context['lp_block']['options']['content']) && $context['lp_block']['options']['content'] === 'sceditor') {
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
			$("#icon").on("change", function() {
				let icon = $("#icon").val();
				$("#block_icon").html(\'<i class="fas fa-\' + icon + \'"></i>\');
			});
		});
	</script>';
}
