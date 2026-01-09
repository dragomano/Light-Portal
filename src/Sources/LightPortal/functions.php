<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventManagerFactory;
use LightPortal\UI\TemplateLoader;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

function template_lp_blade_wrapper(): void
{
	echo TemplateLoader::getLastContent();
}

function template_lp_portal_above(): void
{
	echo TemplateLoader::fromFile('block_view', [
		'layout' => 'above',
		'blocks' => Utils::$context['lp_blocks'] ?? [],
	], false);
}

function template_lp_portal_below(): void
{
	echo TemplateLoader::fromFile('block_view', [
		'layout' => 'below',
		'blocks' => Utils::$context['lp_blocks'] ?? [],
	], false);

	echo TemplateLoader::fromFile('debug', useSubTemplate: false);
}

function template_lp_docs_above(): void
{
	echo TemplateLoader::fromFile('admin/reference_links', useSubTemplate: false);
}

function template_lp_docs_below() {}

function template_callback_panel_layout(): void
{
	echo TemplateLoader::fromFile('admin/callbacks/panel_layout', useSubTemplate: false);
}

function template_callback_panel_direction(): void
{
	echo TemplateLoader::fromFile('admin/callbacks/panel_direction', useSubTemplate: false);
}

function template_lp_custom_above(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerAbove);
}

function template_lp_custom_below(): void
{
	app(EventManagerFactory::class)()->dispatch(PortalHook::addLayerBelow);
}

function template_callback_comment_settings_before(): void
{
	$value = Config::$modSettings['lp_comment_block'] ?? 'none';

	echo '<div x-data="{ comment_block: \'' . $value . '\' }">';
}

function template_callback_comment_settings_after(): void
{
	echo '</div>';
}

function template_callback_menu_settings_before(): void
{
	$value = empty(Config::$modSettings['lp_menu_separate_subsection']) ? 'false' : 'true';

	echo '<div x-data="{ separate_subsection: ' . $value . '}">';
}

function template_callback_menu_settings_after(): void
{
	echo '</div>';
}

function template_lp_home_toolbar_above(): void
{
	echo TemplateLoader::fromFile('partials/_toolbar', useSubTemplate: false);
}

function template_lp_home_toolbar_below(): void {}

function template_lp_list_above(): void
{
	echo TemplateLoader::fromFile('partials/_card_list', useSubTemplate: false);
}

function template_lp_list_below(): void {}

function show_pagination(string $position = 'top'): void
{
	echo TemplateLoader::fromFile(
		'layouts/partials/pagination',
		compact('position'),
		false
	);
}
