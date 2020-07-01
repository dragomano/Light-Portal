<?php

namespace Bugo\LightPortal\Addons\LanguageAccess;

/**
 * LanguageAccess
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class LanguageAccess
{

	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'other';

	/**
	 * Allowed forum languages for a specific block
	 *
	 * Разрешенные языки форума для конкретного блока
	 *
	 * @var array
	 */
	public static $allowed_languages = [];

	/**
	 * Fill additional block classes
	 *
	 * Заполняем дополнительные классы блока
	 *
	 * @return void
	 */
	public static function init()
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
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		global $context;

		$options[$context['current_block']['type']]['parameters']['allowed_languages'] = static::$allowed_languages;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		$args['parameters']['allowed_languages'] = array(
			'name'   => 'allowed_languages',
			'filter' => FILTER_SANITIZE_STRING,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if (isset($context['lp_block']['options']['parameters']['allowed_languages'])) {
			$context['lp_block']['options']['parameters']['allowed_languages'] = is_array($context['lp_block']['options']['parameters']['allowed_languages']) ? $context['lp_block']['options']['parameters']['allowed_languages'] : explode(',', $context['lp_block']['options']['parameters']['allowed_languages']);
		} else
			$context['lp_block']['options']['parameters']['allowed_languages'] = $_POST['allowed_languages'] ?? [];

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
			'options' => array(),
			'tab' => 'access_placement'
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
