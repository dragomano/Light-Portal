<?php

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Utils\Icon;

function template_manage_categories_above() {}

function template_manage_categories_below(): void
{
	echo '
	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		const category = new Cat();
		new Sortable(document.querySelector("#lp_categories tbody"), {
			handle: ".handle",
			animation: 150,
			onSort: e => category.updatePriority(e)
		});
	</script>';
}

function template_category_post(): void
{
	if (isset(Utils::$context['preview_content']) && empty(Utils::$context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup">
		', Utils::$context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
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
		$titles .= ', title_' . $lang['filename'] . ': `' . (Utils::$context['lp_category']['titles'][$lang['filename']] ?? '') . '`';
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
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">', template_post_tab($fields), '</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="category_id" value="', Utils::$context['lp_category']['id'], '">
				<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
				<input type="hidden" name="seqnum" value="', Utils::$context['form_sequence_number'], '">
				<button type="submit" class="button active" name="remove" style="float: left" x-show="!', (int) empty(Utils::$context['lp_category']['id']), '">', Lang::$txt['remove'], '</button>
				<button type="submit" class="button" name="preview" @click="category.post($root)">', Icon::get('preview'), Lang::$txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="category.post($root)">', Icon::get('save'), Lang::$txt['save'], '</button>
				<button type="submit" class="button" name="save_exit" @click="category.post($root)">', Icon::get('save_exit'), Lang::$txt['lp_save_and_exit'], '</button>
			</div>
		</div>
	</form>
	<script>
		const category = new Cat();
	</script>';
}

function template_lp_category_settings(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_categories_manage'], '</h3>
	</div>
	<div class="windowbg noup">
		<dl class="lp_categories settings" x-data>
			<dt>
				<form accept-charset="', Utils::$context['character_set'], '">
					<table class="table_grid">
						<tbody id="lp_categories" x-ref="category_list">';

	foreach (Utils::$context['lp_categories'] as $id => $cat)
		show_single_category($id, $cat);

	echo '
						</tbody>
					</table>
				</form>
			</dt>
			<dd>
				<div class="roundframe">
					<div class="noticebox">
						<form
							id="add_category_form"
							name="add_category_form"
							accept-charset="', Utils::$context['character_set'], '"
							@submit.prevent="category.add($refs)"
						>
							<input
								name="new_category_name"
								type="text"
								placeholder="', Lang::$txt['title'], '"
								maxlength="255"
								form="add_category_form"
								required
								x-ref="cat_name"
							>
							<textarea
								placeholder="', Lang::$txt['lp_categories_desc'], '"
								maxlength="255"
								x-ref="cat_desc"
							></textarea>
						</form>
					</div>
					<div class="centertext">
						<input form="add_category_form" class="button" type="submit" value="', Lang::$txt['lp_categories_add'], '">
					</div>
				</div>
			</dd>
		</dl>
	</div>

	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		const category = new Category();
		new Sortable(document.getElementById("lp_categories"), {
			handle: ".handle",
			animation: 150,
			onSort: e => category.updatePriority(e)
		});
	</script>';
}

function show_single_category(int $id, array $cat): void
{
	echo '
	<tr class="windowbg" data-id="', $id, '" x-data>
		<td class="centertext handle">', Icon::get('arrows'), '</td>
		<td>
			<span class="floatright">
				<span @click="category.remove($root)" title="', Lang::$txt['remove'], '" class="error">&times;</span>
			</span>
			<label for="category_name', $id, '" class="handle">', Lang::$txt['lp_category'], ' #', $id, '</label>
			<input
				type="text"
				value="', $cat['name'], '"
				maxlength="255"
				@change="category.updateName($root, $event.target)"
			>
			<br>
			<textarea
				id="category_desc', $id, '"
				rows="2"
				placeholder="', Lang::$txt['lp_page_description'], '"
				maxlength="255"
				@change="category.updateDescription($root, $event.target.value)"
			>', $cat['desc'], '</textarea>
		</td>
	</tr>';
}
