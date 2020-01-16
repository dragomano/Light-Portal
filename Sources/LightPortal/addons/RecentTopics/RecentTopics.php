<?php

namespace Bugo\LightPortal\Addons\RecentTopics;

use Bugo\LightPortal\Helpers;

/**
 * RecentTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentTopics
{
	/**
	 * Максимальное количество тем для вывода
	 *
	 * @var int
	 */
	private static $num_topics = 10;

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
				'num_topics' => static::$num_topics
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

		$args['parameters'] = array(
			'num_topics' => FILTER_VALIDATE_INT
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

		if ($context['lp_block']['type'] !== 'recent_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_recent_topics_addon_num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_topics',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
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

		if (!empty($recent_topics)) {
			echo '
			<ul class="recent_topics">';

			foreach ($recent_topics as $topic) {
				echo '
				<li>
					', ($topic['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], ' ', $txt['by'], ' ', $topic['poster']['link'], ' <br><span class="smalltext">', Helpers::getFriendlyTime($topic['timestamp']), '</span>
				</li>';
			}

			echo '
			</ul>';
		}

		$content = ob_get_clean();
	}
}
