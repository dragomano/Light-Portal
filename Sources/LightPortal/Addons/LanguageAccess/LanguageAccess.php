<?php

/**
 * LanguageAccess.php
 *
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Addons\Plugin;

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
		$parameters['allowed_languages'] = [
			'name'   => 'allowed_languages',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		];
	}

	public function prepareBlockFields()
	{
		// Prepare the language list
		$current_languages = $this->context['lp_block']['options']['parameters']['allowed_languages'] ?? [];
		$current_languages = is_array($current_languages) ? $current_languages : explode(',', $current_languages);

		$data = [];
		foreach ($this->context['languages'] as $lang) {
			$data[] = "\t\t\t\t" . '{text: "' . $lang['filename'] . '", selected: ' . (in_array($lang['filename'], $current_languages) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#allowed_languages",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $this->txt['lp_language_access']['allowed_languages_subtext'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$this->context['posting_fields']['allowed_languages']['label']['text'] = $this->txt['lp_language_access']['allowed_languages'];
		$this->context['posting_fields']['allowed_languages']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id'       => 'allowed_languages',
				'name'     => 'allowed_languages[]',
				'multiple' => true
			],
			'options' => [],
			'tab' => 'access_placement'
		];
	}
}
