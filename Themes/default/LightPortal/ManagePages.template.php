<?php

// The management page section template | Шаблон раздела управления страницами
function template_manage_pages_above()
{
	global $modSettings, $context, $txt, $user_info, $settings, $scripturl;

	if (!empty($modSettings['lp_main_page_disable']))
		$context['lp_main_page']['status'] = false;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_pages_main'], '</h3>
	</div>
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col" class="date">
					', $txt['date'], '
				</th>
				<th scope="col" class="num_views">
					', $txt['views'], '
				</th>
				<th scope="col" class="type">
					', $txt['lp_page_type'], '
				</th>
				<th scope="col" class="alias">
					', $txt['lp_page_alias'], '
				</th>
				<th scope="col" class="title">
					', $txt['lp_title'], '
				</th>
				<th scope="col" class="actions" style="width: 14%">
					', $txt['lp_actions'], '
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="windowbg">
				<td class="date centertext">
					', $context['lp_main_page']['created_at'], '
				</td>
				<td class="num_views centertext">
					', $context['lp_main_page']['num_views'], '
				</td>
				<td class="type centertext">
					', $txt['lp_page_types'][$context['lp_main_page']['type']], '
				</td>
				<td class="alias centertext">
					', (empty($modSettings['lp_main_page_disable']) ? '<a href="' . $scripturl . '">' . $context['lp_main_page']['alias'] . '</a>' : $context['lp_main_page']['alias']), '
				</td>
				<td class="title centertext">
					', (!empty($modSettings['lp_main_page_title_' . $user_info['language']]) ? $modSettings['lp_main_page_title_' . $user_info['language']] : $context['lp_main_page']['title']), '
				</td>
				<td class="actions centertext" style="cursor: pointer">
					', empty($context['lp_main_page']['status']) ? ('<span class="toggle_status off" data-id="1" title="' . $txt['lp_action_on'] . '"></span>') : ('<span class="toggle_status on" data-id="1" title="' . $txt['lp_action_off'] . '"></span>');

	if ($settings['name'] == 'Lunarfall') {
		echo '
					<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=1"><span class="fas fa-edit settings" title="', $txt['edit'], '"></span></a>';
	} else {
		echo '
					<a href="', $scripturl, '?action=admin;area=lp_pages;sa=edit;id=1"><span class="main_icons settings" title="', $txt['edit'], '"></span></a>';
	}

	echo '
				</td>
			</tr>
		</tbody>
	</table>';
}

function template_manage_pages_below()
{
	global $txt;

	echo '
	<script>
		let work = smf_scripturl + "?action=admin;area=lp_pages";
		jQuery(document).ready(function($) {
			$(".del_page").on("click", function() {
				if (!confirm("' . $txt['quickmod_confirm'] . '"))
					return false;
				let item = $(this).attr("data-id");
				if (item) {
					$.post(work, {del_page: item});
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

// Page creation/editing template | Шаблон создания/редактирования страницы
function template_post_page()
{
	global $context, $txt;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup">
		', $context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
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

	echo '
	<form id="postmodify" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '">';

	template_post_header();

	if ($context['lp_page']['type'] == 'bbc') {
		echo '
			<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>';
	}

	echo '
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="page_id" value="', $context['lp_page']['id'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="preview">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save">', $txt['save'], '</button>
			</div>
		</div>
	</form>
	<script>
		jQuery(document).ready(function($) {
			$("#postmodify").on("change", function (e) {
				if ($(e.target).attr("name")) {
					if ($("#title").val() != "" && $("#alias").val() != "") {
						$("#type").prop("disabled", false);
						$("button[name=preview]").prop("disabled", false);
						$("button[name=save]").prop("disabled", false);
					} else {
						$("#type").prop("disabled", true);
						$("button[name=preview]").prop("disabled", true);
						$("button[name=save]").prop("disabled", true);
					}
				}
			});
			$("#type").on("change", function() {
				ajax_indicator(true);
				$("button[name=preview]").click();
			});
		});
	</script>';
}
