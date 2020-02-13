<?php

namespace Bugo\LightPortal\Addons\RecentComments;

use Bugo\LightPortal\Helpers;

/**
 * RecentComments
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class RecentComments
{
	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * The maximum number of comments to output
	 *
	 * Максимальное количество комментариев для вывода
	 *
	 * @var int
	 */
	private static $num_comments = 10;

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
		$options['recent_comments'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'num_comments' => static::$num_comments
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

		if ($context['current_block']['type'] !== 'recent_comments')
			return;

		$args['parameters'] = array(
			'num_comments' => FILTER_VALIDATE_INT
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

		if ($context['lp_block']['type'] !== 'recent_comments')
			return;

		$context['posting_fields']['num_comments']['label']['text'] = $txt['lp_recent_comments_addon_num_comments'];
		$context['posting_fields']['num_comments']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_comments',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_comments']
			)
		);
	}

	/**
	 * Get the recent comments
	 *
	 * Получаем последние комментарии
	 *
	 * @param int $num_comments
	 * @return array
	 */
	public static function getRecentComments($num_comments)
	{
		global $smcFunc, $scripturl, $txt;

		if (empty($num_comments))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT com.id, com.page_id, com.message, com.created_at, p.title, p.alias, p.permissions, p.status, mem.real_name
			FROM {db_prefix}lp_comments AS com
				INNER JOIN {db_prefix}lp_pages AS p ON (p.page_id = com.page_id)
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)
			WHERE p.alias != {string:alias}
				AND p.status = {int:status}
			ORDER BY com.created_at DESC
			LIMIT {int:limit}',
			array(
				'alias'  => '/',
				'status' => 1,
				'limit'  => $num_comments
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['message']);

			$comments[$row['id']] = array(
				'id'          => $row['id'],
				'page_id'     => $row['page_id'],
				'link'        => '<a href="' . $scripturl . '?page=' . $row['alias'] . '#comment' . $row['id'] . '">' . $txt['response_prefix'] . $row['title'] . '</a>',
				'created_at'  => $row['created_at'],
				'author_name' => $row['real_name'],
				'can_show'    => Helpers::canShowItem($row['permissions'])
			);
		}

		$smcFunc['db_free_result']($request);

		$comments = array_filter($comments, function ($item) {
			return $item['can_show'] == true;
		});

		return $comments;
	}

	/**
	 * Form the block content
	 *
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

		if ($type !== 'recent_comments')
			return;

		$recent_comments = Helpers::useCache('recent_comments_addon_b' . $block_id . '_u' . $context['user']['id'], 'getRecentComments', __CLASS__, $cache_time, $parameters['num_comments']);

		if (!empty($recent_comments)) {
			ob_start();

			echo '
			<ul class="recent_comments noup">';

			foreach ($recent_comments as $comment) {
				echo '
				<li class="windowbg">
					', $comment['link'], '
					<br><span class="smalltext">', $txt['by'], ' ', $comment['author_name'], '</span>
					<br><span class="smalltext">', Helpers::getFriendlyTime($comment['created_at']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
