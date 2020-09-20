<?php

namespace Bugo\LightPortal\Addons\BoardNews;

use Bugo\LightPortal\Helpers;

/**
 * BoardNews
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardNews
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-newspaper';

	/**
	 * Board id by default
	 *
	 * Идентификатор раздела по умолчанию
	 *
	 * @var int
	 */
	private static $board_id = 0;

	/**
	 * The maximum number of posts to output
	 *
	 * Максимальное количество сообщений для вывода
	 *
	 * @var int
	 */
	private static $num_posts = 5;

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
		$options['board_news']['parameters']['board_id']  = static::$board_id;
		$options['board_news']['parameters']['num_posts'] = static::$num_posts;
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

		if ($context['current_block']['type'] !== 'board_news')
			return;

		$args['parameters']['board_id']  = FILTER_VALIDATE_INT;
		$args['parameters']['num_posts'] = FILTER_VALIDATE_INT;
	}

	/**
	 * Get board list
	 *
	 * Получаем список разделов
	 *
	 * @return array
	 */
	private static function getBoardList()
	{
		global $sourcedir, $modSettings, $context;

		require_once($sourcedir . '/Subs-MessageIndex.php');

		$boardListOptions = array(
			'ignore_boards'   => false,
			'use_permissions' => true,
			'not_redirection' => true,
			'excluded_boards' => !empty($modSettings['recycle_board']) ? array((int) $modSettings['recycle_board']) : null,
			'selected_board'  => !empty($context['lp_block']['options']['parameters']['board_id']) ? $context['lp_block']['options']['parameters']['board_id'] : false
		);

		return getBoardList($boardListOptions);
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

		if ($context['lp_block']['type'] !== 'board_news')
			return;

		$context['posting_fields']['board_id']['label']['text'] = $txt['lp_board_news_addon_board_id'];
		$context['posting_fields']['board_id']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'board_id'
			),
			'options' => array()
		);

		$board_list = self::getBoardList();
		foreach ($board_list as $category) {
			$context['posting_fields']['board_id']['input']['options'][$category['name']] = array('options' => array());

			foreach ($category['boards'] as $board) {
				if (RC2_CLEAN) {
					$context['posting_fields']['board_id']['input']['options'][$category['name']]['options'][$board['name']]['attributes'] = array(
						'value'    => $board['id'],
						'selected' => (bool) $board['selected'],
						'label'    => ($board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '') . ' ' . $board['name']
					);
				} else {
					$context['posting_fields']['board_id']['input']['options'][$category['name']]['options'][$board['name']] = array(
						'value'    => $board['id'],
						'selected' => (bool) $board['selected'],
						'label'    => ($board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '') . ' ' . $board['name']
					);
				}
			}
		}

		$context['posting_fields']['num_posts']['label']['text'] = $txt['lp_board_news_addon_num_posts'];
		$context['posting_fields']['num_posts']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_posts',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_posts']
			)
		);
	}

	/**
	 * Get the news list of boards
	 *
	 * Получаем список новостей раздела
	 *
	 * @param array $parameters
	 * @return array
	 */
	public static function getData(array $parameters)
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');
		return ssi_boardNews($parameters['board_id'], $parameters['num_posts'], null, null, 'array');
	}

	/**
	 * Form the content block
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
		global $user_info, $txt, $modSettings, $scripturl, $context;

		if ($type !== 'board_news')
			return;

		$board_news = Helpers::getFromCache('board_news_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $cache_time, $parameters);

		if (!empty($board_news)) {
			ob_start();

			foreach ($board_news as $news) {
				$news['link'] = '<a href="' . $news['href'] . '">' . Helpers::getCorrectDeclension($news['replies'], $txt['lp_comments_set']) . '</a>';

				echo '
			<div class="news_item">
				<h3 class="news_header">
					', $news['icon'], '
					<a href="', $news['href'], '">', $news['subject'], '</a>
				</h3>
				<div class="news_timestamp">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '</div>
				<div class="news_body" style="padding: 2ex 0">', $news['body'], '</div>
				', $news['link'], ($news['locked'] ? '' : ' | ' . $news['comment_link']), '';

				if (!empty($modSettings['enable_likes'])) {
					echo '
					<ul>';

					if (!empty($news['likes']['can_like'])) {
						echo '
						<li class="smflikebutton" id="msg_', $news['message_id'], '_likes"><a href="', $scripturl, '?action=likes;ltype=msg;sa=like;like=', $news['message_id'], ';', $context['session_var'], '=', $context['session_id'], '" class="msg_like"><span class="', ($news['likes']['you'] ? 'unlike' : 'like'), '"></span>', ($news['likes']['you'] ? $txt['unlike'] : $txt['like']), '</a></li>';
					}

					if (!empty($news['likes']['count'])) {
						$context['some_likes'] = true;
						$count = $news['likes']['count'];
						$base = 'likes_';
						if ($news['likes']['you']) {
							$base = 'you_' . $base;
							$count--;
						}
						$base .= (isset($txt[$base . $count])) ? $count : 'n';

						echo '
						<li class="like_count smalltext">', sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=msg;like=' . $news['message_id'] . ';' . $context['session_var'] . '=' . $context['session_id'], comma_format($count)), '</li>';
					}

					echo '
					</ul>';
				}

				echo '
			</div>';

				if (!$news['is_last'])
					echo '
			<hr>';
			}

			$content = ob_get_clean();
		}
	}
}
