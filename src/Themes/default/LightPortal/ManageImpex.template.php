<?php

use Bugo\Compat\{Config, Lang, Utils};

function template_manage_export_blocks(): void
{
	echo '
	<form action="', Utils::$context['form_action'], '" method="post" accept-charset="', Utils::$context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
		</div>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', Lang::$txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', Lang::$txt['lp_export_all'], '" class="button">
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col">#</th>
					<th scope="col" class="type">
						', Lang::$txt['lp_block_note'], ' / ', Lang::$txt['lp_title'], '
					</th>
					<th scope="col" class="type hidden-xs">
						', Lang::$txt['lp_block_type'], '
					</th>
					<th scope="col" class="placement">
						', Lang::$txt['lp_block_placement'], '
					</th>
					<th scope="col" class="actions">
						<input type="checkbox" onclick="invertAll(this, this.form);">
					</th>
				</tr>
			</thead>
			<tbody>';

		$empty = true;
		foreach (Utils::$context['lp_current_blocks'] as $placement) {
			if (is_array($placement)) {
				$empty = false;
				break;
			}
		}

		if ($empty)
			Utils::$context['lp_current_blocks'] = [];

		if (empty(Utils::$context['lp_current_blocks'])) {
			echo '
				<tr class="windowbg">
					<td colspan="5" class="centertext">', Lang::$txt['lp_no_items'], '</td>
				</tr>';
		} else {
			foreach (Utils::$context['lp_current_blocks'] as $placement => $blocks) {
				if (is_array($blocks)) {
					foreach ($blocks as $id => $data) {
						echo '
				<tr class="windowbg', $data['status'] ? ' sticky' : '', '">
					<td class="centertext">
						', $id, '
					</td>
					<td class="type centertext">
						', $data['note'] ?: ($data['titles'][Utils::$context['user']['language']] ?? $data['titles'][Config::$language] ?? ''), '
					</td>
					<td class="type hidden-xs centertext">
						', Lang::$txt['lp_' . $data['type']]['title'] ?? Utils::$context['lp_missing_block_types'][$data['type']], '
					</td>
					<td class="placement centertext">
						', Utils::$context['lp_block_placements'][$placement] ?? (Lang::$txt['unknown'] . ' (' . $placement . ')'), '
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="' . $id . '" name="blocks[]">
					</td>
				</tr>';
					}
				}
			}
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', Lang::$txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', Lang::$txt['lp_export_all'], '" class="button">
		</div>
	</form>';
}

function template_manage_export_plugins(): void
{
	echo '
	<form action="', Utils::$context['form_action'], '" method="post" accept-charset="', Utils::$context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
		</div>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', Lang::$txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', Lang::$txt['lp_export_all'], '" class="button">
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col">#</th>
					<th scope="col" class="type">
						', Lang::$txt['lp_plugin_name'], '
					</th>
					<th scope="col" class="actions">
						<input type="checkbox" onclick="invertAll(this, this.form);">
					</th>
				</tr>
			</thead>
			<tbody>';

		if (empty(Utils::$context['lp_plugins'])) {
			echo '
				<tr class="windowbg">
					<td colspan="3" class="centertext">', Lang::$txt['lp_no_items'], '</td>
				</tr>';
		} else {
			foreach (Utils::$context['lp_plugins'] as $id => $name) {
				echo '
				<tr class="windowbg">
					<td class="centertext">
						', $id + 1, '
					</td>
					<td class="name centertext">
						', $name, '
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="' . $name . '" name="plugins[]">
					</td>
				</tr>';
			}
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', Lang::$txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', Lang::$txt['lp_export_all'], '" class="button">
		</div>
	</form>';
}

function template_manage_import(): void
{
	if (! empty(Utils::$context['import_successful']))
		echo '
	<div class="infobox">', Utils::$context['import_successful'], '</div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Utils::$context['page_area_title'], '</h3>
	</div>
	<div class="information">', Utils::$context['page_area_info'], '</div>
	<div class="descbox">
		<form action="', Utils::$context['form_action'], '" method="post" enctype="multipart/form-data">
			<div class="centertext">
				<input type="hidden" name="MAX_FILE_SIZE" value="', Utils::$context['max_file_size'], '">
				<input name="import_file" type="file" accept="', Utils::$context['lp_file_type'], '">
				<button class="button floatnone" type="submit">', Lang::$txt['lp_import_run'], '</button>
			</div>
		</form>
	</div>';
}
