<?php

function template_page_add()
{
	global $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['lp_pages'], '</h3>
	</div>
	<div class="information">', $txt['lp_pages_add_instruction'], '</div>
	<div id="lp_blocks">
		<form name="page_add_form" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="row">';

	foreach ($context['lp_all_pages'] as $page) {
		echo '
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" x-data>
					<div class="item roundframe" data-type="', $page['type'], '" @click="page.add($el)">
						<i class="', $page['icon'], ' fa-2x" aria-hidden="true"></i>
						<div>
							<strong>', $page['title'], '</strong>
						</div>
						<hr>
						<p>', $page['desc'], '</p>
					</div>
				</div>';
	}

	echo '
			</div>
			<input type="hidden" name="add_page">
		</form>
	</div>

	<script>
		const page = new Page();
	</script>';
}

function template_page_post()
{
	global $context, $txt, $language;

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
	</div>
	<div class="information">
		', $txt['lp_' . $context['lp_page']['type']]['description'], '
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
		$titles .= ', title_' . $lang['filename'] . ': \'' . ($context['lp_page']['title'][$lang['filename']] ?? '') . '\'';
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
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $context['lp_icon_set']['content'], '<span>', $txt['lp_tab_content'], '</span></label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $context['lp_icon_set']['access'], '<span>', $txt['lp_tab_access_placement'], '</span></label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $context['lp_icon_set']['spider'], '<span>', $txt['lp_tab_seo'], '</span></label>
				<input id="tab4" type="radio" name="tabs">
				<label for="tab4" class="bg odd">', $context['lp_icon_set']['tools'], '<span>', $txt['lp_tab_tuning'], '</span></label>
				<section id="content-tab1" class="bg even">';

	template_post_tab($fields);

	echo '
				</section>
				<section id="content-tab2" class="bg even">';

	template_post_tab($fields, 'access_placement');

	echo '
				</section>
				<section id="content-tab3" class="bg even">';

	template_post_tab($fields, 'seo');

	echo '
				</section>
				<section id="content-tab4" class="bg even">';

	template_post_tab($fields, 'tuning');

	echo '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="page_id" value="', $context['lp_page']['id'], '">
				<input type="hidden" name="add_page" value="', $context['lp_page']['type'], '">
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
	<script>
		const page = new Page();
	</script>';
}
