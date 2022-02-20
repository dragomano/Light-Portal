<?php

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

	echo '
	<form id="lp_post" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);" x-data>
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '" @change="page.change($refs)">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $context['lp_icon_set']['content'], '<span>', $txt['lp_tab_content'], '</span></label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $context['lp_icon_set']['spider'], '<span>', $txt['lp_tab_seo'], '</span></label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $context['lp_icon_set']['tools'], '<span>', $txt['lp_tab_tuning'], '</span></label>
				<section id="content-tab1" class="bg even">';

	template_post_tab($fields);

	echo '
				</section>
				<section id="content-tab2" class="bg even">';

	template_post_tab($fields, 'seo');

	echo '
				</section>
				<section id="content-tab3" class="bg even">';

	template_post_tab($fields, 'tuning');

	echo '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	if (! empty($context['lp_page']['id'])) {
		echo '
				<button type="submit" class="button active" name="remove" style="float: left">', $txt['remove'], '</button>';
	}

	echo '
				<button type="submit" class="button" name="preview" @click="page.post($root)">', $context['lp_icon_set']['preview'], $txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="page.post($root)">', $context['lp_icon_set']['save'], $txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="page.post($root)">', $context['lp_icon_set']['save_exit'], $txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>
	<script async defer src="https://cdn.jsdelivr.net/npm/transliteration@2/dist/browser/bundle.umd.min.js"></script>
	<script>
		const page = new Page();
	</script>';

	require_once __DIR__ . '/partials/page_type.php';
	require_once __DIR__ . '/partials/keywords.php';
	require_once __DIR__ . '/partials/permissions.php';
	require_once __DIR__ . '/partials/category.php';
	require_once __DIR__ . '/partials/page_author.php';
}
