<?php

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Helpers;

/**
 * TagList
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

class TagList
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-tags';

	/**
	 * The source of tags (lp_tags|keywords)
	 *
	 * Источник тегов (lp_tags|keywords)
	 *
	 * @var string
	 */
	private static $source = 'lp_tags';

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
		$options['tag_list'] = array(
			'parameters' => array(
				'source' => static::$source
			)
		);
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
		global $context;

		if ($context['current_block']['type'] !== 'tag_list')
			return;

		$args['parameters'] = array(
			'source' => FILTER_SANITIZE_STRING
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

		if ($context['lp_block']['type'] !== 'tag_list')
			return;

		if (!class_exists('\Bugo\Optimus\Keywords'))
			$txt['lp_tag_list_addon_source_set'] = array('lp_tags' => $txt['lp_tag_list_addon_source_set']['lp_tags']);

		$context['posting_fields']['source']['label']['text'] = $txt['lp_tag_list_addon_source'];
		$context['posting_fields']['source']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'source'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_tag_list_addon_source_set'] as $key => $value) {
			if (RC2_CLEAN) {
				$context['posting_fields']['source']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['source']
				);
			} else {
				$context['posting_fields']['source']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['source']
				);
			}
		}
	}

	/**
	 * Get all topic keywords
	 *
	 * Получаем ключевики всех тем
	 *
	 * @return array
	 */
	public static function getAllTopicKeywords()
	{
		global $smcFunc, $scripturl, $context;

		if (!class_exists('\Bugo\Optimus\Keywords'))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				LEFT JOIN {db_prefix}optimus_log_keywords AS olk ON (olk.keyword_id = ok.id)
			GROUP BY ok.id, ok.name
			ORDER BY ok.name DESC',
			array()
		);

		$keywords = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$keywords[] = array(
				'value'     => $row['name'],
				'link'      => $scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency']
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $keywords;
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tag_list = Helpers::getFromCache('tag_list_addon_b' . $block_id . '_u' . $user_info['id'], 'getAll', '\Bugo\LightPortal\Tag', $cache_time, ...array(0, 0, 'value'));
		} else {
			$tag_list = Helpers::getFromCache('tag_list_addon_b' . $block_id . '_u' . $user_info['id'], 'getAllTopicKeywords', __CLASS__, $cache_time);
		}

		ob_start();

		if (!empty($tag_list)) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['value'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else
			echo $txt['lp_no_tags'];

		$content = ob_get_clean();
	}
}
