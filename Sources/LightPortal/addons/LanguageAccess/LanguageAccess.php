<?php

namespace Bugo\LightPortal\Addons\LanguageAccess;

use Bugo\LightPortal\Helpers;

/**
 * LanguageAccess
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class LanguageAccess
{
	/**
	 * @var string
	 */
	public $addon_type = 'other';

	/**
	 * @var array
	 */
	public $allowed_languages = [];

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

		$options[$context['current_block']['type']]['parameters']['allowed_languages'] = $this->allowed_languages;
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

		if (isset($context['lp_block']['options']['parameters']['allowed_languages'])) {
			$context['lp_block']['options']['parameters']['allowed_languages'] = is_array($context['lp_block']['options']['parameters']['allowed_languages']) ? $context['lp_block']['options']['parameters']['allowed_languages'] : explode(',', $context['lp_block']['options']['parameters']['allowed_languages']);
		} else
			$context['lp_block']['options']['parameters']['allowed_languages'] = Helpers::post('allowed_languages', []);

		$context['posting_fields']['allowed_languages']['label']['text'] = $txt['lp_language_access_addon_allowed_languages'];
		$context['posting_fields']['allowed_languages']['input'] = array(
			'type' => 'select',
			'after' => $txt['lp_language_access_addon_allowed_languages_subtext'],
			'attributes' => array(
				'id'       => 'allowed_languages',
				'name'     => 'allowed_languages[]',
				'multiple' => true,
				'style'    => 'height: auto'
			),
			'options' => array()
		);

		foreach ($context['languages'] as $lang) {
			if (RC2_CLEAN) {
				$context['posting_fields']['allowed_languages']['input']['options'][$lang['filename']]['attributes'] = array(
					'value'    => $lang['filename'],
					'selected' => in_array($lang['filename'], $context['lp_block']['options']['parameters']['allowed_languages'])
				);
			} else {
				$context['posting_fields']['allowed_languages']['input']['options'][$lang['filename']] = array(
					'value'    => $lang['filename'],
					'selected' => in_array($lang['filename'], $context['lp_block']['options']['parameters']['allowed_languages'])
				);
			}
		}
	}
}
