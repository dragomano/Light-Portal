<?php declare(strict_types=1);

use Bugo\Compat\{Config, Lang, Theme, Utils};
use Bugo\LightPortal\Areas\Configs\BasicConfig;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\UI\Partials\SelectFactory;
use Bugo\LightPortal\Utils\Icon;

function template_callback_frontpage_mode_settings_middle(): void
{
	echo '
	<dl style="margin-top: -1.4em">
		<dt x-show="frontpage_mode === \'chosen_page\'">
			<label for="lp_frontpage_chosen_page">', Lang::$txt['lp_frontpage_chosen_page'], '</label>
		</dt>
		<dd x-show="frontpage_mode === \'chosen_page\'">
			', SelectFactory::pageSlug(), '
		</dd>
		<dt x-show="frontpage_mode === \'all_pages\'">
			<label for="lp_frontpage_categories">', Lang::$txt['lp_frontpage_categories'], '</label>
		</dt>
		<dd x-show="frontpage_mode === \'all_pages\'">
			', SelectFactory::category(), '
		</dd>
		<dt x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
			<label for="lp_frontpage_boards">', Lang::$txt['lp_frontpage_boards'], '</label>
		</dt>
		<dd x-show="[\'all_topics\', \'chosen_boards\'].includes(frontpage_mode)">
			', SelectFactory::board(), '
		</dd>
		<dt x-show="frontpage_mode === \'chosen_pages\'">
			<label for="lp_frontpage_pages">', Lang::$txt['lp_frontpage_pages'], '</label>
		</dt>
		<dd x-show="frontpage_mode === \'chosen_pages\'">
			', SelectFactory::page(), '
		</dd>
		<dt x-show="frontpage_mode === \'chosen_topics\'">
			<label for="lp_frontpage_topics">', Lang::$txt['lp_frontpage_topics'], '</label>
		</dt>
		<dd x-show="frontpage_mode === \'chosen_topics\'">
			', SelectFactory::topic(), '
		</dd>
	</dl>';
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
	<div x-data="{
		separate_subsection: ', empty(Config::$modSettings['lp_menu_separate_subsection']) ? 'false' : 'true', '
	}">';
}

function template_callback_menu_settings_after(): void
{
	echo '
	</div>';
}

function template_portal_basic_settings(): void
{
	if (! empty(Utils::$context['saved_successful'])) {
		echo '
	<div class="infobox">', Lang::$txt['settings_saved'], '</div>';
	} elseif (! empty(Utils::$context['saved_failed'])) {
		echo '
	<div class="errorbox">', sprintf(Lang::$txt['settings_not_saved'], Utils::$context['saved_failed']), '</div>';
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['mods_cat_features'], '</h3>
	</div>
	<form
		action="', Utils::$context['post_url'], '"
		method="post"
		accept-charset="', Utils::$context['character_set'], '"
		onsubmit="submitonce(this);"
		x-data="{ frontpage_mode: \'', Config::$modSettings['lp_frontpage_mode'] ?? 0, '\' }"
		@change-mode.window="frontpage_mode = $event.detail.front"
	>';

	if (! empty(Utils::$context['settings_message'])) {
		$tag = ! empty(Utils::$context['settings_message']['tag']) ? Utils::$context['settings_message']['tag'] : 'span';

		echo '
		<div class="information">';

		if (is_array(Utils::$context['settings_message'])) {
			echo '
			<', $tag, ! empty(Utils::$context['settings_message']['class']) ? ' class="' . Utils::$context['settings_message']['class'] . '"' : '', '>
				', Utils::$context['settings_message']['label'], '
			</', $tag, '>';
		} else {
			echo Utils::$context['settings_message'];
		}

		echo '
		</div>';
	}

	$fields = Utils::$context['posting_fields'];

	echo '
		<div class="roundframe', empty(Utils::$context['settings_message']) ? ' noup' : '', '">
			<div class="lp_tabs">
				<div data-navigation>
					<div class="bg odd active_navigation" data-tab="', BasicConfig::TAB_BASE, '">
						', Icon::get('cog_spin'), Lang::$txt['lp_base'], '
					</div>
					<div class="bg odd" data-tab="', BasicConfig::TAB_CARDS, '">
						', Icon::get('design'), Lang::$txt['lp_article_cards'], '
					</div>
					<div x-show="! [\'0\', \'chosen_page\'].includes(frontpage_mode)" class="bg odd" data-tab="', BasicConfig::TAB_STANDALONE, '">
						', Icon::get('meteor'), Lang::$txt['lp_standalone_mode_title'], '
					</div>
					<div class="bg odd" data-tab="', BasicConfig::TAB_PERMISSIONS, '">
						', Icon::get('access'), Lang::$txt['edit_permissions'], '
					</div>
				</div>
				<div data-content>
					<section class="bg even active_content" data-content="', BasicConfig::TAB_BASE, '">
						', template_portal_tab($fields, BasicConfig::TAB_BASE), '
					</section>
					<section class="bg even" data-content="', BasicConfig::TAB_CARDS, '">
						', template_portal_tab($fields, BasicConfig::TAB_CARDS), '
					</section>
					<section x-show="! [\'0\', \'chosen_page\'].includes(frontpage_mode)" class="bg even" data-content="', BasicConfig::TAB_STANDALONE, '">
						', template_portal_tab($fields, BasicConfig::TAB_STANDALONE), '
					</section>
					<section class="bg even" data-content="', BasicConfig::TAB_PERMISSIONS, '">
						', template_portal_tab($fields, BasicConfig::TAB_PERMISSIONS), '
					</section>
				</div>
			</div>
			<br class="clear">';

	if (empty(Utils::$context['settings_save_dont_show'])) {
		echo '
			<input type="submit" value="', Lang::$txt['save'], '"', (! empty(Utils::$context['save_disabled']) ? ' disabled' : ''), (! empty(Utils::$context['settings_save_onclick']) ? ' onclick="' . Utils::$context['settings_save_onclick'] . '"' : ''), ' class="button">';
	}

	if (isset(Utils::$context['admin-ssc_token'])) {
		echo '
			<input type="hidden" name="', Utils::$context['admin-ssc_token_var'], '" value="', Utils::$context['admin-ssc_token'], '">';
	}

	if (isset(Utils::$context['admin-dbsc_token'])) {
		echo '
			<input type="hidden" name="', Utils::$context['admin-dbsc_token_var'], '" value="', Utils::$context['admin-dbsc_token'], '">';
	}

	if (isset(Utils::$context['admin-mp_token'])) {
		echo '
			<input type="hidden" name="', Utils::$context['admin-mp_token_var'], '" value="', Utils::$context['admin-mp_token'], '">';
	}

	echo '
			<input type="hidden" name="', Utils::$context['session_var'], '" value="', Utils::$context['session_id'], '">
		</div>
		<script>
			const tabs = new PortalTabs();
		</script>
	</form>';
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

function show_post_errors(): void
{
	if (empty(Utils::$context['post_errors']))
		return;

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
