<?php

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Subs;

/**
 * RecentPosts
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentPosts
{
	/**
	 * Максимальное количество сообщений для вывода
	 *
	 * @var int
	 */
	private static $num_posts = 10;

	/**
	 * Подключаем языковой файл
	 *
	 * @return void
	 */
	public static function lang()
	{
		global $user_info, $txt;

		require_once(__DIR__ . '/langs/' . $user_info['language'] . '.php');
	}

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['recent_posts'] = array(
			'parameters' => array(
				'num_posts' => static::$num_posts
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

		if ($context['current_block']['type'] !== 'recent_posts')
			return;

		$args['parameters'] = array(
			'num_posts' => FILTER_VALIDATE_INT
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

		if ($context['lp_block']['type'] !== 'recent_posts')
			return;

		$context['posting_fields']['num_posts']['label']['text'] = $txt['lp_recent_posts_addon_num_posts'];
		$context['posting_fields']['num_posts']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_posts',
				'value' => $context['lp_block']['options']['parameters']['num_posts']
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

		if ($type !== 'recent_posts')
			return;

		$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? $context['lp_block']['options']['parameters'];

		if (($recent_posts = cache_get_data('light_portal_recent_posts_addon', 3600)) == null) {
			require_once($boarddir . '/SSI.php');

			$recent_posts = ssi_recentPosts($parameters['num_posts'], null, null, 'array');

			cache_put_data('light_portal_recent_posts_addon', $recent_posts, 3600);
		}

		ob_start();

		foreach ($recent_posts as $post) {
			echo '
			<div class="sub_bar">
				', ($post['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span> ' : ''), $post['link'], ' ', $txt['by'], ' ', $post['poster']['link'], ' <br><span class="smalltext">', Subs::getFriendlyTime($post['timestamp']), '</span>
			</div>';
		}

		$content = ob_get_clean();
	}
}
