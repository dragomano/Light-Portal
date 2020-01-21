<?php

namespace Bugo\LightPortal\Addons\TopTopics;

use Bugo\LightPortal\Helpers;

/**
 * TopTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopTopics
{
	/**
	 * Тип расчёта популярности (replies | views)
	 *
	 * @var string
	 */
	private static $type = 'replies';

	/**
	 * Максимальное количество тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

	/**
	 * Отображать только цифры, или нет
	 *
	 * @var bool
	 */
	private static $show_numbers_only = false;

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['top_topics'] = array(
			'parameters' => array(
				'popularity_type'   => static::$type,
				'num_topics'        => static::$num_topics,
				'show_numbers_only' => static::$show_numbers_only
			)
		);
	}

	/**
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'top_topics')
			return;

		$args['parameters'] = array(
			'popularity_type'   => FILTER_SANITIZE_STRING,
			'num_topics'        => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_topics')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_topics_addon_type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		foreach ($txt['lp_top_topics_addon_type_set'] as $key => $value) {
			$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
			);
		}

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_top_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_topics',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_posters_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Получаем список популярных тем
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getTopTopics($params)
	{
		global $boarddir;

		[$popularity_type, $num_topics] = $params;

		require_once($boarddir . '/SSI.php');
		return ssi_topTopics($popularity_type, $num_topics, 'array');
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $context, $txt;

		if ($type !== 'top_topics')
			return;

		$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? $context['lp_block']['options']['parameters'];
		$top_topics = Helpers::useCache('top_topics_addon_u' . $context['user']['id'], 'getTopTopics', __CLASS__, 3600, array($parameters['popularity_type'], $parameters['num_topics']));

		ob_start();

		if (!empty($top_topics)) {
			echo '
			<dl class="stats">';

			$max = $top_topics[0]['num_' . $parameters['popularity_type']];

			foreach ($top_topics as $topic) {
				$width = $topic['num_' . $parameters['popularity_type']] * 100 / $max;

				echo '
				<dt>', $topic['link'], '</dt>
				<dd class="statsbar generic_bar righttext">
					<div class="bar', (empty($topic['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
					<span>', $parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : Helpers::correctDeclension($topic['num_' . $parameters['popularity_type']], $txt['lp_' . $parameters['popularity_type'] . '_set']), '</span>
				</dd>';
			}

			echo '
			</dl>';
		}

		$content = ob_get_clean();
	}
}
