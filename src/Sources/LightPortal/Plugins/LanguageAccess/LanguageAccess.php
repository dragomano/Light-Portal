<?php

/**
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\LanguageAccess;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class LanguageAccess extends Plugin
{
	public string $type = 'block_options';

	public function init(): void
	{
		foreach (Utils::$context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['allowed_languages']))
				continue;

			$allowedLanguages = array_flip(explode(',', (string) $block['parameters']['allowed_languages']));
			if (! array_key_exists(Utils::$context['user']['language'], $allowedLanguages)) {
				unset(Utils::$context['lp_active_blocks'][$id]);
			}
		}
	}

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params['allowed_languages'] = [];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params['allowed_languages'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		CustomField::make('allowed_languages', Lang::$txt['lp_language_access']['allowed_languages'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new LanguageSelect());
	}
}
