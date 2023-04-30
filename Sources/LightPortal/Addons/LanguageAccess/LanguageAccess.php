<?php

/**
 * LanguageAccess.php
 *
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 01.05.23
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class LanguageAccess extends Plugin
{
	public string $type = 'block_options';

	public function init()
	{
		if (empty($this->context['lp_active_blocks']))
			return;

		foreach ($this->context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['allowed_languages']))
				continue;

			$allowed_languages = array_flip(explode(',', $block['parameters']['allowed_languages']));
			if (! array_key_exists($this->context['user']['language'], $allowed_languages)) {
				unset($this->context['lp_active_blocks'][$id]);
			}
		}
	}

	public function blockOptions(array &$options)
	{
		$options[$this->context['current_block']['type']]['parameters']['allowed_languages'] = [];
	}

	public function validateBlockData(array &$parameters)
	{
		$parameters['allowed_languages'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		$this->context['posting_fields']['allowed_languages']['label']['html'] = $this->txt['lp_language_access']['allowed_languages'];
		$this->context['posting_fields']['allowed_languages']['input']['html'] = (new LanguageSelect)();
		$this->context['posting_fields']['allowed_languages']['input']['tab']  = 'access_placement';
	}
}
