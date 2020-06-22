<?php

/**
 * The page creation/editing template
 *
 * Шаблон создания/редактирования страницы
 *
 * @return void
 */
function template_page_post()
{
	global $context, $txt, $settings;

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
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $txt['lp_tab_content'], '</label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $txt['lp_tab_seo'], '</label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $txt['lp_tab_tuning'], '</label>
				<section id="content-tab1" class="bg even">
					', template_post_tab();

	if ($context['lp_page']['type'] == 'bbc') {
		echo '
					<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>';
	}

	echo '
				</section>
				<section id="content-tab2" class="bg even">
					', template_post_tab('seo'), '
				</section>
				<section id="content-tab3" class="bg even">
					', template_post_tab('tuning'), '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="preview">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save">', $txt['save'], '</button>
			</div>
		</div>
	</form>
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/page_post.js"></script>';
}
