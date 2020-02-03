<?php

namespace Bugo\LightPortal\Addons\RecentPosts;

use Bugo\LightPortal\Helpers;

/**
 * RecentPosts
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentPosts
{
	/**
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Максимальное количество сообщений для вывода
	 *
	 * @var int
	 */
	private static $num_posts = 10;

	/**
	 * Тип отображаемой ссылки (link | preview)
	 *
	 * @var string
	 */
	private static $type = 'link';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['recent_posts'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'num_posts' => static::$num_posts,
				'link_type' => static::$type
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
			'num_posts' => FILTER_VALIDATE_INT,
			'link_type' => FILTER_SANITIZE_STRING
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
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posts']
			)
		);

		$context['posting_fields']['link_type']['label']['text'] = $txt['lp_recent_posts_addon_type'];
		$context['posting_fields']['link_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'link_type'
			),
			'options' => array()
		);

		foreach ($txt['lp_recent_posts_addon_type_set'] as $key => $value) {
			if (!defined('JQUERY_VERSION')) {
				$context['posting_fields']['link_type']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['link_type']
				);
			} else {
				$context['posting_fields']['link_type']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['link_type']
				);
			}
		}
	}

	/**
	 * Получаем последние сообщения форума
	 *
	 * @param int $num_posts
	 * @return void
	 */
	public static function getRecentPosts($num_posts)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_recentPosts($num_posts, null, null, 'array');
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt;

		if ($type !== 'recent_posts')
			return;

		$recent_posts = Helpers::useCache('recent_posts_addon_b' . $block_id . '_u' . $context['user']['id'], 'getRecentPosts', __CLASS__, $cache_time, $parameters['num_posts']);

		if (!empty($recent_posts)) {
			ob_start();

			echo '
			<ul class="recent_posts noup">';

			foreach ($recent_posts as $post) {
				$post['preview'] = '<a href="' . $post['href'] . '">' . shorten_subject($post['preview'], 20) . '</a>';

				echo '
				<li class="windowbg">
					', ($post['is_new'] ? '<span class="new_posts">' . $txt['new'] . '</span> ' : ''), $post[$parameters['link_type']], '
					<br><span class="smalltext">', $txt['by'], ' ', $post['poster']['link'], '</span>
					<br><span class="smalltext">', Helpers::getFriendlyTime($post['timestamp']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
