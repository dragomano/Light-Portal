<?php

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Utils\Icon;

function template_page_add(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_pages'], '</h3>
	</div>
	<div class="information">', Lang::$txt['lp_pages_add_instruction'], '</div>
	<div id="lp_blocks">
		<form name="page_add_form" action="', Utils::$context['form_action'], '" method="post" accept-charset="', Utils::$context['character_set'], '">
			<div class="row">';

	foreach (Utils::$context['lp_all_pages'] as $page) {
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

function template_page_post(): void
{
	if (isset(Utils::$context['preview_content']) && empty(Utils::$context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup page_', Utils::$context['lp_page']['type'], '">
		', Utils::$context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
	</div>
	<div class="information">
		', Lang::$txt['lp_' . Utils::$context['lp_page']['type']]['description'], '
	</div>';
	}

	if (! empty(Utils::$context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach (Utils::$context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = Utils::$context['posting_fields'];

	$titles = '';
	foreach (Utils::$context['lp_languages'] as $lang) {
		$titles .= ', title_' . $lang['filename'] . ': `' . (Utils::$context['lp_page']['titles'][$lang['filename']] ?? '') . '`';
	}

	echo '
	<form
		id="lp_post"
		action="', Utils::$context['form_action'], '"
		method="post"
		accept-charset="', Utils::$context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ tab: window.location.hash ? window.location.hash.substring(1) : \'', Config::$language, '\'', $titles, ' }"
	>
		<div class="roundframe', isset(Utils::$context['preview_content']) ? '' : ' noup', '">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="common">', Icon::get('content'), Lang::$txt['lp_tab_content'], '</div>
					<div class="bg odd" data-tab="access">', Icon::get('access'), Lang::$txt['lp_tab_access_placement'], '</div>
					<div class="bg odd" data-tab="seo">', Icon::get('spider'), Lang::$txt['lp_tab_seo'], '</div>
					<div class="bg odd" data-tab="tuning">', Icon::get('tools'), Lang::$txt['lp_tab_tuning'], '</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">', template_post_tab($fields), '</section>
					<section class="bg even" data-content="access">', template_post_tab($fields, 'access_placement'), '</section>
					<section class="bg even" data-content="seo">', template_post_tab($fields, 'seo'), '</section>
					<section class="bg even" data-content="tuning">', template_post_tab($fields, 'tuning'), '</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="page_id" value="', Utils::$context['lp_page']['id'], '">
				<input type="hidden" name="add_page" value="', Utils::$context['lp_page']['type'], '">
				<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
				<input type="hidden" name="seqnum" value="', Utils::$context['form_sequence_number'], '">
				<button type="submit" class="button active" name="remove" style="float: left" x-show="!', (int) empty(Utils::$context['lp_page']['id']), '">', Lang::$txt['remove'], '</button>
				<button type="submit" class="button" name="preview" @click="page.post($root)">', Icon::get('preview'), Lang::$txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="page.post($root)">', Icon::get('save'), Lang::$txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="page.post($root)">', Icon::get('save_exit'), Lang::$txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>
	<script>
		const page = new Page();
		const tabs = new Tabs(".lp_tabs");
	</script>';
}
