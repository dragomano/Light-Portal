<?php

use Bugo\LightPortal\Utils\{Icon, Lang, Theme, Utils};

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
