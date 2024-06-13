<?php

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Areas\Partials\{ActionSelect, BoardSelect, CategorySelect};
use Bugo\LightPortal\Areas\Partials\{PageSelect, PageSlugSelect, TopicSelect};
use Bugo\LightPortal\Enums\Tab;

function template_callback_frontpage_mode_settings_before(): void
{
	echo '
	<div x-data="{ frontpage_mode: \'', Config::$modSettings['lp_frontpage_mode'] ?? 0, '\' }">';
}

function template_callback_frontpage_mode_settings_middle(): void
{
	echo '
		<table class="lp_table_settings">
			<tbody>
				<tr>
					<td x-show="frontpage_mode === \'chosen_page\'">
						<a id="setting_lp_frontpage_chosen_page"></a>
						<span>
							<label for="lp_frontpage_chosen_page">', Lang::$txt['lp_frontpage_chosen_page'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_page\'">', new PageSlugSelect(), '</td>
					<td x-show="frontpage_mode === \'all_pages\'">
						<a id="setting_lp_frontpage_categories"></a>
						<span>
							<label for="lp_frontpage_categories">', Lang::$txt['lp_frontpage_categories'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'all_pages\'">', new CategorySelect(), '</td>
					<td x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
						<a id="setting_lp_frontpage_boards"></a>
						<span>
							<label for="lp_frontpage_boards">', Lang::$txt['lp_frontpage_boards'], '</label>
						</span>
					</td>
					<td x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">', new BoardSelect(), '</td>
					<td x-show="frontpage_mode === \'chosen_pages\'">
						<a id="setting_lp_frontpage_pages"></a>
						<span>
							<label for="lp_frontpage_pages">', Lang::$txt['lp_frontpage_pages'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_pages\'">', new PageSelect(), '</td>
					<td x-show="frontpage_mode === \'chosen_topics\'">
						<a id="setting_lp_frontpage_topics"></a>
						<span>
							<label for="lp_frontpage_topics">', Lang::$txt['lp_frontpage_topics'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_topics\'">', new TopicSelect(), '</td>
				</tr>
			</tbody>
		</table>
		<hr>';
}

function template_callback_frontpage_mode_settings_after(): void
{
	echo '
	</div>';
}

function template_callback_standalone_mode_settings_before(): void
{
	echo '
	<div
		x-data="{
			standalone_mode: ', empty(Config::$modSettings['lp_standalone_mode']) ? 'false' : 'true', ',
			frontpage_mode: \'', Config::$modSettings['lp_frontpage_mode'] ?? 0, '\'
		}"
		@change-mode.window="frontpage_mode = $event.detail.front"
	>';
}

function template_callback_standalone_mode_settings_after(): void
{
	echo '
		<table
			class="lp_table_settings"
			x-show="standalone_mode && ! [\'0\', \'chosen_page\'].includes(frontpage_mode)"
		>
			<tbody>
				<tr>
					<td>
						<a
							id="setting_lp_disabled_actions_help"
							href="', Config::$scripturl, '?action=helpadmin;help=lp_disabled_actions_help"
							onclick="return reqOverlayDiv(this.href);"
						>
							<span class="main_icons help" title="', Lang::$txt['help'], '"></span>
						</a>
						<a id="setting_lp_disabled_actions"></a>
						<span>
							<label for="lp_disabled_actions">', Lang::$txt['lp_disabled_actions'], '</label>
							<br>
							<span class="smalltext">', Lang::$txt['lp_disabled_actions_subtext'], '</span>
						</span>
					</td>
					<td>', new ActionSelect(), '</td>
				</tr>
			</tbody>
		</table>
	</div>';
}

function template_callback_comment_settings_before(): void
{
	echo '
	<div x-data="{ comment_block: \'', Config::$modSettings['lp_comment_block'] ?? 'none', '\' }">';
}

function template_callback_comment_settings_after(): void
{
	echo '
	</div>';
}

function template_callback_menu_settings_before(): void
{
	echo '
	<div x-data="{ separate_subsection: ', empty(Config::$modSettings['lp_menu_separate_subsection']) ? 'false' : 'true', ' }">';
}

function template_callback_menu_settings_after(): void
{
	echo '
	</div>';
}

function template_portal_tab(array $fields, Tab|string $tab = 'content'): bool
{
	$fields['subject'] = ['no'];

	$tab = is_string($tab) ? $tab : $tab->name();

	foreach ($fields as $pfid => $pf) {
		if (empty($pf['input']['tab']))
			$pf['input']['tab'] = Tab::TUNING->name();

		if ($pf['input']['tab'] != $tab)
			$fields[$pfid] = ['no'];
	}

	Utils::$context['posting_fields'] = $fields;

	Theme::loadTemplate('Post');

	template_post_header();

	return false;
}
