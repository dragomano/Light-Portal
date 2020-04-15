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
		</div>
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

		if (empty(count($context['lp_active_blocks']))) {
			echo '
				<tr class="windowbg">
					<td colspan="4" class="centertext">', $txt['lp_no_items'], '</td>
				</tr>';
		} else {
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
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row floatright">
			<input type="submit" name="export_selection" value="' . $txt['lp_export_run'] . '" class="button">
		</div>
	</form>';
}
