<?php

function template_callback_frontpage_mode_settings_before(): void
{
	global $modSettings;

	echo '
	<div x-data="{ frontpage_mode: \'', $modSettings['lp_frontpage_mode'] ?? 0, '\' }">';
}

function template_callback_frontpage_mode_settings_middle(): void
{
	global $txt, $context;

	echo '
		<table class="lp_table_settings">
			<tbody>
				<tr>
					<td x-show="frontpage_mode === \'chosen_page\'">
						<a id="setting_lp_frontpage_alias"></a>
						<span>
							<label for="lp_frontpage_alias">', $txt['lp_frontpage_alias'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_page\'">', $context['lp_frontpage_alias_select'], '</td>
					<td x-show="frontpage_mode === \'all_pages\'">
						<a id="setting_lp_frontpage_categories"></a>
						<span>
							<label for="lp_frontpage_categories">', $txt['lp_frontpage_categories'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'all_pages\'">', $context['lp_frontpage_categories_select'], '</td>
					<td x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
						<a id="setting_lp_frontpage_boards"></a>
						<span>
							<label for="lp_frontpage_boards">', $txt['lp_frontpage_boards'], '</label>
						</span>
					</td>
					<td x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">', $context['lp_frontpage_boards_select'], '</td>
					<td x-show="frontpage_mode === \'chosen_pages\'">
						<a id="setting_lp_frontpage_pages"></a>
						<span>
							<label for="lp_frontpage_pages">', $txt['lp_frontpage_pages'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_pages\'">', $context['lp_frontpage_pages_select'], '</td>
					<td x-show="frontpage_mode === \'chosen_topics\'">
						<a id="setting_lp_frontpage_topics"></a>
						<span>
							<label for="lp_frontpage_topics">', $txt['lp_frontpage_topics'], '</label>
						</span>
					</td>
					<td x-show="frontpage_mode === \'chosen_topics\'">', $context['lp_frontpage_topics_select'], '</td>
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
	global $modSettings;

	echo '
	<div
		x-data="{
			standalone_mode: ', empty($modSettings['lp_standalone_mode']) ? 'false' : 'true', ',
			frontpage_mode: \'', $modSettings['lp_frontpage_mode'] ?? 0, '\'
		}"
		@change-mode.window="frontpage_mode = $event.detail.front"
	>';
}

function template_callback_standalone_mode_settings_after(): void
{
	global $scripturl, $txt, $context;

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
							href="', $scripturl, '?action=helpadmin;help=lp_disabled_actions_help"
							onclick="return reqOverlayDiv(this.href);"
						>
							<span class="main_icons help" title="', $txt['help'], '"></span>
						</a>
						<a id="setting_lp_disabled_actions"></a>
						<span>
							<label for="lp_disabled_actions">', $txt['lp_disabled_actions'], '</label>
							<br>
							<span class="smalltext">', $txt['lp_disabled_actions_subtext'], '</span>
						</span>
					</td>
					<td>', $context['lp_disabled_actions_select'], '</td>
				</tr>
			</tbody>
		</table>
	</div>';
}

function template_callback_comment_settings_before(): void
{
	global $modSettings;

	echo '
	<div x-data="{ comment_block: \'', $modSettings['lp_show_comment_block'] ?? 'none', '\' }">';
}

function template_callback_comment_settings_after(): void
{
	echo '
	</div>';
}

function template_post_tab(array $fields, string $tab = 'content'): bool
{
	global $context;

	$fields['subject'] = ['no'];

	foreach ($fields as $pfid => $pf) {
		if (empty($pf['input']['tab']))
			$pf['input']['tab'] = 'tuning';

		if ($pf['input']['tab'] != $tab)
			$fields[$pfid] = ['no'];
	}

	$context['posting_fields'] = $fields;

	LoadTemplate('Post');

	template_post_header();

	return false;
}
