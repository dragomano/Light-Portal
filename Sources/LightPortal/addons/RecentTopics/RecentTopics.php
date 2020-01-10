<?php

namespace Bugo\LightPortal\Addons\RecentTopics;

/**
 * RecentTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentTopics
{
	/**
	 * Добавляем заголовок и описание блока
	 *
	 * @return void
	 */
	public static function lang()
	{
		global $user_info, $txt;

		require_once(__DIR__ . '/langs/' . $user_info['language'] . '.php');

		$txt['lp_block_types']['recent_topics'] = $txt['lp_recent_topics_addon_title'];
		$txt['lp_block_types_descriptions']['recent_topics'] = $txt['lp_recent_topics_addon_desc'];
	}

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['recent_topics'] = array(
			'parameters' => array(
				'num_topics' => 10
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

		if ($context['current_block']['type'] !== 'recent_topics')
			return;

		$args['num_topics'] = FILTER_VALIDATE_INT;
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'recent_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_recent_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_topics',
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			),
			'options' => array()
		);
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
		global $context, $boarddir, $txt;

		if ($type !== 'recent_topics')
			return;

		$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? $context['lp_block']['options']['parameters'];

		if (($recent_topics = cache_get_data('light_portal_recent_topics_addon', 3600)) == null) {
			require_once($boarddir . '/SSI.php');

			$recent_topics = ssi_recentTopics($parameters['num_topics'], null, null, 'array');

			cache_put_data('light_portal_recent_topics_addon', $recent_topics, 3600);
		}

		ob_start();

		foreach ($recent_topics as $topic) {
			echo '
			<div class="sub_bar">
				', ($topic['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], ' ', $txt['by'], ' ', $topic['poster']['link'], ' <br><span class="smalltext">', $topic['time'], '</span>
			</div>';
		}

		$content = ob_get_clean();
	}
}
