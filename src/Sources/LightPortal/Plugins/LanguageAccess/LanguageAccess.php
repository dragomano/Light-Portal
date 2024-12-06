<?php

/**
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\LanguageAccess;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\UI\Fields\CustomField;

if (! defined('LP_NAME'))
	die('No direct access...');

class LanguageAccess extends Plugin
{
	public string $type = 'block_options';

	private const PARAM = 'allowed_languages';

	public function init(): void
	{
		foreach (Utils::$context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters'][self::PARAM]))
				continue;

			$allowedLanguages = array_flip(explode(',', (string) $block['parameters'][self::PARAM]));
			if (! array_key_exists(Utils::$context['user']['language'], $allowedLanguages)) {
				unset(Utils::$context['lp_active_blocks'][$id]);
			}
		}
	}

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params[self::PARAM] = [];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params[self::PARAM] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(Event $e): void
	{
		CustomField::make(self::PARAM, $this->txt[self::PARAM])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new LanguageSelect(), [
				self::PARAM => $e->args->options[self::PARAM] ?? [],
			]);
	}
}
