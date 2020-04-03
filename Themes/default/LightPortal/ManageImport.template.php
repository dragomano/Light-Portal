<?php

/**
 * Block export template
 *
 * Шаблон экспорта блоков
 *
 * @return void
 */
function template_manage_export_blocks()
{
	global $context, $txt;

	echo '
	<form action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_area_title'], '</h3>
		</div>';

	if (empty($context['lp_current_blocks'])) {
		echo '
		<div class="information">', $txt['lp_no_items'], '</div>
	</form>';
	} else {
		echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col">#</th>
					<th scope="col" class="type">
						', $txt['lp_block_type'], '
					</th>
					<th scope="col" class="placement">
						', $txt['lp_block_placement'], '
					</th>
					<th scope="col" class="actions">
						<input type="checkbox" onclick="invertAll(this, this.form);" checked>
					</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['lp_current_blocks'] as $placement => $blocks) {
			if (is_array($blocks)) {
				foreach ($blocks as $id => $data) {
					echo '
				<tr class="windowbg">
					<td class="centertext">
						', $id, '
					</td>
					<td class="type centertext">
						', $txt['lp_block_types'][$data['type']] ?? $context['lp_missing_block_types'][$data['type']], '
					</td>
					<td class="placement centertext">
						', $txt['lp_block_placement_set'][$placement], '
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="' . $id . '" name="items[]" checked>
					</td>
				</tr>';
				}
			}
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row">
			<input type="submit" name="export_selection" value="' . $txt['lp_export_run'] . '" class="button">
		</div>
	</form>';
	}
}

/**
 * The import template
 *
 * Шаблон импорта
 *
 * @return void
 */
function template_manage_import()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>
	<div class="roundframe noup">
		<form action="', $context['canonical_url'], '" method="post" enctype="multipart/form-data">
			<div class="centertext">
				<input name="import_file" type="file" accept="text/xml">
				<button class="button" type="submit" style="float: none">', $txt['lp_import_run'], '</button>
			</div>
		</form>
	</div>';
}
