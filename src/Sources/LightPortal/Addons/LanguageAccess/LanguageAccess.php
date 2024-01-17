<?php

/**
 * LanguageAccess.php
 *
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Utils\{Lang, Utils};

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

			$allowed_languages = array_flip(explode(',', $block['parameters']['allowed_languages']));
			if (! array_key_exists(Utils::$context['user']['language'], $allowed_languages)) {
				unset(Utils::$context['lp_active_blocks'][$id]);
			}
		}
	}

	public function prepareBlockParams(array &$params): void
	{
		$params['allowed_languages'] = [];
	}

	public function validateBlockParams(array &$params): void
	{
		$params['allowed_languages'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		CustomField::make('allowed_languages', Lang::$txt['lp_language_access']['allowed_languages'])
			->setTab('access_placement')
			->setValue(fn() => new LanguageSelect);
	}
}
