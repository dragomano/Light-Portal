<?php declare(strict_types=1);

use Bugo\Compat\{Config, Lang};
use Bugo\Compat\{Theme, Utils};
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Utils\Icon;

function template_manage_blocks(): void
{
	foreach (Utils::$context['lp_current_blocks'] as $placement => $blocks) {
		$block_group_type = in_array($placement, ['header', 'top', 'left', 'right', 'bottom', 'footer']) ? 'default' : 'additional';

		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatright">
				<a href="', Config::$scripturl, '?action=admin;area=lp_blocks;sa=add;', Utils::$context['session_var'], '=', Utils::$context['session_id'], ';placement=', $placement, '" x-data>
					', str_replace(' class=', ' @mouseover="block.toggleSpin($event.target)" @mouseout="block.toggleSpin($event.target)" class=', Icon::get('plus', Lang::$txt['lp_blocks_add'])), '
				</a>
			</span>
			', Utils::$context['lp_block_placements'][$placement] ?? Lang::$txt['not_applicable'], is_array($blocks) ? (' (' . count($blocks) . ')') : '', '
		</h3>
	</div>
	<table class="lp_', $block_group_type, '_blocks table_grid centertext">';

		if (is_array($blocks)) {
			echo '
		<thead>
			<tr class="title_bar">
				<th scope="col" class="icon hidden-xs hidden-sm">
					', Lang::$txt['custom_profile_icon'], '
				</th>
				<th scope="col" class="title">
					', Lang::$txt['lp_block_note'], ' / ', Lang::$txt['lp_title'], '
				</th>
				<th scope="col" class="type hidden-xs hidden-sm hidden-md">
					', Lang::$txt['lp_block_type'], '
				</th>
				<th scope="col" class="areas hidden-xs hidden-sm">
					', Lang::$txt['lp_block_areas'], '
				</th>
				<th scope="col" class="priority hidden-xs">
					', Lang::$txt['lp_block_priority'], '
				</th>
				<th scope="col" class="status">
					', Lang::$txt['status'], '
				</th>
				<th scope="col" class="actions">
					', Lang::$txt['lp_actions'], '
				</th>
			</tr>
		</thead>
		<tbody data-placement="', $placement, '">';

			foreach ($blocks as $id => $data)
				show_block_entry($id, $data);
		} else {
			echo '
		<tbody data-placement="', $placement, '">
			<tr class="windowbg centertext" x-data>
				<td>', Lang::$txt['lp_no_items'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	echo '
	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/Sortable.min.js"></script>
	<script>
		const block = new Block(),
			defaultBlocks = document.querySelectorAll(".lp_default_blocks tbody"),
			additionalBlocks = document.querySelectorAll(".lp_additional_blocks tbody");

		defaultBlocks.forEach(function (el) {
			Sortable.create(el, {
				group: "default_blocks",
				animation: 500,
				handle: ".handle",
				draggable: "tr.windowbg",
				onAdd: e => block.sort(e),
				onUpdate: e => block.sort(e),
			});
		});

		additionalBlocks.forEach(function (el) {
			Sortable.create(el, {
				group: "additional_blocks",
				animation: 500,
				handle: ".handle",
				draggable: "tr.windowbg",
				onSort: e => block.sort(e)
			});
		});
	</script>';
}

function show_block_entry(int $id, array $data): void
{
	if (empty($id) || empty($data))
		return;

	$title = $data['description'] ?: $data['title'] ?: '';

	echo '
	<tr
		class="windowbg"
		data-id="', $id, '"
		x-data="{ status: ' . (empty($data['status']) ? 'false' : 'true') . ', showContextMenu: false }"
		x-init="$watch(\'status\', value => block.toggleStatus($el))"
	>
		<td class="icon hidden-xs hidden-sm">
			', $data['icon'], '
		</td>
		<td class="title">
			<div class="hidden-xs hidden-sm hidden-md">', $title, '</div>
			<div class="hidden-lg hidden-xl">
				<table class="table_grid">
					<tbody>
						', $title ? '<tr class="windowbg">
							<td colspan="2">' . $title . '</td>
						</tr>' : '', '
						<tr class="windowbg">
							<td>', Lang::$txt['lp_' . $data['type']]['title'] ?? Utils::$context['lp_missing_block_types'][$data['type']], '</td>
							<td class="hidden-md">', $data['areas'], '</td>
						</tr>
					</tbody>
				</table>
			</div>
		</td>
		<td class="type hidden-xs hidden-sm hidden-md">
			', Lang::$txt['lp_' . $data['type']]['title'] ?? Utils::$context['lp_missing_block_types'][$data['type']], '
		</td>
		<td class="areas hidden-xs hidden-sm">
			', $data['areas'], '
		</td>
		<td class="priority hidden-xs">
			', $data['priority'], ' ', str_replace(' class="', ' title="' . Lang::$txt['lp_action_move'] . '" class="handle ', Icon::get('sort')), '
		</td>
		<td class="status">
			<span :class="{ \'on\': status, \'off\': !status }" :title="status ? \'', Lang::$txt['lp_action_off'], '\' : \'', Lang::$txt['lp_action_on'], '\'" @click.prevent="status = !status"></span>
		</td>
		<td class="actions">
			<div class="context_menu" @click.outside="showContextMenu = false">
				<button class="button floatnone" @click.prevent="showContextMenu = true">
					', Icon::get('ellipsis'), '
				</button>
				<div class="roundframe" x-show="showContextMenu" x-transition.duration.500ms>
					<ul>
						<li>
							<a @click.prevent="block.clone($root)" class="button">', Lang::$txt['lp_action_clone'], '</a>
						</li>';

	if (isset(Lang::$txt['lp_' . $data['type']]['title'])) {
		echo '
						<li>
							<a href="', Config::$scripturl, '?action=admin;area=lp_blocks;sa=edit;id=', $id, '" class="button">', Lang::$txt['modify'], '</a>
						</li>';
	}

	echo '
						<li>
							<a @click.prevent="showContextMenu = false; block.remove($root)" class="button error">', Lang::$txt['remove'], '</a>
						</li>
					</ul>
				</div>
			</div>
		</td>
	</tr>';
}

function template_block_add(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_blocks'], '</h3>
	</div>
	<div class="information">', Lang::$txt['lp_blocks_add_instruction'], '</div>
	<div id="lp_blocks">
		<form name="block_add_form" action="', Utils::$context['form_action'], '" method="post" accept-charset="', Utils::$context['character_set'], '">
			<div class="row">';

	foreach (Utils::$context['lp_all_blocks'] as $block) {
		echo '
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" x-data>
					<div class="item roundframe" data-type="', $block['type'], '" @click="block.add($el)">
						<i class="', $block['icon'], ' fa-2x" aria-hidden="true"></i>
						<div>
							<strong>', $block['title'], '</strong>
						</div>
						<hr>
						<p>', $block['desc'], '</p>
					</div>
				</div>';
	}

	echo '
			</div>
			<input type="hidden" name="add_block">
			<input type="hidden" name="placement" value="', Utils::$context['lp_current_block']['placement'], '">
		</form>
	</div>

	<script>
		const block = new Block();
	</script>';
}

function template_block_post(): void
{
	if (isset(Utils::$context['preview_content']) && empty(Utils::$context['post_errors'])) {
		echo '
	<div class="preview_frame">';
		echo sprintf(TitleClass::values()[Utils::$context['lp_block']['title_class']], Utils::$context['preview_title']);

		echo '
		<div class="preview block_', Utils::$context['lp_block']['type'], '">
			', sprintf(ContentClass::values()[Utils::$context['lp_block']['content_class']], Utils::$context['preview_content']), '
		</div>
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_' . Utils::$context['lp_block']['type']]['title'], '</h3>
	</div>
	<div class="information">
		', Lang::$txt['lp_' . Utils::$context['lp_block']['type']]['description'], '
	</div>';
	}

	show_post_errors();

	$fields = Utils::$context['posting_fields'];

	echo '
	<form
		id="lp_post"
		action="', Utils::$context['form_action'], '"
		method="post"
		accept-charset="', Utils::$context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ title: \'', Utils::$context['lp_block']['title'], '\' }"
	>
		<div class="windowbg">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="common">
						', Icon::get('content'), Lang::$txt['lp_tab_content'], '
					</div>
					<div class="bg odd" data-tab="access">
						', Icon::get('access'), Lang::$txt['lp_tab_access_placement'], '
					</div>
					<div
						class="bg odd"
						data-tab="appearance"
						x-show="', (int) Utils::$context['lp_block_tab_appearance'], '"
					>
						', Icon::get('design'), Lang::$txt['lp_tab_appearance'], '
					</div>
					<div class="bg odd" data-tab="tuning">
						', Icon::get('tools'), Lang::$txt['lp_tab_tuning'], '
					</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="common">
						', template_portal_tab($fields), '
					</section>
					<section class="bg even" data-content="access">
						', template_portal_tab($fields, Tab::ACCESS_PLACEMENT), '
					</section>
					<section
						class="bg even"
						data-content="appearance"
						x-show="', (int) Utils::$context['lp_block_tab_appearance'], '"
					>
						', template_portal_tab($fields, Tab::APPEARANCE), '
					</section>
					<section class="bg even" data-content="tuning">
						', template_portal_tab($fields, Tab::TUNING), '
					</section>
				</div>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="block_id" value="', Utils::$context['lp_block']['id'], '">
				<input type="hidden" name="add_block" value="', Utils::$context['lp_block']['type'], '">
				<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
				<input type="hidden" name="seqnum" value="', Utils::$context['form_sequence_number'], '">
				<button
					type="submit"
					class="button active"
					name="remove"
					style="float: left"
					x-show="!', (int) empty(Utils::$context['lp_block']['id']), '"
				>
					', Lang::$txt['remove'], '
				</button>
				<button type="submit" class="button" name="preview" @click="block.post($root)">
					', Icon::get('preview'), Lang::$txt['preview'], '
				</button>
				<button type="submit" class="button" name="save" @click="block.post($root)">
					', Icon::get('save'), Lang::$txt['save'], '
				</button>
				<button type="submit" class="button" name="save_exit" @click="block.post($root)">
					', Icon::get('save_exit'), Lang::$txt['lp_save_and_exit'], '
				</button>
			</div>
		</div>
	</form>
	<script>
		const block = new Block();
		const tabs = new PortalTabs();
	</script>';
}

function template_show_areas_info(): void
{
	echo '
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th colspan="2">', Lang::$txt['lp_block_areas_th'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach (Utils::$context['lp_possible_areas'] as $area => $where_to_display) {
		echo '
			<tr class="windowbg">
				<td class="righttext"><strong>', $area, '</strong></td>
				<td class="lefttext">', $where_to_display, '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}
