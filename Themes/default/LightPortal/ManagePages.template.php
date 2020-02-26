<?php

/**
 * Page creation/editing template
 *
 * Шаблон создания/редактирования страницы
 *
 * @return void
 */
function template_page_post()
{
	global $context, $txt;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup page_', $context['lp_page']['type'], '">
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
	<form id="postpage" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '">';

	template_post_header();

	if ($context['lp_page']['type'] == 'bbc') {
		echo '
			<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>';
	}

	echo '
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="preview">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save">', $txt['save'], '</button>
			</div>
		</div>
	</form>
	<script>
		jQuery(document).ready(function($) {
			$("#postpage").on("change", function (e) {
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
				if ($("#content").val() == "") {
					$("#content").val(" ");
				}
				$("button[name=preview]").click();
			});
		});
	</script>';
}
