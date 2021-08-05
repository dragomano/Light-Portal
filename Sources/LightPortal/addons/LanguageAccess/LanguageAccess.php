<?php

/**
 * LanguageAccess
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Addons\Plugin;

class LanguageAccess extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'other';

	/**
	 * Fill additional block classes
	 *
	 * Заполняем дополнительные классы блока
	 *
	 * @return void
	 */
	public function init()
	{
		global $context;

		if (empty($context['lp_active_blocks']))
			return;

		foreach ($context['lp_active_blocks'] as $id => $block) {
			if (empty($block['parameters']) || empty($block['parameters']['allowed_languages']))
				continue;

			$allowed_languages = array_flip(explode(',', $block['parameters']['allowed_languages']));
			if (!array_key_exists($context['user']['language'], $allowed_languages)) {
				unset($context['lp_active_blocks'][$id]);
			}
		}
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		global $context;

		$options[$context['current_block']['type']]['parameters']['allowed_languages'] = [];
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	public function validateBlockData(&$parameters)
	{
		$parameters['allowed_languages'] = array(
			'name'   => 'allowed_languages',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		// Prepare the language list
		$current_languages = $context['lp_block']['options']['parameters']['allowed_languages'] ?? [];
		$current_languages = is_array($current_languages) ? $current_languages : explode(',', $current_languages);

		$data = [];
		foreach ($context['languages'] as $lang) {
			$data[] = "\t\t\t\t" . '{text: "' . $lang['filename'] . '", selected: ' . (in_array($lang['filename'], $current_languages) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#allowed_languages",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $txt['lp_language_access']['allowed_languages_subtext'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$context['posting_fields']['allowed_languages']['label']['text'] = $txt['lp_language_access']['allowed_languages'];
		$context['posting_fields']['allowed_languages']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'allowed_languages',
				'name'     => 'allowed_languages[]',
				'multiple' => true
			),
			'options' => array()
		);
	}
}
